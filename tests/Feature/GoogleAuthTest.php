<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * 啟用 OAuth（模擬已佈署環境：非 local + 旗標 + 憑證）。
 */
function enableGoogleOAuth(): void
{
    config([
        'services.google.enabled' => true,
        'services.google.client_id' => 'test-client-id',
        'services.google.client_secret' => 'test-client-secret',
        'services.google.redirect' => 'https://example.test/auth/google/callback',
    ]);
}

/**
 * 讓 Socialite 回傳指定的 Google 使用者，避免呼叫真實 API。
 */
function fakeGoogleUser(string $id, string $email, string $name = 'Google User'): void
{
    $user = (new SocialiteUser)->map([
        'id' => $id,
        'email' => $email,
        'name' => $name,
        'nickname' => null,
    ]);
    $user->token = 'fake-token';
    $user->refreshToken = 'fake-refresh-token';

    Socialite::shouldReceive('driver->user')->andReturn($user);
}

it('returns 404 on the redirect route when oauth disabled (e.g. local)', function () {
    // 預設 testing 環境且無憑證 → enabled=false
    expect(config('services.google.enabled'))->toBeFalse();

    $this->get(route('auth.google.redirect'))->assertNotFound();
});

it('redirects to google when oauth enabled', function () {
    enableGoogleOAuth();

    $response = $this->get(route('auth.google.redirect'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('accounts.google.com');
});

it('creates and logs in a new user from google callback', function () {
    enableGoogleOAuth();
    fakeGoogleUser('google-123', 'new@example.com', 'New Person');

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    $user = User::where('email', 'new@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->google_id)->toBe('google-123')
        ->and($user->password)->toBeNull();
});

it('does not create a duplicate account when email already exists without google_id (ADR-004)', function () {
    enableGoogleOAuth();
    User::factory()->create(['email' => 'existing@example.com', 'google_id' => null]);
    fakeGoogleUser('google-999', 'existing@example.com');

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error');
    $this->assertGuest();
    expect(User::where('email', 'existing@example.com')->count())->toBe(1);
    expect(User::where('email', 'existing@example.com')->first()->google_id)->toBeNull();
});

it('redirects to register with error when email exists and oauth intent was register', function () {
    enableGoogleOAuth();
    User::factory()->create(['email' => 'existing@example.com', 'google_id' => null]);
    fakeGoogleUser('google-999', 'existing@example.com');

    $response = $this->withSession(['google_oauth_intent' => 'register'])
        ->get(route('auth.google.callback'));

    $response->assertRedirect(route('register'));
    $response->assertSessionHas('error');
    $this->assertGuest();
});

it('logs in an existing user already linked by google_id', function () {
    enableGoogleOAuth();
    $user = User::factory()->create(['google_id' => 'google-555']);
    fakeGoogleUser('google-555', $user->email);

    $response = $this->get(route('auth.google.callback'));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    expect(User::count())->toBe(1);
});

it('links google account to the currently authenticated user', function () {
    enableGoogleOAuth();
    $user = User::factory()->create(['google_id' => null]);
    fakeGoogleUser('google-link-1', 'someone-else@example.com');

    $this->actingAs($user)->get(route('auth.google.callback'))
        ->assertRedirect(route('profile.show'));

    expect($user->fresh()->google_id)->toBe('google-link-1');
});

it('rejects unlink when the account has no password', function () {
    $user = User::factory()->create([
        'password' => null,
        'google_id' => 'google-only-1',
    ]);

    $this->actingAs($user)
        ->delete(route('user.google.unlink'))
        ->assertSessionHasErrors('password');

    expect($user->fresh()->google_id)->toBe('google-only-1');
});

it('allows unlink when the account still has a password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'google_id' => 'google-both-1',
    ]);

    $this->actingAs($user)
        ->delete(route('user.google.unlink'))
        ->assertRedirect(route('profile.show'))
        ->assertSessionHasNoErrors();

    expect($user->fresh()->google_id)->toBeNull();
});

it('requires authentication for the link route', function () {
    $this->post(route('user.google.link'))->assertRedirect(route('login'));
});

it('returns 404 for the link route when oauth disabled', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('user.google.link'))->assertNotFound();
});

it('shares googleOAuth disabled state to the login page on local/testing', function () {
    $this->get(route('login'))
        ->assertInertia(fn ($page) => $page
            ->component('Auth/Login')
            ->where('googleOAuth.enabled', false)
            ->whereNot('googleOAuth.disabledReason', '')
        );
});

it('forces oauth off on local environment regardless of flag/credentials (ADR-007)', function () {
    $original = [
        'APP_ENV' => $_ENV['APP_ENV'] ?? null,
        'GOOGLE_OAUTH_ENABLED' => $_ENV['GOOGLE_OAUTH_ENABLED'] ?? null,
        'GOOGLE_CLIENT_ID' => $_ENV['GOOGLE_CLIENT_ID'] ?? null,
        'GOOGLE_CLIENT_SECRET' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? null,
    ];

    $set = function (array $vars): void {
        foreach ($vars as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    };

    // local：即使旗標與憑證齊全也必須 false
    $set([
        'APP_ENV' => 'local',
        'GOOGLE_OAUTH_ENABLED' => 'true',
        'GOOGLE_CLIENT_ID' => 'cid',
        'GOOGLE_CLIENT_SECRET' => 'secret',
    ]);
    $local = require base_path('config/services.php');
    expect($local['google']['enabled'])->toBeFalse();

    // 非 local（staging）：旗標 + 憑證齊全 → true
    $set(['APP_ENV' => 'staging']);
    $staging = require base_path('config/services.php');
    expect($staging['google']['enabled'])->toBeTrue();

    // 還原環境
    foreach ($original as $key => $value) {
        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        } else {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
});
