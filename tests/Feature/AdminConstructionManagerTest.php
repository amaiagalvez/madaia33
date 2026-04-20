<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use App\Models\NoticeTag;
use App\Models\Construction;

beforeEach(function (): void {
    Role::query()->firstOrCreate(['name' => Role::SUPER_ADMIN]);
    Role::query()->firstOrCreate(['name' => Role::GENERAL_ADMIN]);
    Role::query()->firstOrCreate(['name' => Role::CONSTRUCTION_MANAGER]);
});

it('superadmin can create and edit constructions', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);

    Livewire::actingAs($user)
        ->test('admin-construction-manager')
        ->call('createConstruction')
        ->set('title', 'Obra berria')
        ->set('description', 'Deskribapena')
        ->set('startsAt', now()->toDateString())
        ->set('endsAt', now()->addDays(10)->toDateString())
        ->set('isActive', true)
        ->call('saveConstruction')
        ->assertHasNoErrors();

    $construction = Construction::query()->firstOrFail();

    Livewire::actingAs($user)
        ->test('admin-construction-manager')
        ->call('editConstruction', $construction->id)
        ->set('title', 'Obra eguneratua')
        ->call('saveConstruction')
        ->assertHasNoErrors();

    expect($construction->fresh()?->title)->toBe('Obra eguneratua');
});

it('superadmin can assign construction managers', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);

    $manager = User::factory()->create();
    $manager->assignRole(Role::CONSTRUCTION_MANAGER);

    Livewire::actingAs($user)
        ->test('admin-construction-manager')
        ->call('createConstruction')
        ->set('title', 'Managerrekin obra')
        ->set('startsAt', now()->toDateString())
        ->set('isActive', true)
        ->set('selectedManagers', [(string) $manager->id])
        ->call('saveConstruction')
        ->assertHasNoErrors();

    $construction = Construction::query()->firstOrFail();

    expect($construction->managers()->pluck('users.id')->all())->toBe([$manager->id]);
});

it('construction manager cannot delete constructions', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::CONSTRUCTION_MANAGER);

    $construction = Construction::factory()->create();

    Livewire::actingAs($user)
        ->test('admin-construction-manager')
        ->call('confirmDelete', $construction->id)
        ->assertForbidden();
});

it('requires confirmation before toggling construction active state', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);

    $construction = Construction::factory()->create([
        'is_active' => true,
    ]);

    Livewire::actingAs($user)
        ->test('admin-construction-manager')
        ->call('confirmToggleActive', $construction->id)
        ->assertSet('confirmingActiveId', $construction->id)
        ->assertSet('activeAction', 'deactivate')
        ->assertSet('showActiveModal', true);

    expect($construction->fresh()?->is_active)->toBeTrue();

    Livewire::actingAs($user)
        ->test('admin-construction-manager')
        ->call('confirmToggleActive', $construction->id)
        ->call('doToggleActive')
        ->assertSet('confirmingActiveId', null)
        ->assertSet('activeAction', '')
        ->assertSet('showActiveModal', false);

    expect($construction->fresh()?->is_active)->toBeFalse();
});

it('validates ends_at after starts_at', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);

    Livewire::actingAs($user)
        ->test('admin-construction-manager')
        ->call('createConstruction')
        ->set('title', 'Datak probatzen')
        ->set('startsAt', '2026-04-20')
        ->set('endsAt', '2026-04-10')
        ->call('saveConstruction')
        ->assertHasErrors(['endsAt' => 'after_or_equal']);
});

it('shows manager selector only for superadmin and general admin', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole(Role::SUPER_ADMIN);

    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    $constructionManager = User::factory()->create();
    $constructionManager->assignRole(Role::CONSTRUCTION_MANAGER);

    Livewire::actingAs($superadmin)
        ->test('admin-construction-manager')
        ->call('createConstruction')
        ->assertViewHas('canAssignManagers', true);

    Livewire::actingAs($generalAdmin)
        ->test('admin-construction-manager')
        ->call('createConstruction')
        ->assertViewHas('canAssignManagers', true);

    Livewire::actingAs($constructionManager)
        ->test('admin-construction-manager')
        ->call('createConstruction')
        ->assertViewHas('canAssignManagers', false);
});

it('admin constructions route allows construction manager role', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::CONSTRUCTION_MANAGER);

    $response = $this->actingAs($user)->get(route('admin.constructions'));

    $response->assertOk();
});

it('prevents creating two constructions with the same simple slug', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);

    Construction::factory()->create([
        'title' => 'Obra Errepikatua',
        'slug' => 'obra-errepikatua',
    ]);

    Livewire::actingAs($user)
        ->test('admin-construction-manager')
        ->call('createConstruction')
        ->set('title', 'Obra Errepikatua')
        ->set('startsAt', now()->toDateString())
        ->set('isActive', true)
        ->call('saveConstruction')
        ->assertHasErrors(['title']);
});

it('updates construction slug and notice tag after editing title', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::SUPER_ADMIN);

    $construction = Construction::factory()->create([
        'title' => 'Aurreko izena',
        'slug' => 'aurreko-izena',
    ]);

    Livewire::actingAs($user)
        ->test('admin-construction-manager')
        ->call('editConstruction', $construction->id)
        ->set('title', 'Izen berria')
        ->call('saveConstruction')
        ->assertHasNoErrors();

    $updatedConstruction = $construction->fresh();

    expect($updatedConstruction?->slug)->toBe('izen-berria')
        ->and(NoticeTag::query()->where('slug', 'izen-berria')->exists())->toBeTrue()
        ->and(NoticeTag::query()->where('slug', 'aurreko-izena')->exists())->toBeFalse();
});
