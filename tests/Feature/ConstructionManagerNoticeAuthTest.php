<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Construction;

beforeEach(function (): void {
    Role::query()->firstOrCreate(['name' => Role::CONSTRUCTION_MANAGER]);
});

it('forbids saving notices with unassigned construction tag', function () {
    $manager = User::factory()->create();
    $manager->assignRole(Role::CONSTRUCTION_MANAGER);

    $ownedConstruction = Construction::factory()->create();
    $foreignConstruction = Construction::factory()->create();

    $manager->constructions()->sync([$ownedConstruction->id]);
    $foreignTag = $foreignConstruction->tag()->firstOrFail();

    Livewire::actingAs($manager)
        ->test('admin-notice-manager')
        ->set('titleEu', fake()->sentence(3))
        ->set('contentEu', fake()->paragraph())
        ->set('selectedTagId', $foreignTag->id)
        ->call('saveNotice')
        ->assertForbidden();
})->repeat(2);
