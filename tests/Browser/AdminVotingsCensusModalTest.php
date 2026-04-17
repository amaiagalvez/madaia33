<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use Tests\DuskTestCase;
use App\Models\Location;
use App\Models\Property;
use Laravel\Dusk\Browser;
use App\Models\PropertyAssignment;

test('census modal hides vote and delegation columns', function () {
    app()->setLocale('eu');

    $admin = User::factory()->create([
        'email' => 'dusk-census-columns-admin@example.com',
        'name' => 'Dusk Census Columns Admin',
    ]);

    Role::query()->firstOrCreate([
        'name' => Role::SUPER_ADMIN,
    ]);

    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Dusk Census Owner',
    ]);

    $portal = Location::factory()->portal()->create(['code' => 'CENSUS-01']);
    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '1A',
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'start_date' => now()->subDays(30),
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'name_eu' => 'Errolda zutabe test bozketa',
        'name_es' => 'Votacion test columnas censo',
        'question_eu' => 'Galdera test',
        'question_es' => 'Pregunta test',
        'is_published' => true,
        'is_anonymous' => false,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $voting): void {
        $selector = '[data-action="open-census-' . $voting->id . '"]';

        $browser->loginAs($admin)
            ->visit('/admin/bozketak')
            ->waitFor($selector, 10)
            ->click($selector)
            ->waitFor('[data-owners-modal-table]', 10)
            ->assertPresent('[data-owners-modal-table]')
            ->assertMissing('[data-owners-modal-vote-column]')
            ->assertMissing('[data-owners-modal-delegate-dni-column]')
            ->assertMissing('[data-owners-modal-delegated-by-column]');
    });
});
