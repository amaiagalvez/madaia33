<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Voting;
use Livewire\Livewire;
use App\Models\Location;
use App\Models\Property;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Livewire\PublicVotings;
use App\Models\VotingSelection;
use App\Models\VotingOptionTotal;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PublicVotingController;

beforeEach(function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

it('requires authentication to access public votings page', function () {
    test()->get(route('votings.eu'))
        ->assertRedirect(route('login'));
});

it('shows terms blocking modal in front votings when owner has not accepted terms', function () {
    $owner = Owner::factory()->create([
        'accepted_terms_at' => null,
    ]);
    $portal = Location::factory()->portal()->create(['code' => '33-T']);
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

    createSetting('owners_terms_text_eu', '<p>Baldintzak bozketetan</p>');

    Livewire::actingAs($owner->user)
        ->test(PublicVotings::class)
        ->assertSet('requiresTermsAcceptance', true)
        ->assertSeeHtml('data-votings-terms-modal')
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasErrors(["selectedOptions.{$voting->id}"]);

    expect(VotingBallot::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $owner->id)
        ->count())->toBe(0);
});

it('allows an eligible owner to vote once and stores auditable rows', function () {
    Mail::fake();

    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);
    $portal = Location::factory()->portal()->create(['code' => '33-A']);
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

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 2,
        'label_eu' => 'Bai',
        'label_es' => 'Si',
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    Livewire::actingAs($owner->user)
        ->test(PublicVotings::class)
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasNoErrors();

    expect(VotingBallot::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $owner->id)
        ->count())->toBe(1);

    expect(VotingSelection::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $owner->id)
        ->where('voting_option_id', $option->id)
        ->count())->toBe(1);

    expect(VotingOptionTotal::query()
        ->where('voting_id', $voting->id)
        ->where('voting_option_id', $option->id)
        ->value('votes_count'))->toBe(1);

    Livewire::actingAs($owner->user)
        ->test(PublicVotings::class)
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasErrors(["selectedOptions.{$voting->id}"]);

    expect(VotingBallot::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $owner->id)
        ->count())->toBe(1);
});

it('shows already voted notice and hides voting options when owner already voted', function () {
    Mail::fake();

    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);
    $portal = Location::factory()->portal()->create(['code' => '33-AV']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
        'label_eu' => 'Bai',
        'label_es' => 'Si',
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    Livewire::actingAs($owner->user)
        ->test(PublicVotings::class)
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasNoErrors()
        ->assertSee(__('votings.front.already_voted'))
        ->assertDontSee(__('votings.front.vote_button'))
        ->assertDontSee('Bai');
});

it('does not store option selections for anonymous votings', function () {
    Mail::fake();

    $owner = Owner::factory()->create([
        'accepted_terms_at' => now(),
    ]);
    $portal = Location::factory()->portal()->create(['code' => '33-B']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->anonymous()->create([
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    Livewire::actingAs($owner->user)
        ->test(PublicVotings::class)
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasNoErrors();

    expect(VotingSelection::query()
        ->where('voting_id', $voting->id)
        ->count())->toBe(0);

    expect(VotingOptionTotal::query()
        ->where('voting_id', $voting->id)
        ->where('voting_option_id', $option->id)
        ->value('votes_count'))->toBe(1);
});

it('stores delegated voter user id when voting on behalf of another owner', function () {
    Mail::fake();

    $delegatedOwner = Owner::factory()->create();
    $adminUser = User::factory()->create();
    $adminUser->assignRole(Role::DELEGATED_VOTE);

    $portal = Location::factory()->portal()->create(['code' => '33-C']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $delegatedOwner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    test()->actingAs($adminUser)
        ->withSession([PublicVotingController::DELEGATED_OWNER_SESSION_KEY => $delegatedOwner->id]);

    Livewire::actingAs($adminUser)
        ->test(PublicVotings::class)
        ->call('setVoteCoordinates', 43.2701201, -2.9399999)
        ->set('delegateDni', '12345678A')
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasNoErrors();

    $ballot = VotingBallot::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $delegatedOwner->id)
        ->first();

    expect($ballot)->not->toBeNull()
        ->and($ballot->cast_by_user_id)->toBe($adminUser->id)
        ->and($ballot->cast_ip_address)->not->toBeNull()
        ->and($ballot->cast_latitude)->toBe(43.2701201)
        ->and($ballot->cast_longitude)->toBe(-2.9399999)
        ->and($ballot->cast_delegate_dni)->toBe('12345678A');
});

it('allows superadmin to open public votings in read only mode', function () {
    $superadmin = User::factory()->create([
        'id' => 1,
    ]);
    $superadmin->assignRole(Role::SUPER_ADMIN);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    Livewire::actingAs($superadmin)
        ->test(PublicVotings::class)
        ->assertSet('canCastVotes', false)
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasErrors(["selectedOptions.{$voting->id}"]);
});

it('allows superadmin to cast vote in delegated mode', function () {
    Mail::fake();

    $superadmin = User::factory()->create([
        'id' => 1,
    ]);
    $superadmin->assignRole(Role::SUPER_ADMIN);

    $delegatedOwner = Owner::factory()->create();
    $portal = Location::factory()->portal()->create(['code' => '33-D']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $delegatedOwner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    test()->actingAs($superadmin)
        ->withSession([PublicVotingController::DELEGATED_OWNER_SESSION_KEY => $delegatedOwner->id]);

    Livewire::actingAs($superadmin)
        ->test(PublicVotings::class)
        ->assertSet('canCastVotes', true)
        ->assertSet('isDelegated', true)
        ->set('delegateDni', '87654321B')
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasNoErrors();

    expect(VotingBallot::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $delegatedOwner->id)
        ->where('cast_delegate_dni', '87654321B')
        ->exists())->toBeTrue();
});

it('forbids delegated voting flow for users without delegated role', function () {
    $adminUser = User::factory()->create();

    test()->actingAs($adminUser)
        ->withSession([PublicVotingController::DELEGATED_OWNER_SESSION_KEY => 999]);

    Livewire::actingAs($adminUser)
        ->test(PublicVotings::class)
        ->assertForbidden();
});

it('requires delegate dni when voting in delegated mode', function () {
    $delegatedOwner = Owner::factory()->create();
    $adminUser = User::factory()->create();
    $adminUser->assignRole(Role::DELEGATED_VOTE);

    $portal = Location::factory()->portal()->create(['code' => '33-F']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $delegatedOwner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    test()->actingAs($adminUser)
        ->withSession([PublicVotingController::DELEGATED_OWNER_SESSION_KEY => $delegatedOwner->id]);

    Livewire::actingAs($adminUser)
        ->test(PublicVotings::class)
        ->assertSet('isDelegated', true)
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasErrors(['delegateDni']);

    expect(VotingBallot::query()->where('voting_id', $voting->id)->exists())->toBeFalse();
});

it('allows delegated-vote users to select delegated owner from public votings screen', function () {
    $delegatedUser = User::factory()->create();
    $delegatedUser->assignRole(Role::DELEGATED_VOTE);

    $delegatedOwner = Owner::factory()->create([
        'coprop1_name' => 'Aukeratzeko Jabea',
    ]);

    $portal = Location::factory()->portal()->create(['code' => '33-E']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $delegatedOwner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    Livewire::actingAs($delegatedUser)
        ->test(PublicVotings::class)
        ->assertSet('canCastVotes', false)
        ->assertSet('canManageDelegatedVoting', true)
        ->assertSee(__('votings.front.delegated_vote_button'))
        ->call('openDelegatedVoteModal')
        ->assertSet('showDelegatedModal', true)
        ->assertSee('Aukeratzeko Jabea')
        ->call('startDelegatedVote', $delegatedOwner->id)
        ->assertRedirect(route('votings.eu'));
});

it('stores in person vote without dni when voting on behalf of another owner', function () {
    Mail::fake();

    $inPersonOwner = Owner::factory()->create();
    $adminUser = User::factory()->create();
    $adminUser->assignRole(Role::DELEGATED_VOTE);

    $portal = Location::factory()->portal()->create(['code' => '33-G']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $inPersonOwner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    test()->actingAs($adminUser)
        ->withSession([PublicVotingController::IN_PERSON_OWNER_SESSION_KEY => $inPersonOwner->id]);

    Livewire::actingAs($adminUser)
        ->test(PublicVotings::class)
        ->call('setVoteCoordinates', 43.2701201, -2.9399999)
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasNoErrors();

    $ballot = VotingBallot::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $inPersonOwner->id)
        ->first();

    expect($ballot)->not->toBeNull()
        ->and($ballot->cast_by_user_id)->toBe($adminUser->id)
        ->and($ballot->cast_ip_address)->not->toBeNull()
        ->and($ballot->cast_latitude)->toBe(43.2701201)
        ->and($ballot->cast_longitude)->toBe(-2.9399999)
        ->and($ballot->cast_delegate_dni)->toBeNull()
        ->and($ballot->is_in_person)->toBeTrue();
});

it('allows superadmin to cast in-person vote', function () {
    Mail::fake();

    $superadmin = User::factory()->create([
        'id' => 1,
    ]);
    $superadmin->assignRole(Role::SUPER_ADMIN);

    $inPersonOwner = Owner::factory()->create();
    $portal = Location::factory()->portal()->create(['code' => '33-H']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $inPersonOwner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    test()->actingAs($superadmin)
        ->withSession([PublicVotingController::IN_PERSON_OWNER_SESSION_KEY => $inPersonOwner->id]);

    Livewire::actingAs($superadmin)
        ->test(PublicVotings::class)
        ->assertSet('canCastVotes', true)
        ->assertSet('isInPersonVoting', true)
        ->set("selectedOptions.{$voting->id}", $option->id)
        ->call('vote', $voting->id)
        ->assertHasNoErrors();

    expect(VotingBallot::query()
        ->where('voting_id', $voting->id)
        ->where('owner_id', $inPersonOwner->id)
        ->where('is_in_person', true)
        ->where('cast_delegate_dni', null)
        ->exists())->toBeTrue();
});

it('allows delegated-vote users to select in-person owner from public votings screen', function () {
    $delegatedUser = User::factory()->create();
    $delegatedUser->assignRole(Role::DELEGATED_VOTE);

    $inPersonOwner = Owner::factory()->create([
        'coprop1_name' => 'Presentziala Jabea',
    ]);

    $portal = Location::factory()->portal()->create(['code' => '33-I']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $inPersonOwner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
    ]);

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    Livewire::actingAs($delegatedUser)
        ->test(PublicVotings::class)
        ->assertSet('canCastVotes', false)
        ->assertSet('canManageDelegatedVoting', true)
        ->assertSee(__('votings.front.in_person_vote_button'))
        ->call('openInPersonVoteModal')
        ->assertSet('showInPersonModal', true)
        ->assertSee('Presentziala Jabea')
        ->call('startInPersonVote', $inPersonOwner->id)
        ->assertRedirect(route('votings.eu'));
});
