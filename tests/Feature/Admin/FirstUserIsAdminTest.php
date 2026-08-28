<?php

use App\Models\User;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery\MockInterface;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

it('assigns admin role to the first registered user', function () {
    expect(User::query()->count())->toBe(0);

    $user = app(CreatesNewUsers::class)->create([
        'name' => 'First User',
        'email' => 'first@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => false,
    ]);

    assertDatabaseHas('users', [
        'id' => $user->id,
        'is_admin' => true,
    ]);
});

it('does not assign admin role to subsequent registered users', function () {
    User::factory()->admin()->create();

    $user = app(CreatesNewUsers::class)->create([
        'name' => 'Second User',
        'email' => 'second@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => false,
    ]);

    assertDatabaseHas('users', [
        'id' => $user->id,
        'is_admin' => false,
    ]);
});

it('assigns admin role to the first google registered user', function () {
    config([
        'services.google.enabled' => true,
        'services.google.client_id' => 'test-client-id',
        'services.google.client_secret' => 'test-client-secret',
    ]);

    expect(User::query()->count())->toBe(0);

    $googleUser = Mockery::mock(SocialiteUser::class, function (MockInterface $mock) {
        $mock->shouldReceive('getId')->andReturn('google-first-user');
        $mock->shouldReceive('getEmail')->andReturn('google-first@example.com');
        $mock->shouldReceive('getName')->andReturn('Google First');
        $mock->shouldReceive('getNickname')->andReturn(null);
        $mock->token = 'token';
        $mock->refreshToken = null;
    });

    Socialite::shouldReceive('driver->user')->andReturn($googleUser);

    get(route('auth.google.callback'))->assertRedirect();

    assertDatabaseHas('users', [
        'email' => 'google-first@example.com',
        'is_admin' => true,
    ]);
});
