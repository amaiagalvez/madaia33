<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use App\Models\Location;
use App\Models\Property;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Models\VotingSelection;
use App\Models\VotingOptionTotal;
use App\Models\PropertyAssignment;

beforeEach(function (): void {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

it('shows non-anonymous voting results with per-owner selected option percentage and option totals', function (): void {
    app()->setLocale('es');

    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Owner Results Visible',
    ]);

    $portal = Location::factory()->portal()->create(['name' => 'RES-1']);
    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '2B',
        'community_pct' => 10.25,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'start_date' => now()->subMonth(),
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'name_eu' => 'Emaitzak ez-anonimoa',
        'name_es' => 'Resultados no anonimos',
        'question_eu' => 'Galdera ez-anonimoa',
        'question_es' => 'Pregunta no anonima',
        'is_anonymous' => false,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    $yesOption = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
        'label_eu' => 'Bai',
        'label_es' => 'Si',
    ]);

    $noOption = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 2,
        'label_eu' => 'Ez',
        'label_es' => 'No',
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
        'voting_option_id' => $yesOption->id,
    ]);

    VotingOptionTotal::factory()->create([
        'voting_id' => $voting->id,
        'voting_option_id' => $yesOption->id,
        'votes_count' => 1,
        'pct_total' => 10.25,
    ]);

    VotingOptionTotal::factory()->create([
        'voting_id' => $voting->id,
        'voting_option_id' => $noOption->id,
        'votes_count' => 0,
        'pct_total' => 0,
    ]);

    $response = test()->actingAs($admin)
        ->get(route('admin.votings.results.show', ['voting' => $voting->id]));

    $response->assertOk()
        ->assertSee('data-voting-results-table', false)
        ->assertSee('data-results-charts', false)
        ->assertSee('data-chart-participation-owners', false)
        ->assertSee('data-chart-participation-percentage', false)
        ->assertSee('data-chart-options-owners', false)
        ->assertSee('data-chart-options-percentage', false)
        ->assertSee('Owner Results Visible')
        ->assertSee('RES-1 2B')
        ->assertSee('10,2500%', false)
        ->assertSee('data-option-value="selected"', false)
        ->assertSee('data-option-total-details-row', false)
        ->assertDontSee('data-total-option-votes-count="' . $yesOption->id . '"', false)
        ->assertSee('data-total-option-percentage="' . $yesOption->id . '"', false)
        ->assertSee('data-votes-count="1"', false)
        ->assertSee('data-pct-total="10.25"', false)
        ->assertSee('10,2500%')
        ->assertSee('data-total-voted-owners', false)
        ->assertSee('data-total-owner-percentage', false)
        ->assertSee('data-votes-count-mismatch="0"', false)
        ->assertSee('data-pct-total-mismatch="0"', false)
        ->assertSee('data-participation-eligible-owners', false)
        ->assertSee('data-participation-voted-owners', false)
        ->assertSee('data-option-owners-chart-value="' . $yesOption->id . '"', false)
        ->assertSee('data-option-pct-chart-value="' . $yesOption->id . '"', false);
});

it('shows anonymous voting results without per-owner option percentages and keeps option totals', function (): void {
    app()->setLocale('es');

    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Owner Hidden Vote',
    ]);

    $portal = Location::factory()->portal()->create(['name' => 'RES-2']);
    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '3C',
        'community_pct' => 7.5,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'start_date' => now()->subMonth(),
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->anonymous()->create([
        'name_eu' => 'Emaitzak anonimoa',
        'name_es' => 'Resultados anonimos',
        'question_eu' => 'Galdera anonimoa',
        'question_es' => 'Pregunta anonima',
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    $yesOption = VotingOption::factory()->create([
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
        'voting_option_id' => $yesOption->id,
    ]);

    VotingOptionTotal::factory()->create([
        'voting_id' => $voting->id,
        'voting_option_id' => $yesOption->id,
        'votes_count' => 1,
        'pct_total' => 7.5,
    ]);

    $response = test()->actingAs($admin)
        ->get(route('admin.votings.results.show', ['voting' => $voting->id]));

    $response->assertOk()
        ->assertSee('data-voting-results-table', false)
        ->assertSee('data-results-charts', false)
        ->assertSee('data-chart-participation-owners', false)
        ->assertSee('data-chart-participation-percentage', false)
        ->assertSee('data-chart-options-owners', false)
        ->assertSee('data-chart-options-percentage', false)
        ->assertSee(__('votings.admin.results_total'))
        ->assertDontSee('data-option-total-details-row', false)
        ->assertSee('data-total-option-votes-count="' . $yesOption->id . '"', false)
        ->assertSee('data-total-option-percentage="' . $yesOption->id . '"', false)
        ->assertSee('data-option-value="hidden"', false)
        ->assertDontSee('data-option-value="selected"', false)
        ->assertSee('7,5000%')
        ->assertSee('data-total-voted-owners', false)
        ->assertSee('data-total-owner-percentage', false)
        ->assertSee('data-participation-eligible-owners', false)
        ->assertSee('data-participation-voted-owners', false)
        ->assertSee('data-option-owners-chart-value="' . $yesOption->id . '"', false)
        ->assertSee('data-option-pct-chart-value="' . $yesOption->id . '"', false);
});

it('highlights totals in red when votes_count or pct_total differ from calculated owner data', function (): void {
    app()->setLocale('es');

    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Owner Mismatch Case',
    ]);

    $portal = Location::factory()->portal()->create(['name' => 'RES-3']);
    $property = Property::factory()->create([
        'location_id' => $portal->id,
        'name' => '4D',
        'community_pct' => 15,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'start_date' => now()->subMonth(),
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_anonymous' => false,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
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
        'votes_count' => 2,
        'pct_total' => 20,
    ]);

    $response = test()->actingAs($admin)
        ->get(route('admin.votings.results.show', ['voting' => $voting->id]));

    $response->assertOk()
        ->assertSee('data-results-charts', false)
        ->assertSee('data-chart-options-owners', false)
        ->assertSee('data-chart-options-percentage', false)
        ->assertSee('data-votes-count-mismatch="1"', false)
        ->assertSee('data-pct-total-mismatch="1"', false)
        ->assertSee('data-votes-count-value="' . $option->id . '"', false)
        ->assertSee('data-pct-total-value="' . $option->id . '"', false)
        ->assertSee('data-option-owners-chart-value="' . $option->id . '"', false)
        ->assertSee('data-option-pct-chart-value="' . $option->id . '"', false)
        ->assertSee('font-semibold text-red-600', false);
});
