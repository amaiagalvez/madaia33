<?php

use App\Models\Owner;
use App\Models\OwnerAuditLog;
use App\Models\Property;
use App\Models\Voting;
use App\Models\VotingBallot;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\PropertyAssignment;

test('profile owner tab renders a single shared owner form block', function () {
  $owner = Owner::factory()->create([
    'accepted_terms_at' => now(),
  ]);

  OwnerAuditLog::query()->create([
    'owner_id' => $owner->id,
    'changed_by_user_id' => $owner->user_id,
    'field' => 'coprop1_phone',
    'old_value' => '600111222',
    'new_value' => '699999999',
    'created_at' => now()->subMinute(),
    'updated_at' => now()->subMinute(),
  ]);

  $property = Property::factory()->create([
    'community_pct' => 1.25,
    'location_pct' => 2.50,
  ]);

  PropertyAssignment::factory()->create([
    'owner_id' => $owner->id,
    'property_id' => $property->id,
    'end_date' => null,
    'owner_validated' => false,
  ]);

  /** @var DuskTestCase $this */
  $this->browse(function (Browser $browser) use ($owner) {
    $browser->loginAs($owner->user)
      ->visit('/eu/profila?tab=owner')
      ->waitFor('[data-profile-panel="owner"]', 5)
      ->assertPresent('[data-profile-owner-edit-form]')
      ->assertPresent('[data-profile-owner-form-actions]')
      ->assertPresent('[data-profile-owner-save-button]')
      ->assertPresent('[data-profile-owner-cancel-button]')
      ->assertPresent('[data-owner-shared-form="true"]')
      ->assertPresent('[data-profile-owner-audit-log]')
      ->assertPresent('[data-profile-owner-audit-row]')
      ->assertPresent('[data-profile-owner-validation-help]')
      ->assertPresent('[data-profile-owner-property-percentages]')
      ->assertScript(
        'return document.querySelectorAll("[data-owner-shared-form=\"true\"]").length;',
        1,
      );
  });
});

test('profile votings tab renders pending active and missed closed lists', function () {
  $owner = Owner::factory()->create([
    'accepted_terms_at' => now(),
  ]);

  $property = Property::factory()->create();

  PropertyAssignment::factory()->create([
    'owner_id' => $owner->id,
    'property_id' => $property->id,
    'start_date' => today()->subYears(2)->format('Y-m-d'),
    'end_date' => null,
  ]);

  $participatedVoting = Voting::factory()->create([
    'name_eu' => 'Parte hartutako bozketa browser',
    'name_es' => 'Votacion participada browser',
  ]);

  Voting::factory()->current()->create([
    'name_eu' => 'Aktibo pendiente browser',
    'name_es' => 'Activa pendiente browser',
  ]);

  Voting::factory()->create([
    'name_eu' => 'Itxitako galduta browser',
    'name_es' => 'Cerrada perdida browser',
    'starts_at' => today()->subDays(10),
    'ends_at' => today()->subDays(2),
  ]);

  VotingBallot::factory()->create([
    'voting_id' => $participatedVoting->id,
    'owner_id' => $owner->id,
    'cast_by_user_id' => $owner->user_id,
    'voted_at' => now()->subHour(),
  ]);

  /** @var DuskTestCase $this */
  $this->browse(function (Browser $browser) use ($owner) {
    $browser->loginAs($owner->user)
      ->visit('/eu/profila?tab=votings')
      ->waitFor('[data-profile-panel="votings"]', 5)
      ->assertPresent('[data-profile-votings-participated]')
      ->assertPresent('[data-profile-votings-pending-active]')
      ->assertPresent('[data-profile-votings-pending-link]')
      ->assertScript(
        'return document.querySelector("[data-profile-votings-pending-link]")?.getAttribute("href")?.endsWith("/eu/bozketak");',
        true,
      )
      ->assertPresent('[data-profile-votings-missed-closed]')
      ->assertSee('Aktibo pendiente browser')
      ->assertSee('Itxitako galduta browser');
  });
});
