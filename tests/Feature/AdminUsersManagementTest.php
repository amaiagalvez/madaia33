<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\VotingBallot;
use App\Livewire\Admin\Users;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\Hash;

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

it('forbids users index for general admin', function () {
    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    test()->actingAs($generalAdmin)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

it('lists users excluding superadmin id 1', function () {
    User::factory()->create([
        'id' => 1,
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
    ]);

    $manager = adminUser([
        'email' => 'manager@example.com',
    ]);

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
    $manager = adminUser([
        'email' => 'manager-locations@example.com',
    ]);

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

it('filters users list by selected role', function () {
    $manager = adminUser([
        'email' => 'manager-filter-role@example.com',
    ]);

    $communityUser = User::factory()->create([
        'name' => 'Community Role User',
    ]);
    $communityUser->assignRole(Role::COMMUNITY_ADMIN);

    $delegatedUser = User::factory()->create([
        'name' => 'Delegated Role User',
    ]);
    $delegatedUser->assignRole(Role::DELEGATED_VOTE);

    Livewire::actingAs($manager)
        ->test(Users::class)
        ->set('roleFilter', Role::COMMUNITY_ADMIN)
        ->assertSee('Community Role User')
        ->assertDontSee('Delegated Role User');
});

it('shows delegated vote terms status only for delegated-vote users', function () {
    $manager = adminUser([
        'email' => 'manager-delegated-status@example.com',
    ]);

    $delegatedNoTerms = User::factory()->create([
        'name' => 'Delegated No Terms',
    ]);
    $delegatedNoTerms->assignRole(Role::DELEGATED_VOTE);

    $delegatedWithTerms = User::factory()->create([
        'name' => 'Delegated With Terms',
        'delegated_vote_terms_accepted_at' => now(),
    ]);
    $delegatedWithTerms->assignRole(Role::DELEGATED_VOTE);

    $regularUser = User::factory()->create([
        'name' => 'Regular Without Delegated Role',
    ]);

    test()->actingAs($manager)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Delegated No Terms')
        ->assertSee('Delegated With Terms')
        ->assertSee('Regular Without Delegated Role')
        ->assertSeeHtml('data-user-delegated-terms="' . $delegatedNoTerms->id . '"')
        ->assertSeeHtml('data-user-delegated-terms="' . $delegatedWithTerms->id . '"')
        ->assertDontSeeHtml('data-user-delegated-terms="' . $regularUser->id . '"');
});

it('allows superadmin to create a community admin with multiple locations', function () {
    $manager = adminUser();

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

    $manager = adminUser();

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

it('builds owner profile link with editOwner query param from users form', function () {
    $manager = adminUser();

    $managedUser = User::factory()->create([
        'email' => 'owner-link@example.com',
    ]);

    $owner = Owner::factory()->create([
        'user_id' => $managedUser->id,
    ]);

    Livewire::actingAs($manager)
        ->test(Users::class)
        ->call('editUser', $managedUser->id)
        ->assertSee(route('admin.owners.index', ['editOwner' => $owner->id], false), false);
});

it('uses shared admin styling components in users list and side panel form', function () {
    $manager = adminUser();

    User::factory()->create([
        'name' => 'Styled User',
        'email' => 'styled-user@example.com',
    ]);

    Livewire::actingAs($manager)
        ->test(Users::class)
        ->assertSeeHtml('data-admin-table-header')
        ->assertSeeHtml('data-admin-action="edit"')
        ->assertSeeHtml('data-admin-action="delete"')
        ->call('createUser')
        ->assertSeeHtml('data-admin-side-panel-form')
        ->assertSeeHtml('data-admin-form-footer-actions')
        ->assertSeeHtml('data-admin-form-input')
        ->assertSeeHtml('data-admin-field="multi-checkbox-pills"');
});

it('prevents deleting a user when related owner has already voted', function () {
    $manager = adminUser();

    $targetUser = User::factory()->create([
        'email' => 'cannot-delete-voted@example.com',
    ]);

    $owner = Owner::factory()->create([
        'user_id' => $targetUser->id,
    ]);

    $voting = Voting::factory()->create();

    VotingBallot::factory()->create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => null,
    ]);

    Livewire::actingAs($manager)
        ->test(Users::class)
        ->call('confirmDelete', $targetUser->id)
        ->call('deleteUser')
        ->assertSee(__('admin.users.delete_blocked_has_votes'));

    expect(User::query()->whereKey($targetUser->id)->exists())->toBeTrue();
});

it('prevents deleting a user when related owner has assignment history even if inactive', function () {
    $manager = adminUser();

    $targetUser = User::factory()->create([
        'email' => 'cannot-delete-assigned@example.com',
    ]);

    $owner = Owner::factory()->create([
        'user_id' => $targetUser->id,
    ]);

    PropertyAssignment::factory()->closed()->create([
        'owner_id' => $owner->id,
    ]);

    Livewire::actingAs($manager)
        ->test(Users::class)
        ->call('confirmDelete', $targetUser->id)
        ->call('deleteUser')
        ->assertSee(__('admin.users.delete_blocked_has_assignments'));

    expect(User::query()->whereKey($targetUser->id)->exists())->toBeTrue();
});

it('resets user password to default value after confirmation modal flow', function () {
    $manager = adminUser();

    $targetUser = User::factory()->create([
        'email' => 'reset-password-user@example.com',
        'password' => 'old-secret-pass',
    ]);

    Livewire::actingAs($manager)
        ->test(Users::class)
        ->call('confirmResetPassword', $targetUser->id)
        ->assertSet('confirmingResetPasswordUserId', $targetUser->id)
        ->assertSet('showResetPasswordModal', true)
        ->call('resetUserPassword')
        ->assertSet('confirmingResetPasswordUserId', null)
        ->assertSet('showResetPasswordModal', false);

    expect(Hash::check('123456789', $targetUser->fresh()->password))->toBeTrue();
});
