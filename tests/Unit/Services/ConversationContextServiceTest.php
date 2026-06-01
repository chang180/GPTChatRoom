<?php

use App\Jobs\SummarizeConversationJob;
use App\Models\ChatRoom;
use App\Models\ConversationSummary;
use App\Models\Message;
use App\Models\User;
use App\Services\ConversationContextService;
use Illuminate\Support\Facades\Bus;

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

it('does not dispatch summarize when the room has at most twenty context messages', function () {
    Bus::fake();

    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 20);

    $service = new ConversationContextService(mockGptService());
    $service->ensureSummaryCheckpoint($room);

    Bus::assertNothingDispatched();
});

it('does not dispatch summarize when overflow is below the minimum batch size', function () {
    Bus::fake();

    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 21);

    $service = new ConversationContextService(mockGptService());
    $service->ensureSummaryCheckpoint($room);

    Bus::assertNothingDispatched();
});

it('dispatches summarize after response when enough overflow messages exist', function () {
    Bus::fake();

    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 25);

    $service = new ConversationContextService(mockGptService());
    $service->ensureSummaryCheckpoint($room);

    Bus::assertDispatchedAfterResponse(
        SummarizeConversationJob::class,
        fn (SummarizeConversationJob $job) => $job->chatRoomId === $room->id,
    );
});

it('summarizes only messages outside the recent window when running the checkpoint', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 25);

    $fifthMessage = Message::query()
        ->where('chat_room_id', $room->id)
        ->orderBy('id')
        ->skip(4)
        ->first();

    $overflowMessages = Message::query()
        ->where('chat_room_id', $room->id)
        ->orderBy('id')
        ->take(5)
        ->get()
        ->map(fn (Message $message) => [
            'role' => $message->sender_type === 'gpt' ? 'assistant' : 'user',
            'content' => $message->text,
        ])
        ->values()
        ->all();

    $gptMock = mockGptService();
    $gptMock->shouldReceive('summarizeMessages')
        ->once()
        ->with($overflowMessages)
        ->andReturn('Summary of messages 1-5');

    $service = new ConversationContextService($gptMock);
    $service->runSummarizeCheckpoint($room);

    $checkpoint = ConversationSummary::where('chat_room_id', $room->id)->first();

    expect($checkpoint)->not->toBeNull();
    expect($checkpoint->content)->toBe('Summary of messages 1-5');
    expect($checkpoint->summarized_up_to_message_id)->toBe($fifthMessage->id);
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

    $gptMock = mockGptService();
    $gptMock->shouldNotReceive('summarizeMessages');

    $service = new ConversationContextService($gptMock);
    $service->runSummarizeCheckpoint($room);
});

it('appends new batch summaries without re-sending the full existing summary to the model', function () {
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

    $gptMock = mockGptService();
    $gptMock->shouldReceive('summarizeMessages')
        ->once()
        ->with($newOverflowMessages)
        ->andReturn('New batch summary');

    $service = new ConversationContextService($gptMock);
    $service->runSummarizeCheckpoint($room);

    $checkpoint = ConversationSummary::where('chat_room_id', $room->id)->first();

    expect($checkpoint->content)->toBe("Existing summary\nNew batch summary");
    expect($checkpoint->summarized_up_to_message_id)->toBe(
        Message::query()->where('chat_room_id', $room->id)->orderBy('id')->skip(24)->first()->id
    );
});

it('compresses the stored summary when the merged content exceeds the configured limit', function () {
    config(['conversation.summary_max_chars' => 20]);

    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 25);

    $gptMock = mockGptService();
    $gptMock->shouldReceive('summarizeMessages')
        ->once()
        ->andReturn('A very long new batch summary that should trigger compression');
    $gptMock->shouldReceive('compressSummary')
        ->once()
        ->andReturn('Compressed summary');

    $service = new ConversationContextService($gptMock);
    $service->runSummarizeCheckpoint($room);

    expect(ConversationSummary::where('chat_room_id', $room->id)->first()->content)
        ->toBe('Compressed summary');
});

it('does not summarize while another request holds an active summarize lock', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 25);

    ConversationSummary::create([
        'chat_room_id' => $room->id,
        'content' => '',
        'summarized_up_to_message_id' => 0,
        'summarizing_at' => now(),
        'summarizing_until' => now()->addMinutes(5),
    ]);

    $gptMock = mockGptService();
    $gptMock->shouldNotReceive('summarizeMessages');

    $service = new ConversationContextService($gptMock);
    $service->runSummarizeCheckpoint($room);

    expect(ConversationSummary::where('chat_room_id', $room->id)->first()->isSummarizeLocked())->toBeTrue();
});

it('can acquire the summarize lock after the previous lock has expired', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 25);

    ConversationSummary::create([
        'chat_room_id' => $room->id,
        'content' => '',
        'summarized_up_to_message_id' => 0,
        'summarizing_at' => now()->subMinutes(10),
        'summarizing_until' => now()->subMinute(),
    ]);

    $gptMock = mockGptService();
    $gptMock->shouldReceive('summarizeMessages')
        ->once()
        ->andReturn('Recovered summary');

    $service = new ConversationContextService($gptMock);
    $service->runSummarizeCheckpoint($room);

    $checkpoint = ConversationSummary::where('chat_room_id', $room->id)->first();

    expect($checkpoint->content)->toBe('Recovered summary');
    expect($checkpoint->isSummarizeLocked())->toBeFalse();
});

it('clears the summarize lock after a successful summarize', function () {
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    seedRoomContextMessages($room, $user, 25);

    $gptMock = mockGptService();
    $gptMock->shouldReceive('summarizeMessages')
        ->once()
        ->andReturn('Summary with lock cleared');

    $service = new ConversationContextService($gptMock);
    $service->runSummarizeCheckpoint($room);

    $checkpoint = ConversationSummary::where('chat_room_id', $room->id)->first();

    expect($checkpoint->content)->toBe('Summary with lock cleared');
    expect($checkpoint->summarizing_at)->toBeNull();
    expect($checkpoint->summarizing_until)->toBeNull();
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

    $service = new ConversationContextService(mockGptService());
    $conversation = $service->buildConversationContext($room);

    expect($conversation[0])->toBe([
        'role' => 'user',
        'content' => ConversationContextService::SUMMARY_CONTENT_PREFIX."\nStored summary",
    ]);
    expect($conversation[1]['content'])->toBe('Message 1');
    expect($conversation[2]['content'])->toBe('Message 2');
});

it('truncates long summaries before sending them to the chat model', function () {
    config(['conversation.summary_context_max_chars' => 10]);

    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $room = ChatRoom::getGlobalTheme('work');

    ConversationSummary::create([
        'chat_room_id' => $room->id,
        'content' => 'This summary is far too long for the demo context window',
        'summarized_up_to_message_id' => 0,
    ]);

    $service = new ConversationContextService(mockGptService());
    $conversation = $service->buildConversationContext($room, 0);

    expect($conversation[0]['content'])->toBe(
        ConversationContextService::SUMMARY_CONTENT_PREFIX."\nThis summa…"
    );
});
