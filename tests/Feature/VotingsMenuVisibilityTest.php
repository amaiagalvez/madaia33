<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use App\Models\Location;
use App\Models\Property;
use App\SupportedLocales;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

dataset('nav_locales', SupportedLocales::all());

it('hides votings menu link when user is not authenticated and there are no open votings', function (string $locale) {
    test()->get(route(SupportedLocales::routeName('home', $locale)))
        ->assertSuccessful()
        ->assertDontSee(__('general.nav.votings'));
})->with('nav_locales');

it('shows votings menu link when user is not authenticated and open votings exist', function (string $locale) {
    Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    test()->get(route(SupportedLocales::routeName('home', $locale)))
        ->assertSuccessful()
        ->assertSee(__('general.nav.votings'));
})->with('nav_locales');

it('hides votings menu link when authenticated but no open votings and no pending delegations', function (string $locale) {
    $user = User::factory()->create();
    $owner = Owner::factory()->create(['user_id' => $user->id]);

    Voting::factory()->future()->create([
        'is_published' => true,
    ]);

    test()->actingAs($user)
        ->get(route(SupportedLocales::routeName('home', $locale)))
        ->assertSuccessful()
        ->assertDontSee(__('general.nav.votings'));
})->with('nav_locales');

it('shows votings menu link when authenticated and open votings exist', function (string $locale) {
    $user = User::factory()->create();
    Owner::factory()->create(['user_id' => $user->id]);

    Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    test()->actingAs($user)
        ->get(route(SupportedLocales::routeName('home', $locale)))
        ->assertSuccessful()
        ->assertSee(__('general.nav.votings'));
})->with('nav_locales');

it('shows votings menu link when authenticated and open votings exist even with pending delegations context', function (string $locale) {
    $owner1 = Owner::factory()->create();
    $owner2 = Owner::factory()->create();

    $portal = Location::factory()->portal()->create(['code' => '33-A']);
    $property1 = Property::factory()->create(['location_id' => $portal->id]);
    $property2 = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner1->id,
        'property_id' => $property1->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner2->id,
        'property_id' => $property2->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    // Mark owner2 as already voted
    DB::table('voting_ballots')->insert([
        'voting_id' => $voting->id,
        'owner_id' => $owner2->id,
        'cast_by_user_id' => $owner2->user_id,
        'voted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // owner1 hasn't voted, so they are eligible to vote and have pending delegations
    test()->actingAs($owner1->user)
        ->get(route(SupportedLocales::routeName('home', $locale)))
        ->assertSuccessful()
        ->assertSee(__('general.nav.votings'));
})->with('nav_locales');
