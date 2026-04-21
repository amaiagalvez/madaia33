<?php

use App\Models\Role;
use App\Models\User;
use App\Models\NoticeTag;
use App\Models\Construction;

beforeEach(function (): void {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate(['name' => $roleName]);
    }
});

it('allows superadmin and general admin to manage constructions', function (): void {
    $construction = Construction::factory()->create();

    $superadmin = User::factory()->create(['id' => 1]);
    $superadmin->assignRole(Role::SUPER_ADMIN);

    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    expect($superadmin->can('viewAny', Construction::class))->toBeTrue()
        ->and($superadmin->can('create', Construction::class))->toBeTrue()
        ->and($superadmin->can('update', $construction))->toBeTrue()
        ->and($superadmin->can('delete', $construction))->toBeTrue()
        ->and($generalAdmin->can('viewAny', Construction::class))->toBeTrue()
        ->and($generalAdmin->can('create', Construction::class))->toBeTrue()
        ->and($generalAdmin->can('update', $construction))->toBeTrue()
        ->and($generalAdmin->can('delete', $construction))->toBeTrue();
});

it('allows construction manager to create and update but not delete constructions', function (): void {
    $construction = Construction::factory()->create();

    $manager = User::factory()->create();
    $manager->assignRole(Role::CONSTRUCTION_MANAGER);

    expect($manager->can('viewAny', Construction::class))->toBeTrue()
        ->and($manager->can('create', Construction::class))->toBeTrue()
        ->and($manager->can('update', $construction))->toBeTrue()
        ->and($manager->can('delete', $construction))->toBeFalse();
});

it('allows only superadmin and general admin to manage notice tags', function (): void {
    $tag = NoticeTag::factory()->create();

    $superadmin = User::factory()->create(['id' => 1]);
    $superadmin->assignRole(Role::SUPER_ADMIN);

    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    $constructionManager = User::factory()->create();
    $constructionManager->assignRole(Role::CONSTRUCTION_MANAGER);

    expect($superadmin->can('create', NoticeTag::class))->toBeTrue()
        ->and($superadmin->can('update', $tag))->toBeTrue()
        ->and($superadmin->can('delete', $tag))->toBeTrue()
        ->and($generalAdmin->can('create', NoticeTag::class))->toBeTrue()
        ->and($generalAdmin->can('update', $tag))->toBeTrue()
        ->and($generalAdmin->can('delete', $tag))->toBeTrue()
        ->and($constructionManager->can('create', NoticeTag::class))->toBeFalse()
        ->and($constructionManager->can('update', $tag))->toBeFalse()
        ->and($constructionManager->can('delete', $tag))->toBeFalse();
});
