<?php

use App\Models\Message;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;



it('shows the chat room for authenticated users', function () {
    // Create a test user
    $user = User::factory()->create();

    // Act as the test user
    actingAs($user);

    // Send a GET request to the chat room index
    $response = get(route('chat.index'));

    // Assert the response is correct
    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('ChatRoom')
        ->has('messages')
        ->where('user.id', $user->id)
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
    $user = User::factory()->create();

    // Mock the GPT API response
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['role' => 'assistant', 'content' => 'This is a mock response from GPT-4']]
            ]
        ], 200)
    ]);

    // Act as the test user
    actingAs($user);
    $id = $user->id;

    // Send a POST request to send a message
    $response = post(route('chat.send-message'), [
        'message' => 'Hello, GPT!'
    ]);

    // Assert the response is correct
    $response->assertStatus(200);
    $response->assertJson([
        'gptResponse' => 'This is a mock response from GPT-4',
    ]);

    // Assert the message is saved in the database
    $this->assertDatabaseHas('messages', [
        'user_id' => $user->id,
        'text' => 'Hello, GPT!'
    ]);

    // Assert the GPT response is saved in the database
    $this->assertDatabaseHas('messages', [
        'user_id' => $user->id,
        'text' => 'This is a mock response from GPT-4'
    ]);
});

