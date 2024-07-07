<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('returns a response from the GPT API', function () {
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

    // Send a POST request to the GPTController
    $response = post(route('chat.send-message'), [
        'message' => 'Hello, GPT!'
    ]);

    // Assert the response is correct
    $response->assertStatus(200);
    $response->assertJson([
        'gptResponse' => 'This is a mock response from GPT-4',
    ]);
});

it('returns an error if the API key is missing', function () {
    // Create a test user
    $user = User::factory()->create();

    // Clear the API key configuration
    config(['services.openai.api_key' => null]);

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

    // Mock the GPT API response to fail
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'error' => [
                'message' => 'API request failed',
            ]
        ], 200)
    ]);

    // Act as the test user
    actingAs($user);

    // Send a POST request to the GPTController
    $response = post(route('chat.send-message'), [
        'message' => 'Hello, GPT!'
    ]);

    // Assert the response is an error
    $response->assertStatus(200);
    $response->assertJson([
        'gptResponse' => 'API request failed'
    ]);
});



