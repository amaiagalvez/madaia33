<?php

use App\Models\Role;
use App\Models\User;

it('allows construction management only for superadmin general admin and construction manager', function (): void {
    $allowedRoles = [
        Role::SUPER_ADMIN,
        Role::GENERAL_ADMIN,
        Role::CONSTRUCTION_MANAGER,
    ];

    foreach ($allowedRoles as $roleName) {
        $user = new User;
        $user->id = 2;
        $user->setRelation('roles', collect([new Role(['name' => $roleName])]));

        expect($user->canManageConstructions())->toBeTrue();
    }

    $disallowedRoles = [
        Role::COMMUNITY_ADMIN,
        Role::PROPERTY_OWNER,
        Role::DELEGATED_VOTE,
    ];

    foreach ($disallowedRoles as $roleName) {
        $user = new User;
        $user->id = 2;
        $user->setRelation('roles', collect([new Role(['name' => $roleName])]));

        expect($user->canManageConstructions())->toBeFalse();
    }
});

it('includes construction manager in notice management and admin panel access', function (): void {
    $user = new User;
    $user->id = 2;
    $user->setRelation('roles', collect([new Role(['name' => Role::CONSTRUCTION_MANAGER])]));

    expect($user->canManageNotices())->toBeTrue()
        ->and($user->canAccessAdminPanel())->toBeTrue();
});
