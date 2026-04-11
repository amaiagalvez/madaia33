<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Location;
use App\Livewire\Admin\Users;

beforeEach(function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

it('forbids users index for users without management role', function () {
    $user = User::factory()->create();

    test()->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

it('lists users excluding superadmin id 1', function () {
    User::factory()->create([
        'id' => 1,
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
    ]);

    $manager = User::factory()->create([
        'email' => 'manager@example.com',
    ]);
    $manager->assignRole(Role::GENERAL_ADMIN);

    $listedUser = User::factory()->create([
        'name' => 'Managed User',
    ]);

    test()->actingAs($manager)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Managed User')
        ->assertDontSee('Super Admin');
});

it('shows assigned managed locations in users list', function () {
    $manager = User::factory()->create([
        'email' => 'manager-locations@example.com',
    ]);
    $manager->assignRole(Role::GENERAL_ADMIN);

    $communityAdmin = User::factory()->create([
        'name' => 'Community Locations User',
    ]);
    $communityAdmin->assignRole(Role::COMMUNITY_ADMIN);

    $portal = Location::factory()->portal()->create(['code' => 'L-PORTAL-01']);
    $garage = Location::factory()->garage()->create(['code' => 'L-GARAGE-01']);
    $communityAdmin->managedLocations()->sync([$portal->id, $garage->id]);

    test()->actingAs($manager)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Community Locations User')
        ->assertSee('L-PORTAL-01')
        ->assertSee('L-GARAGE-01');
});

it('allows admin general to create a community admin with multiple locations', function () {
    $manager = User::factory()->create();
    $manager->assignRole(Role::GENERAL_ADMIN);

    $portal = Location::factory()->portal()->create();
    $garage = Location::factory()->garage()->create();

    Livewire::actingAs($manager)
        ->test(Users::class)
        ->call('createUser')
        ->set('name', 'Community Admin')
        ->set('email', 'community@example.com')
        ->set('password', 'password123')
        ->set('isActive', true)
        ->set('selectedRoles', [Role::COMMUNITY_ADMIN])
        ->set('selectedManagedLocations', [(string) $portal->id, (string) $garage->id])
        ->call('saveUser')
        ->assertHasNoErrors();

    $user = User::query()->where('email', 'community@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->roleNames()->all())->toContain(Role::COMMUNITY_ADMIN)
        ->and($user->managedLocations()->pluck('locations.id')->all())->toEqualCanonicalizing([$portal->id, $garage->id]);
});

it('forbids editing or deleting user id 1 from livewire actions', function () {
    $superadmin = User::factory()->create([
        'id' => 1,
        'email' => 'superadmin@example.com',
    ]);
    $superadmin->assignRole(Role::SUPER_ADMIN);

    $manager = User::factory()->create();
    $manager->assignRole(Role::GENERAL_ADMIN);

    Livewire::actingAs($manager)
        ->test(Users::class)
        ->call('editUser', $superadmin->id)
        ->assertForbidden();

    Livewire::actingAs($manager)
        ->test(Users::class)
        ->call('confirmDelete', $superadmin->id)
        ->assertForbidden();
});

it('allows superadmin to login as listed users from users table action', function () {
    $superadmin = User::factory()->create([
        'id' => 1,
        'email' => 'superadmin@example.com',
    ]);
    $superadmin->assignRole(Role::SUPER_ADMIN);

    $targetUser = User::factory()->create([
        'email' => 'impersonated@example.com',
    ]);

    Livewire::actingAs($superadmin)
        ->test(Users::class)
        ->call('loginAs', $targetUser->id)
        ->assertRedirect(route('home.eu'));

    test()->assertAuthenticatedAs($targetUser);
    expect(session('impersonator_user_id'))->toBe($superadmin->id);
});

it('returns from impersonated session back to original user', function () {
    $superadmin = User::factory()->create([
        'id' => 1,
        'email' => 'superadmin@example.com',
    ]);
    $superadmin->assignRole(Role::SUPER_ADMIN);

    $targetUser = User::factory()->create([
        'email' => 'ownerlike@example.com',
    ]);

    Livewire::actingAs($superadmin)
        ->test(Users::class)
        ->call('loginAs', $targetUser->id)
        ->assertRedirect(route('home.eu'));

    test()->assertAuthenticatedAs($targetUser);

    test()->withSession(['_token' => 'return-token'])
        ->post(route('admin.users.stop_impersonation'), ['_token' => 'return-token'])
        ->assertRedirect(route('admin.users.index'));

    test()->assertAuthenticatedAs($superadmin);
    expect(session()->has('impersonator_user_id'))->toBeFalse();
});
