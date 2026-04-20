<?php

use App\Models\Role;
use App\Models\User;

it('matches canManageConstructions by role', function () {
    $role = fake()->randomElement(Role::names());

    $user = new User;
    $user->id = 2;
    $user->setRelation('roles', collect([new Role(['name' => $role])]));

    $expected = in_array($role, [
        Role::SUPER_ADMIN,
        Role::GENERAL_ADMIN,
        Role::CONSTRUCTION_MANAGER,
    ], true);

    expect($user->canManageConstructions())->toBe($expected);
})->repeat(2);
