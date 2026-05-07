<?php

use App\Models\ChatRoom;
use App\Services\GPTService;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Session;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;
use Inertia\Testing\AssertableInertia as Assert;

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

    ChatRoom::create([
        'name' => '工作',
        'slug' => 'work',
        'description' => '工作相關的討論和任務',
        'is_active' => true,
    ]);

    $mock = \Mockery::mock(GPTService::class);
    $mock->shouldReceive('sendMessage')
        ->once()
        ->with('Hello, GPT!')
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
            'id'
        ],
        'gptResponse'
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

    $workRoom = ChatRoom::create([
        'name' => '工作',
        'slug' => 'work',
        'description' => '工作相關的討論和任務',
        'is_active' => true,
    ]);

    $studyRoom = ChatRoom::create([
        'name' => '學習',
        'slug' => 'study',
        'description' => '學習和知識分享',
        'is_active' => true,
    ]);

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
