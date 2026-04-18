<?php

use App\Models\Owner;
use App\Models\Voting;
use App\Models\Location;
use App\Models\Property;
use App\SupportedLocales;
use App\Models\VotingOption;
use App\Models\PropertyAssignment;

test('authenticated public votings response does not disable bfcache with no-store', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);

    $portal = Location::factory()->portal()->create(['code' => '99-A']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
        'label_eu' => 'Bai',
        'label_es' => 'Si',
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    $response = test()
        ->actingAs($owner->user)
        ->withSession(['locale' => SupportedLocales::SPANISH])
        ->get(route(SupportedLocales::routeName('votings', SupportedLocales::SPANISH)));

    $response->assertOk()
        ->assertSee('data-page="votings"', false);

    expect((string) $response->headers->get('Cache-Control'))
        ->not->toContain('no-store');
});
