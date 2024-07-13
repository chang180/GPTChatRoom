<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;


it('redirects authenticated users to dashboard', function () {
    /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
    $user = User::factory()->create();

    $response = actingAs($user)->get('/');

    $response->assertRedirect('/dashboard');
});

it('displays welcome page to guests', function () {
    $response = get('/');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Welcome')
        ->has('canLogin')
        ->has('canRegister')
        ->has('laravelVersion')
        ->has('phpVersion'));
});
