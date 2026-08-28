<?php

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

it('forbids non-admin users from accessing the admin dashboard', function () {
    /** @var Authenticatable $user */
    $user = User::factory()->create();

    actingAs($user);

    get(route('admin.dashboard'))->assertForbidden();
});

it('allows admin users to view the admin dashboard', function () {
    /** @var Authenticatable $admin */
    $admin = User::factory()->admin()->create();

    actingAs($admin);

    get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('users.data'));
});

it('allows an admin to promote another user to admin', function () {
    /** @var Authenticatable $admin */
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    actingAs($admin);

    patch(route('admin.users.update', $member), [
        'is_admin' => true,
    ])->assertRedirect();

    expect($member->fresh()->isAdmin())->toBeTrue();
});

it('allows an admin to demote another admin when more than one admin exists', function () {
    /** @var Authenticatable $admin */
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();

    actingAs($admin);

    patch(route('admin.users.update', $otherAdmin), [
        'is_admin' => false,
    ])->assertRedirect();

    expect($otherAdmin->fresh()->isAdmin())->toBeFalse();
});

it('forbids demoting the last remaining admin', function () {
    /** @var Authenticatable $admin */
    $admin = User::factory()->admin()->create();

    actingAs($admin);

    patch(route('admin.users.update', $admin), [
        'is_admin' => false,
    ])->assertRedirect()
        ->assertSessionHas('error');

    expect($admin->fresh()->isAdmin())->toBeTrue();
});
