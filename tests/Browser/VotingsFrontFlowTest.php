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
use App\Models\PropertyAssignment;

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

test('superadmin can open votings front in read only mode', function () {
    $superadmin = User::query()->find(1) ?? User::factory()->create([
        'id' => 1,
        'email' => 'superadmin@example.com',
    ]);

    Role::query()->firstOrCreate([
        'name' => Role::SUPER_ADMIN,
    ]);

    $superadmin->assignRole(Role::SUPER_ADMIN);

    $voting = Voting::factory()->current()->create([
        'is_published' => true,
        'is_anonymous' => false,
    ]);

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'position' => 1,
        'label_eu' => 'Bai',
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($superadmin, $voting) {
        $browser->loginAs($superadmin)
            ->visit('/eu/bozketak')
            ->waitFor('[data-page="votings"]')
            ->assertPresent("[data-voting-card='{$voting->id}']")
            ->assertSee('Superadmin gisa bozketak ikus ditzakezu, baina ezin duzu bozkatu.')
            ->assertMissing("[data-vote-submit='{$voting->id}']");
    });
});

test('delegated-vote user sees delegated button on public votings and can open modal', function () {
    $delegatedUser = User::factory()->create();

    Role::query()->firstOrCreate([
        'name' => Role::DELEGATED_VOTE,
    ]);

    $delegatedUser->assignRole(Role::DELEGATED_VOTE);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Dusk Delegatua',
    ]);

    $portal = Location::factory()->portal()->create(['code' => '88-Z']);
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
    $this->browse(function (Browser $browser) use ($delegatedUser) {
        $browser->loginAs($delegatedUser)
            ->visit('/eu/bozketak')
            ->waitFor('[data-page="votings"]')
            ->assertPresent('[data-open-front-delegated-modal]')
            ->click('[data-open-front-delegated-modal]')
            ->waitFor('[data-front-delegated-modal]')
            ->assertPresent('[data-front-vote-as-owner]');
    });
});

test('delegated-vote user sees in-person button on public votings and can open modal', function () {
    $delegatedUser = User::factory()->create();

    Role::query()->firstOrCreate([
        'name' => Role::DELEGATED_VOTE,
    ]);

    $delegatedUser->assignRole(Role::DELEGATED_VOTE);

    $owner = Owner::factory()->create([
        'coprop1_name' => 'Dusk Presentziala',
    ]);

    $portal = Location::factory()->portal()->create(['code' => '88-Y']);
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
    $this->browse(function (Browser $browser) use ($delegatedUser) {
        $browser->loginAs($delegatedUser)
            ->visit('/eu/bozketak')
            ->waitFor('[data-page="votings"]')
            ->assertPresent('[data-open-front-in-person-modal]')
            ->click('[data-open-front-in-person-modal]')
            ->waitFor('[data-front-in-person-modal]')
            ->assertPresent('[data-front-in-person-vote-as-owner]');
    });
});
