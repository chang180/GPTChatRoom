<?php

use App\Models\User;
use function Pest\Laravel\post;
use function Pest\Laravel\actingAs;

it('returns a response from the GPT API', function () {
    // Create a test user
    $user = User::factory()->create();

    // Act as the test user
    actingAs($user);

    // Send a POST request to the GPTController
    $response = post(route('chat.send-message'), [
        'message' => 'Hello, GPT!'
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

    // 获取实际的 GPT 响应
    $gptResponse = $response['gptResponse'];

    // Assert the message is saved in the database
    $this->assertDatabaseHas('messages', [
        'user_id' => $user->id,
        'text' => 'Hello, GPT!',
        'sender_type' => 'user',
    ]);

    // Assert the GPT response is saved in the database
    $this->assertDatabaseHas('messages', [
        'user_id' => $user->id,
        'text' => $gptResponse,
        'sender_type' => 'gpt',
    ]);
});

it('returns an error if the API key is missing', function () {
    // Create a test user
    $user = User::factory()->create();

    // Clear the API key configuration
    config(['openai.api_key' => null]);

    // Act as the test user
    actingAs($user);

    // Send a POST request to the GPTController
    $response = post(route('chat.send-message'), [
        'message' => 'Hello, GPT!'
    ]);

    // Assert the response is an error
    $response->assertStatus(200);
    $response->assertJson([
        'gptResponse' => 'API key missing'
    ]);
});

it('returns an error if the GPT API request fails', function () {
    // Create a test user
    $user = User::factory()->create();

    // Use an invalid API key configuration
    config(['openai.api_key' => 'invalid_api_key']);

    // Act as the test user
    actingAs($user);

    // Send a POST request to the GPTController
    $response = post(route('chat.send-message'), [
        'message' => 'Hello, GPT!'
    ]);

    // Assert the response is an error
    $response->assertStatus(200);
    $response->assertSee('API request failed');
});
