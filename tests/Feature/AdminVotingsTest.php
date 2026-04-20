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
use App\Livewire\Admin\Votings;
use App\Models\VotingSelection;
use App\Models\PropertyAssignment;

beforeEach(function () {
    foreach (Role::names() as $roleName) {
        Role::query()->firstOrCreate([
            'name' => $roleName,
        ]);
    }
});

it('allows superadmin id 1 to access admin votings route', function () {
    $superadmin = User::factory()->create([
        'id' => 1,
        'email' => 'superadmin@example.com',
    ]);
    $superadmin->assignRole(Role::SUPER_ADMIN);

    test()->actingAs($superadmin)
        ->get(route('admin.votings'))
        ->assertOk();
});

it('redirects admin pdf download with selected voting ids', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $firstVoting = Voting::factory()->current()->create();
    $secondVoting = Voting::factory()->current()->create();

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->set('selectedVotingIds', [(string) $firstVoting->id, (string) $secondVoting->id])
        ->call('downloadDelegatedPdf')
        ->assertRedirect(route('admin.votings.pdf.delegated', ['voting_ids' => [$firstVoting->id, $secondVoting->id]]));
});

it('redirects admin results pdf download with selected voting ids', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $firstVoting = Voting::factory()->current()->create();
    $secondVoting = Voting::factory()->current()->create();

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->set('selectedVotingIds', [(string) $firstVoting->id, (string) $secondVoting->id])
        ->call('downloadResultsPdf')
        ->assertRedirect(route('admin.votings.pdf.results', ['voting_ids' => [$firstVoting->id, $secondVoting->id]]));
});

it('shows an error when downloading admin pdf without selecting votings', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('downloadDelegatedPdf')
        ->assertSee(__('votings.admin.select_for_pdf_required'));
});

it('allows general admin to access admin votings route', function () {
    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    test()->actingAs($generalAdmin)
        ->get(route('admin.votings'))
        ->assertOk();
});

it('shows only global votings for general admin users', function () {
    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    $visibleVoting = Voting::factory()->create([
        'name_eu' => 'Bozketa Orokorra',
        'name_es' => 'Votacion Global',
    ]);

    $hiddenVoting = Voting::factory()->create([
        'name_eu' => 'Bozketa Kokatua',
        'name_es' => 'Votacion Ubicada',
    ]);

    $location = Location::factory()->portal()->create();
    $hiddenVoting->locations()->create(['location_id' => $location->id]);

    Livewire::actingAs($generalAdmin)
        ->test(Votings::class)
        ->assertSee('Bozketa Orokorra')
        ->assertDontSee('Bozketa Kokatua')
        ->call('editVoting', $hiddenVoting->id)
        ->assertForbidden();
});

it('shows only managed-location votings for community admin users', function () {
    $communityAdmin = User::factory()->create();
    $communityAdmin->assignRole(Role::COMMUNITY_ADMIN);

    $managedLocation = Location::factory()->portal()->create(['name' => 'CM-1']);
    $otherLocation = Location::factory()->portal()->create(['name' => 'CM-2']);
    $communityAdmin->managedLocations()->sync([$managedLocation->id]);

    $managedVoting = Voting::factory()->create([
        'name_eu' => 'Kudeatutako Bozketa',
        'name_es' => 'Votacion Gestionada',
    ]);
    $managedVoting->locations()->create(['location_id' => $managedLocation->id]);

    $otherVoting = Voting::factory()->create([
        'name_eu' => 'Kanpoko Bozketa',
        'name_es' => 'Votacion Externa',
    ]);
    $otherVoting->locations()->create(['location_id' => $otherLocation->id]);

    Livewire::actingAs($communityAdmin)
        ->test(Votings::class)
        ->assertSee('Kudeatutako Bozketa')
        ->assertDontSee('Kanpoko Bozketa')
        ->call('editVoting', $otherVoting->id)
        ->assertForbidden();
});

it('hides owner names in census and voters modals for non super admin users', function () {
    app()->setLocale('es');

    $communityAdmin = User::factory()->create();
    $communityAdmin->assignRole(Role::COMMUNITY_ADMIN);

    $owner = Owner::factory()->create(['coprop1_name' => 'Propietaria Oculta']);
    $location = Location::factory()->portal()->create(['name' => 'HID-1']);
    $property = Property::factory()->create(['location_id' => $location->id]);

    $communityAdmin->managedLocations()->sync([$location->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create(['is_published' => true, 'name_es' => 'Votacion Privada']);
    $voting->locations()->create(['location_id' => $location->id]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $ballot = VotingBallot::factory()->create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => $communityAdmin->id,
    ]);

    VotingSelection::factory()->create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'voting_ballot_id' => $ballot->id,
        'voting_option_id' => $option->id,
    ]);

    Livewire::actingAs($communityAdmin)
        ->test(Votings::class)
        ->call('openCensus', $voting->id)
        ->assertDontSee('Propietaria Oculta')
        ->assertSee('—')
        ->call('openVoters', $voting->id)
        ->assertDontSee('Propietaria Oculta')
        ->assertSee('—');
});

it('shows active votings with census and votes in admin component', function () {
    app()->setLocale('es');

    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create();
    $portal = Location::factory()->portal()->create(['name' => '88-A']);
    $property = Property::factory()->create(['location_id' => $portal->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create([
        'name_eu' => 'Aurrekontua 2026',
        'name_es' => 'Presupuesto 2026',
        'is_published' => true,
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'label_eu' => 'Ostirala 19:30ean',
        'label_es' => 'Viernes a las 19:30',
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    $ballot = VotingBallot::create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => null,
        'voted_at' => now(),
    ]);

    VotingSelection::create([
        'voting_id' => $voting->id,
        'voting_ballot_id' => $ballot->id,
        'owner_id' => $owner->id,
        'voting_option_id' => $option->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openCensus', $voting->id)
        ->assertSet('ownersModalContext', 'census')
        ->assertSee('Presupuesto 2026')
        ->assertDontSee('data-owners-modal-vote-column', false)
        ->assertDontSee('data-owners-modal-delegate-dni-column', false)
        ->assertDontSee('data-owners-modal-delegated-by-column', false);
});

it('shows delegated voter name in voters modal for audit visibility', function () {
    app()->setLocale('es');

    $admin = User::factory()->create(['name' => 'Admin Delegatua']);
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create(['coprop1_name' => 'Jabe Delegatua']);
    $portal = Location::factory()->portal()->create(['name' => '99-A']);
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
        'label_eu' => 'Ostirala 19:30ean',
        'label_es' => 'Viernes a las 19:30',
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    $ballot = VotingBallot::create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => $admin->id,
        'voted_at' => now(),
    ]);

    VotingSelection::create([
        'voting_id' => $voting->id,
        'voting_ballot_id' => $ballot->id,
        'owner_id' => $owner->id,
        'voting_option_id' => $option->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openVoters', $voting->id)
        ->assertSet('showOwnersModal', true)
        ->assertSee('Jabe Delegatua')
        ->assertSee('Viernes a las 19:30')
        ->assertSee('Admin Delegatua');
});

it('shows casting user name in voters modal for in-person ballots', function () {
    app()->setLocale('es');

    $admin = User::factory()->create(['name' => 'Admin Presentziala']);
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create(['coprop1_name' => 'Jabe Presentziala']);
    $portal = Location::factory()->portal()->create(['name' => 'PRES-01']);
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
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    $ballot = VotingBallot::create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => $admin->id,
        'is_in_person' => true,
        'voted_at' => now(),
    ]);

    VotingSelection::create([
        'voting_id' => $voting->id,
        'voting_ballot_id' => $ballot->id,
        'owner_id' => $owner->id,
        'voting_option_id' => $option->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openVoters', $voting->id)
        ->assertSet('showOwnersModal', true)
        ->assertSet('ownersModalRows.0.delegated_by', 'Admin Presentziala')
        ->assertSee('Jabe Presentziala')
        ->assertSee('Admin Presentziala');
});

it('keeps historical eligible owners in census modal and only voters in voters modal', function () {
    app()->setLocale('es');

    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $portal = Location::factory()->portal()->create(['name' => '77-H']);
    $propertyA = Property::factory()->create(['location_id' => $portal->id, 'name' => 'P-1']);
    $propertyB = Property::factory()->create(['location_id' => $portal->id, 'name' => 'P-2']);

    $voting = Voting::factory()->create([
        'name_eu' => 'Historiako bozketa',
        'name_es' => 'Votacion historica',
        'starts_at' => now()->subMonths(2)->startOfDay(),
        'ends_at' => now()->subMonths(2)->addDays(2)->startOfDay(),
        'is_published' => true,
        'is_anonymous' => false,
    ]);
    $voting->locations()->create(['location_id' => $portal->id]);

    $voterOwner = Owner::factory()->create(['coprop1_name' => 'Propietaria Historica 1']);
    $nonVoterOwner = Owner::factory()->create(['coprop1_name' => 'Propietaria Historica 2']);

    PropertyAssignment::factory()->create([
        'owner_id' => $voterOwner->id,
        'property_id' => $propertyA->id,
        'start_date' => now()->subMonths(4),
        'end_date' => now()->subMonth(),
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $nonVoterOwner->id,
        'property_id' => $propertyB->id,
        'start_date' => now()->subMonths(4),
        'end_date' => now()->subMonth(),
    ]);

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'label_eu' => 'Astelehena 18:00etan',
        'label_es' => 'Lunes a las 18:00',
        'position' => 1,
    ]);

    $ballot = VotingBallot::create([
        'voting_id' => $voting->id,
        'owner_id' => $voterOwner->id,
        'cast_by_user_id' => $admin->id,
        'voted_at' => now()->subMonths(2)->addDay(),
    ]);

    VotingSelection::create([
        'voting_id' => $voting->id,
        'voting_ballot_id' => $ballot->id,
        'owner_id' => $voterOwner->id,
        'voting_option_id' => $option->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openCensus', $voting->id)
        ->assertSee('Propietaria Historica 1')
        ->assertSee('Propietaria Historica 2');

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openVoters', $voting->id)
        ->assertSee('Propietaria Historica 1')
        ->assertDontSee('Propietaria Historica 2')
        ->assertSee('Lunes a las 18:00');
});

it('forbids admin voting manager access for owner accounts', function () {
    $ownerUser = User::factory()->create();
    Owner::factory()->for($ownerUser)->create();

    Livewire::actingAs($ownerUser)
        ->test(Votings::class)
        ->assertForbidden();
});

it('filters delegated vote modal by owner and location search terms', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $ownerPortal = Owner::factory()->create([
        'coprop1_name' => 'Ane Koop1',
        'coprop2_name' => 'Ane Koop2',
    ]);

    $ownerGarage = Owner::factory()->create([
        'coprop1_name' => 'Bea Koop1',
        'coprop2_name' => 'Bea Koop2',
    ]);

    $portal = Location::factory()->portal()->create(['name' => 'P-301']);
    $garage = Location::factory()->garage()->create(['name' => 'G-88']);

    $portalProperty = Property::factory()->create(['location_id' => $portal->id]);
    $garageProperty = Property::factory()->create(['location_id' => $garage->id]);

    $portalProperty->update(['community_pct' => 1.25]);
    $garageProperty->update(['community_pct' => 2.50]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerPortal->id,
        'property_id' => $portalProperty->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerGarage->id,
        'property_id' => $garageProperty->id,
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
    $voting->locations()->create(['location_id' => $garage->id]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openDelegatedVoteModal')
        ->assertSee('Ane Koop1')
        ->assertSee('Bea Koop1')
        ->assertSee('1,25%')
        ->assertSee('2,50%')
        ->set('delegatedSearch', 'P-301')
        ->assertSee('Ane Koop1')
        ->assertDontSee('Bea Koop1')
        ->set('delegatedSearch', 'Bea Koop2')
        ->assertSee('Bea Koop1')
        ->assertDontSee('Ane Koop1');

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openInPersonVoteModal')
        ->assertSee('Ane Koop1')
        ->assertSee('Bea Koop1')
        ->assertSee('1,25%')
        ->assertSee('2,50%');
});

it('starts delegated vote and redirects to public votings route', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Delegazio Jabea',
    ]);

    $portal = Location::factory()->portal()->create(['name' => 'P-777']);
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
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    Livewire::actingAs($admin)
        ->withQueryParams([])
        ->test(Votings::class)
        ->call('openDelegatedVoteModal')
        ->call('startDelegatedVote', $owner->id)
        ->assertRedirect(route('votings.eu'));

    expect(session('delegated_voting_owner_id'))->toBe($owner->id);
});

it('allows editing an existing voting from admin component', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $portal = Location::factory()->portal()->create(['name' => 'P-501']);

    $voting = Voting::factory()->current()->create([
        'name_eu' => 'Hasierako izena',
        'question_eu' => 'Hasierako galdera',
        'is_published' => false,
        'is_anonymous' => false,
    ]);

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'label_eu' => 'Aukera zaharra',
        'position' => 1,
    ]);

    $voting->locations()->create(['location_id' => $portal->id]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('editVoting', $voting->id)
        ->set('nameEu', 'Izen eguneratua')
        ->set('questionEu', 'Galdera eguneratua')
        ->set('isPublished', true)
        ->set('options', [
            ['labelEu' => 'Aukera berria', 'labelEs' => 'Opcion nueva'],
        ])
        ->call('saveVoting')
        ->assertSet('showCreateForm', false);

    $voting->refresh();

    expect($voting->name_eu)->toBe('Izen eguneratua')
        ->and($voting->question_eu)->toBe('Galdera eguneratua')
        ->and($voting->is_published)->toBeTrue()
        ->and($voting->options()->count())->toBe(1)
        ->and($voting->options()->first()?->label_eu)->toBe('Aukera berria')
        ->and($voting->options()->first()?->label_es)->toBe('Opcion nueva');
});

it('allows deleting a voting when no ballots exist', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $voting = Voting::factory()->current()->create();

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('confirmDeleteVoting', $voting->id)
        ->call('deleteVoting')
        ->assertSet('showDeleteModal', false)
        ->assertSee(__('general.messages.deleted'));

    expect(Voting::query()->whereKey($voting->id)->exists())->toBeFalse();
});

it('prevents deleting a voting when ballots already exist', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    $owner = Owner::factory()->create();

    $voting = Voting::factory()->current()->create();

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    VotingBallot::create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => null,
        'voted_at' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('confirmDeleteVoting', $voting->id)
        ->call('deleteVoting')
        ->assertSet('showDeleteModal', false)
        ->assertSee(__('votings.admin.delete_blocked_with_votes'));

    expect(Voting::query()->whereKey($voting->id)->exists())->toBeTrue();
});

it('allows general admin to create edit and delete global votings', function () {
    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    Livewire::actingAs($generalAdmin)
        ->test(Votings::class)
        ->call('createVoting')
        ->set('nameEu', 'GENERAL_ADMIN Bozketa')
        ->set('questionEu', 'GENERAL_ADMIN galdera?')
        ->set('startsAt', now()->addDay()->format('Y-m-d'))
        ->set('endsAt', now()->addDays(7)->format('Y-m-d'))
        ->set('options', [['labelEu' => 'Bai', 'labelEs' => 'Sí']])
        ->call('saveVoting')
        ->assertSet('showCreateForm', false);

    $voting = Voting::query()->where('name_eu', 'GENERAL_ADMIN Bozketa')->firstOrFail();
    expect($voting->locations()->count())->toBe(0);

    Livewire::actingAs($generalAdmin)
        ->test(Votings::class)
        ->call('editVoting', $voting->id)
        ->set('nameEu', 'GENERAL_ADMIN Bozketa Eguneratua')
        ->call('saveVoting')
        ->assertSet('showCreateForm', false);

    expect($voting->fresh()->name_eu)->toBe('GENERAL_ADMIN Bozketa Eguneratua');

    Livewire::actingAs($generalAdmin)
        ->test(Votings::class)
        ->call('confirmDeleteVoting', $voting->id)
        ->call('deleteVoting')
        ->assertSet('showDeleteModal', false);

    expect(Voting::query()->whereKey($voting->id)->exists())->toBeFalse();
});

it('hides owner names in census and voters modals for general admin users', function () {
    app()->setLocale('es');

    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    $owner = Owner::factory()->create(['coprop1_name' => 'Propietaria General Oculta']);
    $location = Location::factory()->portal()->create(['name' => 'GA-999']);
    $property = Property::factory()->create(['location_id' => $location->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $voting = Voting::factory()->current()->create(['is_published' => true, 'name_es' => 'Votacion Global Privada']);
    // No locations → global voting visible to GENERAL_ADMIN

    $option = VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
    ]);

    $ballot = VotingBallot::factory()->create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'cast_by_user_id' => $generalAdmin->id,
    ]);

    VotingSelection::factory()->create([
        'voting_id' => $voting->id,
        'owner_id' => $owner->id,
        'voting_ballot_id' => $ballot->id,
        'voting_option_id' => $option->id,
    ]);

    Livewire::actingAs($generalAdmin)
        ->test(Votings::class)
        ->call('openCensus', $voting->id)
        ->assertDontSee('Propietaria General Oculta')
        ->assertSee('—')
        ->call('openVoters', $voting->id)
        ->assertDontSee('Propietaria General Oculta')
        ->assertSee('—');
});

it('allows community admin to create edit and delete managed location votings', function () {
    $communityAdmin = User::factory()->create();
    $communityAdmin->assignRole(Role::COMMUNITY_ADMIN);

    $managedLocation = Location::factory()->portal()->create(['name' => 'CA-CRUD']);
    $communityAdmin->managedLocations()->sync([$managedLocation->id]);

    Livewire::actingAs($communityAdmin)
        ->test(Votings::class)
        ->call('createVoting')
        ->set('nameEu', 'COMMUNITY_ADMIN Bozketa')
        ->set('questionEu', 'COMMUNITY_ADMIN galdera?')
        ->set('startsAt', now()->addDay()->format('Y-m-d'))
        ->set('endsAt', now()->addDays(7)->format('Y-m-d'))
        ->set('options', [['labelEu' => 'Bai', 'labelEs' => 'Sí']])
        ->set('selectedLocations', [(string) $managedLocation->id])
        ->call('saveVoting')
        ->assertSet('showCreateForm', false);

    $voting = Voting::query()->where('name_eu', 'COMMUNITY_ADMIN Bozketa')->firstOrFail();
    expect($voting->locations()->count())->toBe(1);

    Livewire::actingAs($communityAdmin)
        ->test(Votings::class)
        ->call('editVoting', $voting->id)
        ->set('nameEu', 'COMMUNITY_ADMIN Bozketa Eguneratua')
        ->call('saveVoting')
        ->assertSet('showCreateForm', false);

    expect($voting->fresh()->name_eu)->toBe('COMMUNITY_ADMIN Bozketa Eguneratua');

    Livewire::actingAs($communityAdmin)
        ->test(Votings::class)
        ->call('confirmDeleteVoting', $voting->id)
        ->call('deleteVoting')
        ->assertSet('showDeleteModal', false);

    expect(Voting::query()->whereKey($voting->id)->exists())->toBeFalse();
});

it('persists bilingual questions and allows creating a second voting', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SUPER_ADMIN);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('createVoting')
        ->set('nameEu', 'Lehen bozketa')
        ->set('questionEu', '<p>Lehen galdera EU</p>')
        ->set('questionEs', '<p>Primera pregunta ES</p>')
        ->set('startsAt', now()->addDay()->format('Y-m-d'))
        ->set('endsAt', now()->addDays(7)->format('Y-m-d'))
        ->set('options', [['labelEu' => 'Bai', 'labelEs' => 'Si']])
        ->call('saveVoting')
        ->assertHasNoErrors()
        ->assertSet('showCreateForm', false)
        ->call('createVoting')
        ->set('nameEu', 'Bigarren bozketa')
        ->set('questionEu', '<p>Bigarren galdera EU</p>')
        ->set('questionEs', '<p>Segunda pregunta ES</p>')
        ->set('startsAt', now()->addDays(2)->format('Y-m-d'))
        ->set('endsAt', now()->addDays(8)->format('Y-m-d'))
        ->set('options', [['labelEu' => 'Ez', 'labelEs' => 'No']])
        ->call('saveVoting')
        ->assertHasNoErrors()
        ->assertSet('showCreateForm', false);

    $firstVoting = Voting::query()->where('name_eu', 'Lehen bozketa')->firstOrFail();
    $secondVoting = Voting::query()->where('name_eu', 'Bigarren bozketa')->firstOrFail();

    expect($firstVoting->question_eu)->toBe('<p>Lehen galdera EU</p>')
        ->and($firstVoting->question_es)->toBe('<p>Primera pregunta ES</p>')
        ->and($secondVoting->question_eu)->toBe('<p>Bigarren galdera EU</p>')
        ->and($secondVoting->question_es)->toBe('<p>Segunda pregunta ES</p>');
});

it('forbids non-superadmin users from opening in-person and delegated vote modals', function (string $role) {
    $admin = User::factory()->create();
    $admin->assignRole($role);

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openInPersonVoteModal')
        ->assertForbidden();

    Livewire::actingAs($admin)
        ->test(Votings::class)
        ->call('openDelegatedVoteModal')
        ->assertForbidden();
})->with([
    Role::GENERAL_ADMIN,
    Role::COMMUNITY_ADMIN,
]);
