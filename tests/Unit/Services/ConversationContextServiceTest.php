<?php

use App\Models\ChatRoom;
use App\Models\ConversationSummary;
use App\Models\Message;
use App\Models\User;
use App\Services\ConversationContextService;
use App\Services\GPTService;

use function Pest\Laravel\mock;

function seedRoomContextMessages(ChatRoom $chatRoom, User $user, int $count): void
{
    for ($i = 1; $i <= $count; $i++) {
        Message::create([
            'user_id' => $user->id,
            'chat_room_id' => $chatRoom->id,
            'text' => "Message {$i}",
            'sender_type' => $i % 2 === 1 ? 'user' : 'gpt',
        ]);
    }
}

it('does not summarize when the room has at most twenty context messages', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 20);

    $gptMock = mock(GPTService::class);
    $gptMock->shouldNotReceive('summarizeConversation');

    $service = new ConversationContextService($gptMock);
    $service->ensureSummaryCheckpoint($room);

    expect(ConversationSummary::where('chat_room_id', $room->id)->exists())->toBeFalse();
});

it('summarizes only messages outside the recent window', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 21);

    $oldestMessage = Message::query()
        ->where('chat_room_id', $room->id)
        ->orderBy('id')
        ->first();

    $gptMock = mock(GPTService::class);
    $gptMock->shouldReceive('summarizeConversation')
        ->once()
        ->with(null, [
            ['role' => 'user', 'content' => 'Message 1'],
        ])
        ->andReturn('Summary of message 1');

    $service = new ConversationContextService($gptMock);
    $service->ensureSummaryCheckpoint($room);

    $checkpoint = ConversationSummary::where('chat_room_id', $room->id)->first();

    expect($checkpoint)->not->toBeNull()
        ->and($checkpoint->content)->toBe('Summary of message 1')
        ->and($checkpoint->summarized_up_to_message_id)->toBe($oldestMessage->id);
});

it('skips summarization when the checkpoint already covers overflow messages', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 40);

    $twentiethMessage = Message::query()
        ->where('chat_room_id', $room->id)
        ->orderBy('id')
        ->skip(19)
        ->first();

    ConversationSummary::create([
        'chat_room_id' => $room->id,
        'content' => 'Existing summary',
        'summarized_up_to_message_id' => $twentiethMessage->id,
    ]);

    $gptMock = mock(GPTService::class);
    $gptMock->shouldNotReceive('summarizeConversation');

    $service = new ConversationContextService($gptMock);
    $service->ensureSummaryCheckpoint($room);
});

it('incrementally merges new overflow messages into an existing summary', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 45);

    $twentiethMessage = Message::query()
        ->where('chat_room_id', $room->id)
        ->orderBy('id')
        ->skip(19)
        ->first();

    ConversationSummary::create([
        'chat_room_id' => $room->id,
        'content' => 'Existing summary',
        'summarized_up_to_message_id' => $twentiethMessage->id,
    ]);

    $newOverflowMessages = Message::query()
        ->where('chat_room_id', $room->id)
        ->orderBy('id')
        ->skip(20)
        ->take(5)
        ->get()
        ->map(fn (Message $message) => [
            'role' => $message->sender_type === 'gpt' ? 'assistant' : 'user',
            'content' => $message->text,
        ])
        ->values()
        ->all();

    $gptMock = mock(GPTService::class);
    $gptMock->shouldReceive('summarizeConversation')
        ->once()
        ->with('Existing summary', $newOverflowMessages)
        ->andReturn('Merged summary');

    $service = new ConversationContextService($gptMock);
    $service->ensureSummaryCheckpoint($room);

    $checkpoint = ConversationSummary::where('chat_room_id', $room->id)->first();

    expect($checkpoint->content)->toBe('Merged summary')
        ->and($checkpoint->summarized_up_to_message_id)->toBe(
            Message::query()->where('chat_room_id', $room->id)->orderBy('id')->skip(24)->first()->id
        );
});

it('does not summarize while another request holds an active summarize lock', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 21);

    ConversationSummary::create([
        'chat_room_id' => $room->id,
        'content' => '',
        'summarized_up_to_message_id' => 0,
        'summarizing_at' => now(),
        'summarizing_until' => now()->addMinutes(5),
    ]);

    $gptMock = mock(GPTService::class);
    $gptMock->shouldNotReceive('summarizeConversation');

    $service = new ConversationContextService($gptMock);
    $service->ensureSummaryCheckpoint($room);

    expect(ConversationSummary::where('chat_room_id', $room->id)->first()->isSummarizeLocked())->toBeTrue();
});

it('can acquire the summarize lock after the previous lock has expired', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 21);

    ConversationSummary::create([
        'chat_room_id' => $room->id,
        'content' => '',
        'summarized_up_to_message_id' => 0,
        'summarizing_at' => now()->subMinutes(10),
        'summarizing_until' => now()->subMinute(),
    ]);

    $gptMock = mock(GPTService::class);
    $gptMock->shouldReceive('summarizeConversation')
        ->once()
        ->andReturn('Recovered summary');

    $service = new ConversationContextService($gptMock);
    $service->ensureSummaryCheckpoint($room);

    $checkpoint = ConversationSummary::where('chat_room_id', $room->id)->first();

    expect($checkpoint->content)->toBe('Recovered summary')
        ->and($checkpoint->isSummarizeLocked())->toBeFalse();
});

it('clears the summarize lock after a successful summarize', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 21);

    $gptMock = mock(GPTService::class);
    $gptMock->shouldReceive('summarizeConversation')
        ->once()
        ->andReturn('Summary with lock cleared');

    $service = new ConversationContextService($gptMock);
    $service->ensureSummaryCheckpoint($room);

    $checkpoint = ConversationSummary::where('chat_room_id', $room->id)->first();

    expect($checkpoint->content)->toBe('Summary with lock cleared')
        ->and($checkpoint->summarizing_at)->toBeNull()
        ->and($checkpoint->summarizing_until)->toBeNull();
});

it('prepends the checkpoint summary before recent messages in context', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 2);

    ConversationSummary::create([
        'chat_room_id' => $room->id,
        'content' => 'Stored summary',
        'summarized_up_to_message_id' => 1,
    ]);

    $service = new ConversationContextService(mock(GPTService::class));
    $conversation = $service->buildConversationContext($room);

    expect($conversation[0])->toBe([
        'role' => 'user',
        'content' => ConversationContextService::SUMMARY_CONTENT_PREFIX."\nStored summary",
    ])
        ->and($conversation[1]['content'])->toBe('Message 1')
        ->and($conversation[2]['content'])->toBe('Message 2');
});
