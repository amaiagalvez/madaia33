<?php

use App\Models\Location;
use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyAssignment;
use App\Models\Voting;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

test('open voting callout redirects guest to private login and then to votings page', function () {
  $owner = Owner::factory()->create();
  $portal = Location::factory()->portal()->create(['code' => '66-A']);
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
  ]);

  $voting->locations()->create(['location_id' => $portal->id]);

  /** @var DuskTestCase $this */
  $this->browse(function (Browser $browser) use ($owner) {
    $browser->visit('/eu')
      ->assertPresent('[data-home-votings-callout]')
      ->click('[data-home-votings-cta]')
      ->waitForLocation('/eu/pribatua')
      ->type('input[name=email]', $owner->user->email)
      ->type('input[name=password]', 'password')
      ->press('[data-test="login-button"]')
      ->waitForLocation('/eu/bozketak')
      ->assertPresent('[data-page="votings"]')
      ->assertPresent('[data-voting-card]');
  });
});

test('eligible owner can vote from front and ballot is stored as auditable record', function () {
  $owner = Owner::factory()->create();
  $portal = Location::factory()->portal()->create(['code' => '77-A']);
  $property = Property::factory()->create(['location_id' => $portal->id]);

  PropertyAssignment::factory()->create([
    'owner_id' => $owner->id,
    'property_id' => $property->id,
    'end_date' => null,
  ]);

  $voting = Voting::factory()->current()->create([
    'is_published' => true,
    'is_anonymous' => false,
  ]);

  $option = VotingOption::factory()->create([
    'voting_id' => $voting->id,
    'position' => 1,
    'label_eu' => 'Bai',
  ]);

  $voting->locations()->create(['location_id' => $portal->id]);

  /** @var DuskTestCase $this */
  $this->browse(function (Browser $browser) use ($owner, $option, $voting) {
    $browser->loginAs($owner->user)
      ->visit('/eu/bozketak')
      ->waitFor('[data-voting-card]')
      ->click("input[type='radio'][value='{$option->id}']")
      ->press("[data-vote-submit='{$voting->id}']")
      ->waitForText('Zure botoa ondo erregistratu da.')
      ->assertSee('Bozkatuta');
  });

  $ballot = VotingBallot::query()
    ->where('voting_id', $voting->id)
    ->where('owner_id', $owner->id)
    ->first();

  expect($ballot)->not->toBeNull()
    ->and($ballot->cast_by_user_id)->toBeNull();
});
