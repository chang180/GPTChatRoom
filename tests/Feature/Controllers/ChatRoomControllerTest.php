<?php

use App\Events\AiReplyCompleted;
use App\Events\ChatMessageCreated;
use App\Events\ChatRoomCleared;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use App\Services\GPTService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;

it('shows the chat room page', function () {
    // Create a test user
    /** @var Authenticatable $user */
    $user = User::factory()->create();

    // Act as the test user
    actingAs($user);

    // Send a GET request to the chat room index
    $response = get(route('chat.index'));

    // Assert the response is correct
    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('ChatRoom')
    );
});

it('returns unauthorized for unauthenticated users', function () {
    // Send a GET request to the chat room index
    $response = get(route('chat.index'));

    // Assert the response is unauthorized
    $response->assertStatus(302);
});

it('sends a message and receives a response', function () {
    // Create a test user
    /** @var Authenticatable $user */
    $user = User::factory()->create();

    // Act as the test user
    actingAs($user);

    ChatRoom::ensureGlobalThemes();

    $mock = \Mockery::mock(GPTService::class);
    $mock->shouldReceive('sendMessage')
        ->once()
        ->withArgs(function ($message, $conversation) {
            return $message === 'Hello, GPT!'
                && is_array($conversation)
                && count($conversation) === 1
                && $conversation[0]['role'] === 'user'
                && $conversation[0]['content'] === 'Hello, GPT!';
        })
        ->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Mocked GPT response',
                    ],
                ],
            ],
        ]);
    $this->app->instance(GPTService::class, $mock);

    // Generate a CSRF token
    Session::start();
    $csrfToken = csrf_token();

    // Send a POST request to send a message
    $response = post(route('chat.send-message'), [
        'message' => 'Hello, GPT!',
        '_token' => $csrfToken, // Include the CSRF token in the request
    ]);

    // Assert the response is correct
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'message' => [
            'user_id',
            'text',
            'sender_type',
            'created_at',
            'updated_at',
            'id',
        ],
        'gptResponse',
    ]);

    // Assert the message is saved in the database
    $this->assertDatabaseHas('messages', [
        'user_id' => $user->id,
        'text' => 'Hello, GPT!',
        'sender_type' => 'user',
    ]);

    // Assert the GPT response is saved in the database
    $this->assertDatabaseHas('messages', [
        'user_id' => $user->id,
        'text' => 'Mocked GPT response',
        'sender_type' => 'gpt',
    ]);
});

it('loads more messages for the current theme only', function () {
    /** @var Authenticatable $user */
    $user = User::factory()->create();

    ChatRoom::ensureGlobalThemes();
    $workRoom = ChatRoom::getGlobalTheme('work');
    $studyRoom = ChatRoom::getGlobalTheme('study');

    actingAs($user);

    \App\Models\Message::create([
        'user_id' => $user->id,
        'chat_room_id' => $workRoom->id,
        'text' => 'work message',
        'sender_type' => 'user',
    ]);

    \App\Models\Message::create([
        'user_id' => $user->id,
        'chat_room_id' => $studyRoom->id,
        'text' => 'study message',
        'sender_type' => 'user',
    ]);

    $response = getJson(route('chat.load-more', [
        'theme' => 'work',
        'page' => 1,
        'per_page' => 30,
    ]));

    $response->assertStatus(200);
    expect($response->json('messages'))->toHaveCount(1);
    expect($response->json('messages.0.text'))->toBe('work message');
});

it('includes recent room messages as AI context', function () {
    /** @var Authenticatable $user */
    $user = User::factory()->create();
    ChatRoom::ensureGlobalThemes();
    $workRoom = ChatRoom::getGlobalTheme('work');

    actingAs($user);

    \App\Models\Message::create([
        'user_id' => $user->id,
        'chat_room_id' => $workRoom->id,
        'text' => 'Previous user message',
        'sender_type' => 'user',
    ]);

    \App\Models\Message::create([
        'user_id' => $user->id,
        'chat_room_id' => $workRoom->id,
        'text' => 'Previous assistant message',
        'sender_type' => 'gpt',
    ]);

    $mock = \Mockery::mock(GPTService::class);
    $mock->shouldReceive('sendMessage')
        ->once()
        ->withArgs(function ($message, $conversation) {
            return $message === 'Current prompt'
                && $conversation === [
                    ['role' => 'user', 'content' => 'Previous user message'],
                    ['role' => 'assistant', 'content' => 'Previous assistant message'],
                    ['role' => 'user', 'content' => 'Current prompt'],
                ];
        })
        ->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Context-aware response',
                    ],
                ],
            ],
        ]);
    $this->app->instance(GPTService::class, $mock);

    Session::start();

    $response = post(route('chat.send-message'), [
        'message' => 'Current prompt',
        'theme' => 'work',
        '_token' => csrf_token(),
    ]);

    $response->assertStatus(200);
    expect($response->json('gptResponse'))->toBe('Context-aware response');
});

it('dispatches a broadcast event for direct messages', function () {
    /** @var Authenticatable $user */
    $user = User::factory()->create();

    ChatRoom::ensureGlobalThemes();
    Event::fake([ChatMessageCreated::class]);

    actingAs($user);
    Session::start();

    $response = post(route('chat.send-message'), [
        'message' => 'Direct message',
        'theme' => 'work',
        'message_type' => 'direct',
        '_token' => csrf_token(),
    ]);

    $response->assertStatus(200);

    Event::assertDispatched(ChatMessageCreated::class, function (ChatMessageCreated $event) use ($user) {
        return $event->messageType === 'direct'
            && $event->message->text === 'Direct message'
            && $event->message->sender_type === 'user'
            && $event->message->user_id === $user->id
            && $event->message->chatRoom?->slug === 'work';
    });
});

it('dispatches broadcast events for ai messages and final replies', function () {
    /** @var Authenticatable $user */
    $user = User::factory()->create();

    ChatRoom::ensureGlobalThemes();
    Event::fake([ChatMessageCreated::class, AiReplyCompleted::class]);

    actingAs($user);

    $mock = \Mockery::mock(GPTService::class);
    $mock->shouldReceive('sendMessage')
        ->once()
        ->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => 'AI final reply',
                    ],
                ],
            ],
        ]);
    $this->app->instance(GPTService::class, $mock);

    Session::start();

    $response = post(route('chat.send-message'), [
        'message' => 'Question for AI',
        'theme' => 'work',
        '_token' => csrf_token(),
    ]);

    $response->assertStatus(200);

    Event::assertDispatched(ChatMessageCreated::class, function (ChatMessageCreated $event) use ($user) {
        return $event->messageType === 'ai_query'
            && $event->message->text === 'Question for AI'
            && $event->message->sender_type === 'user'
            && $event->message->user_id === $user->id
            && $event->message->chatRoom?->slug === 'work';
    });

    Event::assertDispatched(AiReplyCompleted::class, function (AiReplyCompleted $event) use ($user) {
        return $event->message->text === 'AI final reply'
            && $event->message->sender_type === 'gpt'
            && $event->message->user_id === $user->id
            && $event->message->chatRoom?->slug === 'work';
    });
});

it('forbids clearing a global theme chat room', function () {
    /** @var Authenticatable $user */
    $user = User::factory()->create();

    ChatRoom::ensureGlobalThemes();
    $workRoom = ChatRoom::getGlobalTheme('work');

    Message::create([
        'user_id' => $user->id,
        'chat_room_id' => $workRoom->id,
        'text' => 'Message to clear',
        'sender_type' => 'user',
    ]);

    actingAs($user);
    Session::start();

    $response = $this->delete(route('chat.clear'), [
        'theme' => 'work',
        '_token' => csrf_token(),
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
        ]);

    $this->assertDatabaseHas('messages', [
        'chat_room_id' => $workRoom->id,
        'text' => 'Message to clear',
    ]);
});

it('dispatches a broadcast event when clearing an owned chat room', function () {
    /** @var Authenticatable $user */
    $user = User::factory()->create();

    $ownedRoom = ChatRoom::create([
        'user_id' => $user->id,
        'slug' => 'private-lab',
        'name' => '私人聊天室',
        'description' => 'Owned room',
        'is_active' => true,
    ]);

    Message::create([
        'user_id' => $user->id,
        'chat_room_id' => $ownedRoom->id,
        'text' => 'Message to clear',
        'sender_type' => 'user',
    ]);

    Event::fake([ChatRoomCleared::class]);

    actingAs($user);
    Session::start();

    $response = $this->delete(route('chat.clear'), [
        'theme' => 'private-lab',
        '_token' => csrf_token(),
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'deleted_count' => 1,
        ]);

    $this->assertDatabaseMissing('messages', [
        'chat_room_id' => $ownedRoom->id,
        'text' => 'Message to clear',
    ]);

    Event::assertDispatched(ChatRoomCleared::class, function (ChatRoomCleared $event) use ($ownedRoom) {
        return $event->chatRoomId === $ownedRoom->id
            && $event->deletedCount === 1;
    });
});
