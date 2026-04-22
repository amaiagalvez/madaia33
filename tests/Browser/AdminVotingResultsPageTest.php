<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use Tests\DuskTestCase;
use App\Models\Location;
use App\Models\Property;
use Laravel\Dusk\Browser;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Models\VotingSelection;
use App\Models\VotingOptionTotal;
use App\Models\PropertyAssignment;

test('admin voting list shows per-row results button and opens voting results page', function () {
    app()->setLocale('eu');

    $admin = User::factory()->create([
        'email' => 'dusk-voting-results-admin@example.com',
        'name' => 'Dusk Voting Results Admin',
    ]);

    Role::query()->firstOrCreate([
        'name' => Role::SUPER_ADMIN,
    ]);

    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Dusk Results Owner',
    ]);

    $portal = Location::factory()->portal()->create(['name' => 'R-11']);
    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '1A',
        'community_pct' => 12.5,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'start_date' => now()->subDays(30),
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'name_eu' => 'Emaitza test bozketa',
        'name_es' => 'Votacion test resultados',
        'question_eu' => 'Galdera test',
        'question_es' => 'Pregunta test',
        'is_published' => true,
        'is_anonymous' => false,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
        'label_eu' => 'Bai',
        'label_es' => 'Si',
    ]);

    $ballot = VotingBallot::factory()->create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => $admin->id,
    ]);

    VotingSelection::factory()->create([
        'voting_id' => $voting->id,
        'voting_ballot_id' => $ballot->id,
        'owner_id' => $owner->id,
        'voting_option_id' => $option->id,
    ]);

    VotingOptionTotal::factory()->create([
        'voting_id' => $voting->id,
        'voting_option_id' => $option->id,
        'votes_count' => 1,
        'pct_total' => 12.5,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $voting, $option): void {
        $browser->loginAs($admin)
            ->visit('/admin/bozketak')
            ->waitFor('[data-voting-results-link="' . $voting->id . '"]', 10)
            ->assertPresent('[data-voting-results-link="' . $voting->id . '"]')
            ->click('[data-voting-results-link="' . $voting->id . '"]')
            ->waitFor('[data-voting-results-breadcrumb]', 10)
            ->assertPresent('[data-voting-results-title]')
            ->assertPresent('[data-voting-results-question]')
            ->assertPresent('[data-voting-results-table]')
            ->assertPresent('[data-total-voted-owners]')
            ->assertPresent('[data-total-owner-percentage]')
            ->assertSeeIn('[data-total-voted-owners]', '1')
            ->assertSee('12,5000%')
            ->assertPresent('[data-results-charts]')
            ->assertPresent('[data-chart-participation-owners]')
            ->assertPresent('[data-chart-participation-percentage]')
            ->assertPresent('[data-chart-options-owners]')
            ->assertPresent('[data-chart-options-percentage]')
            ->assertPresent('[data-participation-eligible-owners-bar]')
            ->assertPresent('[data-participation-voted-owners-bar]')
            ->assertPresent('[data-participation-eligible-pct-bar]')
            ->assertPresent('[data-participation-voted-pct-bar]')
            ->assertPresent('[data-option-owners-bar="' . $option->id . '"]')
            ->assertPresent('[data-option-pct-bar="' . $option->id . '"]')
            ->assertPresent('[data-option-total-details-row]')
            ->assertPresent('[data-votes-count-mismatch="0"]')
            ->assertPresent('[data-pct-total-mismatch="0"]')
            ->assertSeeIn('[data-votes-count-value="' . $option->id . '"]', '1')
            ->assertSeeIn('[data-pct-total-value="' . $option->id . '"]', '12,5000');
    });
});

test('anonymous voting shows votes_count in GUZTIRA option totals row', function () {
    app()->setLocale('eu');

    $admin = User::factory()->create([
        'email' => 'dusk-voting-results-anon-admin@example.com',
        'name' => 'Dusk Voting Results Anonymous Admin',
    ]);

    Role::query()->firstOrCreate([
        'name' => Role::SUPER_ADMIN,
    ]);

    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Dusk Anonymous Owner',
    ]);

    $portal = Location::factory()->portal()->create(['name' => 'R-12']);
    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '2B',
        'community_pct' => 8.75,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'start_date' => now()->subDays(30),
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->anonymous()->create([
        'name_eu' => 'Emaitza anonimo test bozketa',
        'name_es' => 'Votacion anonima test resultados',
        'question_eu' => 'Galdera anonimo test',
        'question_es' => 'Pregunta anonima test',
        'is_published' => true,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
        'label_eu' => 'Bai',
        'label_es' => 'Si',
    ]);

    $ballot = VotingBallot::factory()->create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => $admin->id,
    ]);

    VotingSelection::factory()->create([
        'voting_id' => $voting->id,
        'voting_ballot_id' => $ballot->id,
        'owner_id' => $owner->id,
        'voting_option_id' => $option->id,
    ]);

    VotingOptionTotal::factory()->create([
        'voting_id' => $voting->id,
        'voting_option_id' => $option->id,
        'votes_count' => 1,
        'pct_total' => 8.75,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $voting, $option): void {
        $browser->loginAs($admin)
            ->visit('/admin/bozketak')
            ->waitFor('[data-voting-results-link="' . $voting->id . '"]', 10)
            ->click('[data-voting-results-link="' . $voting->id . '"]')
            ->waitFor('[data-voting-results-table]', 10)
            ->assertMissing('[data-option-total-details-row]')
            ->assertPresent('[data-total-option-votes-count="' . $option->id . '"]')
            ->assertSeeIn('[data-total-option-votes-count="' . $option->id . '"]', '1')
            ->assertPresent('[data-total-option-percentage="' . $option->id . '"]')
            ->assertSeeIn('[data-total-option-percentage="' . $option->id . '"]', '8,7500%');
    });
});
