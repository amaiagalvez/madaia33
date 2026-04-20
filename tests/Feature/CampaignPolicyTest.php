<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Location;
use App\Models\CampaignLocation;
use App\Policies\CampaignPolicy;

it('allows superadmin and admin_general to send campaigns with all filter', function (string $role) {
    $policy = new CampaignPolicy;

    $user = User::factory()->create();
    Role::query()->firstOrCreate(['name' => $role]);
    $user->assignRole($role);

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
    ]);

    expect($policy->send($user, $campaign))->toBeTrue();
})->with([
    Role::SUPER_ADMIN,
    Role::GENERAL_ADMIN,
]);

it('denies admin_general for location-specific campaigns', function () {
    $policy = new CampaignPolicy;

    $user = User::factory()->create();
    Role::query()->firstOrCreate(['name' => Role::GENERAL_ADMIN]);
    $user->assignRole(Role::GENERAL_ADMIN);

    $location = Location::factory()->portal()->create(['name' => 'P-33']);

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
    ]);

    CampaignLocation::factory()->create([
        'campaign_id' => $campaign->id,
        'location_id' => $location->id,
    ]);

    expect($policy->view($user, $campaign))->toBeFalse()
        ->and($policy->send($user, $campaign))->toBeFalse()
        ->and($policy->duplicate($user, $campaign))->toBeFalse();
});

it('denies admin_comunidad when using all filter', function () {
    $policy = new CampaignPolicy;

    $user = User::factory()->create();
    Role::query()->firstOrCreate(['name' => Role::COMMUNITY_ADMIN]);
    $user->assignRole(Role::COMMUNITY_ADMIN);

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
    ]);

    expect($policy->send($user, $campaign))->toBeFalse();
});

it('denies admin_comunidad for unmanaged location filters', function () {
    $policy = new CampaignPolicy;

    $user = User::factory()->create();
    Role::query()->firstOrCreate(['name' => Role::COMMUNITY_ADMIN]);
    $user->assignRole(Role::COMMUNITY_ADMIN);

    $managedLocation = Location::factory()->portal()->create(['name' => 'P-50']);
    $unmanagedLocation = Location::factory()->portal()->create(['name' => 'P-51']);

    $user->managedLocations()->attach($managedLocation->id);

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
    ]);

    CampaignLocation::factory()->create([
        'campaign_id' => $campaign->id,
        'location_id' => $unmanagedLocation->id,
    ]);

    expect($policy->send($user, $campaign))->toBeFalse();
});

it('denies admin_comunidad when a multi-location filter contains unmanaged locations', function () {
    $policy = new CampaignPolicy;

    $user = User::factory()->create();
    Role::query()->firstOrCreate(['name' => Role::COMMUNITY_ADMIN]);
    $user->assignRole(Role::COMMUNITY_ADMIN);

    $managedPortal = Location::factory()->portal()->create(['name' => 'P-70']);
    $unmanagedGarage = Location::factory()->garage()->create(['name' => 'G-71']);

    $user->managedLocations()->attach($managedPortal->id);

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
    ]);

    CampaignLocation::factory()->create([
        'campaign_id' => $campaign->id,
        'location_id' => $managedPortal->id,
    ]);

    CampaignLocation::factory()->create([
        'campaign_id' => $campaign->id,
        'location_id' => $unmanagedGarage->id,
    ]);

    expect($policy->send($user, $campaign))->toBeFalse();
});

it('allows update and delete only when campaign is draft or scheduled', function () {
    $policy = new CampaignPolicy;

    $user = User::factory()->create();
    Role::query()->firstOrCreate(['name' => Role::GENERAL_ADMIN]);
    $user->assignRole(Role::GENERAL_ADMIN);

    $draftCampaign = Campaign::factory()->create(['status' => 'draft']);
    $scheduledCampaign = Campaign::factory()->create(['status' => 'scheduled']);
    $completedCampaign = Campaign::factory()->create(['status' => 'completed']);

    expect($policy->update($user, $draftCampaign))->toBeTrue()
        ->and($policy->delete($user, $draftCampaign))->toBeTrue()
        ->and($policy->update($user, $scheduledCampaign))->toBeTrue()
        ->and($policy->delete($user, $scheduledCampaign))->toBeTrue()
        ->and($policy->update($user, $completedCampaign))->toBeFalse()
        ->and($policy->delete($user, $completedCampaign))->toBeFalse();
});
