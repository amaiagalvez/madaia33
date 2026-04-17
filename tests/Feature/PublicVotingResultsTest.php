<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Voting;
use Livewire\Livewire;
use App\Livewire\Admin\Votings as AdminVotings;

beforeEach(function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate(['name' => $roleName]);
    }
});

// ─── Public results route ────────────────────────────────────────────────────

it('shows public voting results page when show_results is true and user is authenticated', function () {
    $user = User::factory()->create();
    $voting = Voting::factory()->create(['show_results' => true]);
    $secondVoting = Voting::factory()->create(['show_results' => true]);

    test()->actingAs($user)
        ->get(route('votings.results.eu', ['voting' => $voting->id]))
        ->assertOk()
        ->assertSee($voting->name_eu)
        ->assertSee(route('votings.results.eu', ['voting' => $voting->id]))
        ->assertSee(route('votings.results.eu', ['voting' => $secondVoting->id]));
});

it('returns 404 for public results when show_results is false', function () {
    $user = User::factory()->create();
    $voting = Voting::factory()->create(['show_results' => false]);

    test()->actingAs($user)
        ->get(route('votings.results.eu', ['voting' => $voting->id]))
        ->assertNotFound();
});

it('redirects guests to login for public results route', function () {
    $voting = Voting::factory()->create(['show_results' => true]);

    test()->get(route('votings.results.eu', ['voting' => $voting->id]))
        ->assertRedirect();
});

// ─── Admin toggleShowResults ─────────────────────────────────────────────────

it('toggles show_results on a voting via livewire', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $voting = Voting::factory()->create(['show_results' => false]);

    Livewire::actingAs($admin)
        ->test(AdminVotings::class)
        ->call('confirmShowResults', $voting->id, true)
        ->call('doShowResults');

    expect($voting->fresh()->show_results)->toBeTrue();

    Livewire::actingAs($admin)
        ->test(AdminVotings::class)
        ->call('confirmShowResults', $voting->id, false)
        ->call('doShowResults');

    expect($voting->fresh()->show_results)->toBeFalse();
});

it('blocks non-admin from toggling show_results via admin route', function () {
    $user = User::factory()->create();

    test()->actingAs($user)
        ->get(route('admin.votings'))
        ->assertForbidden();
});

// ─── Home announcement ───────────────────────────────────────────────────────

it('shows results announcement on home when votings have show_results true', function () {
    $user = User::factory()->create();
    $oldVoting = Voting::factory()->create([
        'show_results' => true,
        'ends_at' => now()->subDay(),
    ]);
    $latestVoting = Voting::factory()->create([
        'show_results' => true,
        'ends_at' => now()->addDay(),
    ]);

    test()->actingAs($user)
        ->get(route('home.eu'))
        ->assertOk()
        ->assertSee(__('votings.front.results_view_link'))
        ->assertSee(route('votings.results.eu', ['voting' => $latestVoting->id]))
        ->assertDontSee(route('votings.results.eu', ['voting' => $oldVoting->id]));
});

it('shows results links on home for guests and redirects them to login when clicked', function () {
    $voting = Voting::factory()->create(['show_results' => true]);
    $resultsUrl = route('votings.results.eu', ['voting' => $voting->id]);

    test()->get(route('home.eu'))
        ->assertOk()
        ->assertSee($resultsUrl);

    test()->get($resultsUrl)
        ->assertRedirect();
});

it('does not show results announcement when no votings have show_results true', function () {
    Voting::factory()->create(['show_results' => false]);

    test()->get(route('home.eu'))
        ->assertOk()
        ->assertDontSee('data-home-results-announcement', false);
});
