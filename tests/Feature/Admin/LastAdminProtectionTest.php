<?php

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Jetstream\Features;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patch;

it('forbids deleting the last remaining admin account', function () {
    if (! Features::hasAccountDeletionFeatures()) {
        $this->markTestSkipped('Account deletion is not enabled.');
    }

    /** @var Authenticatable $admin */
    $admin = User::factory()->admin()->create([
        'password' => bcrypt('password'),
    ]);

    actingAs($admin);

    $response = $this->delete('/user', [
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('password');
    assertDatabaseHas('users', ['id' => $admin->id]);
});

it('allows deleting an admin account when another admin exists', function () {
    if (! Features::hasAccountDeletionFeatures()) {
        $this->markTestSkipped('Account deletion is not enabled.');
    }

    User::factory()->admin()->create();
    /** @var Authenticatable $admin */
    $admin = User::factory()->admin()->create([
        'password' => bcrypt('password'),
    ]);

    actingAs($admin);

    $this->delete('/user', [
        'password' => 'password',
    ]);

    expect(User::query()->find($admin->id))->toBeNull();
});

it('forbids demoting the last remaining admin via admin dashboard', function () {
    /** @var Authenticatable $admin */
    $admin = User::factory()->admin()->create();

    actingAs($admin);

    patch(route('admin.users.update', $admin), [
        'is_admin' => false,
    ])->assertRedirect()
        ->assertSessionHas('error');

    expect($admin->fresh()->isAdmin())->toBeTrue();
});

it('allows demoting an admin when another admin remains', function () {
    /** @var Authenticatable $admin */
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();

    actingAs($admin);

    patch(route('admin.users.update', $otherAdmin), [
        'is_admin' => false,
    ])->assertRedirect()
        ->assertSessionHas('success');

    expect($otherAdmin->fresh()->isAdmin())->toBeFalse();
});
