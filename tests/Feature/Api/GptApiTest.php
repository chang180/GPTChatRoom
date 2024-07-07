<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class)->group('api');

it('can handle gpt response', function () {
    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Hello, this is GPT-4!']]
            ]
        ], 200)
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/send-message', [
        'message' => 'Hello, GPT-4!',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'choices' => [
            ['message' => ['content' => 'Hello, this is GPT-4!']]
        ]
    ]);
});

it('fails if API key is missing', function () {
    // Temporarily unset the API key
    $originalApiKey = config('services.openai.api_key');
    config(['services.openai.api_key' => null]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/send-message', [
        'message' => 'Hello, GPT-4!',
    ]);

    $response->assertStatus(500);

    // Restore the API key
    config(['services.openai.api_key' => $originalApiKey]);
});

it('fails if API key is invalid', function () {
    // Set an invalid API key
    $originalApiKey = config('services.openai.api_key');
    config(['services.openai.api_key' => 'invalid_key']);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/send-message', [
        'message' => 'Hello, GPT-4!',
    ]);

    $response->assertStatus(401);

    // Restore the API key
    config(['services.openai.api_key' => $originalApiKey]);
});
