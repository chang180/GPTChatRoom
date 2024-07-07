<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class)->group('chat');

it('allows a user to access chat room', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/chat');

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('ChatRoom')
        ->has('auth.user')
        ->where('auth.user.name', $user->name)
    );
});

it('allows a user to send message', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/send-message', [
        'message' => 'Hello, GPT-4!',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['choices']);
});
