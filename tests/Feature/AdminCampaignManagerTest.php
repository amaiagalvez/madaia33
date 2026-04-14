<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Campaign;
use App\Models\Location;

it('renders campaigns in the admin list', function () {
    $user = adminUser();

    Campaign::factory()->create([
        'subject_eu' => 'Kanpaina nagusia',
        'status' => 'draft',
        'channel' => 'email',
        'recipient_filter' => 'all',
    ]);

    Campaign::factory()->create([
        'subject_eu' => 'Programatutako kanpaina',
        'status' => 'scheduled',
        'channel' => 'sms',
        'recipient_filter' => 'all',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->assertSee('Kanpaina nagusia')
        ->assertSee('Programatutako kanpaina')
        ->assertSeeHtml('data-campaign-table');
});

it('validates required campaign fields', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->set('subjectEu', '')
        ->set('subjectEs', '')
        ->set('bodyEu', '')
        ->set('bodyEs', '')
        ->set('channel', '')
        ->set('recipientFilter', '')
        ->call('saveCampaign')
        ->assertHasErrors([
            'subjectEu',
            'subjectEs',
            'bodyEu',
            'bodyEs',
            'channel',
            'recipientFilter',
        ]);
});

it('duplicates a campaign into a new draft', function () {
    $user = adminUser();

    $sourceCampaign = Campaign::factory()->create([
        'subject_eu' => 'Jatorrizko kanpaina',
        'status' => 'completed',
        'channel' => 'email',
        'recipient_filter' => 'all',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('duplicateCampaign', $sourceCampaign->id);

    $duplicate = Campaign::query()
        ->whereKeyNot($sourceCampaign->id)
        ->where('subject_eu', 'Jatorrizko kanpaina')
        ->latest('id')
        ->first();

    expect($duplicate)->not->toBeNull()
        ->and($duplicate?->status)->toBe('draft');
});

it('shows edit and delete actions only for draft or scheduled campaigns', function () {
    $user = adminUser();

    $draftCampaign = Campaign::factory()->create(['status' => 'draft']);
    $scheduledCampaign = Campaign::factory()->create(['status' => 'scheduled']);
    $completedCampaign = Campaign::factory()->create(['status' => 'completed']);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->assertSeeHtml('wire:click="editCampaign(' . $draftCampaign->id . ')"')
        ->assertSeeHtml('wire:click="confirmDelete(' . $draftCampaign->id . ')"')
        ->assertSeeHtml('wire:click="editCampaign(' . $scheduledCampaign->id . ')"')
        ->assertSeeHtml('wire:click="confirmDelete(' . $scheduledCampaign->id . ')"')
        ->assertDontSeeHtml('wire:click="editCampaign(' . $completedCampaign->id . ')"')
        ->assertDontSeeHtml('wire:click="confirmDelete(' . $completedCampaign->id . ')"');
});

it('hides the all filter option from community admins', function () {
    Role::query()->firstOrCreate(['name' => Role::COMMUNITY_ADMIN]);

    $communityAdmin = User::factory()->create();
    $communityAdmin->assignRole(Role::COMMUNITY_ADMIN);

    $managedLocation = Location::factory()->portal()->create([
        'code' => 'CA-33',
        'name' => 'Portal CA-33',
    ]);

    $communityAdmin->managedLocations()->sync([$managedLocation->id]);

    Livewire::actingAs($communityAdmin)
        ->test('admin-campaign-manager')
        ->call('createCampaign')
        ->assertDontSeeHtml('value="all"')
        ->assertSee('CA-33');
});
