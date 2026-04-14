<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Campaign;
use App\Models\Location;
use App\Models\CampaignDocument;
use App\Models\CampaignTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

it('shows translated recipient filters and the invalid contacts breadcrumb', function () {
    $user = adminUser();

    Campaign::factory()->create([
        'subject_eu' => 'Atariko mezua',
        'status' => 'draft',
        'channel' => 'email',
        'recipient_filter' => 'portal:P-33',
    ]);

    test()->actingAs($user)
        ->get(route('admin.campaigns'))
        ->assertOk()
        ->assertSee('P-33')
        ->assertDontSee('portal:P-33')
        ->assertDontSee('campaigns.admin.filters.portal');

    test()->actingAs($user)
        ->get(route('admin.campaigns.invalid-contacts'))
        ->assertOk()
        ->assertSee('data-campaign-breadcrumb', false)
        ->assertSee(__('admin.campaigns'))
        ->assertSee(__('campaigns.admin.invalid_contacts'))
        ->assertDontSee('campaigns.admin.');

    test()->actingAs($user)
        ->get(route('admin.campaigns.templates'))
        ->assertOk()
        ->assertSee(__('campaigns.admin.templates'))
        ->assertDontSee('campaigns.admin.');
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

it('duplicates a campaign into a new draft and opens the new form', function () {
    $user = adminUser();

    $sourceCampaign = Campaign::factory()->create([
        'subject_eu' => 'Jatorrizko kanpaina',
        'status' => 'completed',
        'channel' => 'email',
        'recipient_filter' => 'all',
    ]);

    $component = Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('duplicateCampaign', $sourceCampaign->id);

    $duplicate = Campaign::query()
        ->whereKeyNot($sourceCampaign->id)
        ->where('subject_eu', 'Jatorrizko kanpaina')
        ->latest('id')
        ->first();

    expect($duplicate)->not->toBeNull()
        ->and($duplicate?->status)->toBe('draft');

    $component->assertRedirect(route('admin.campaigns', ['editCampaign' => $duplicate->id]));
});

it('opens and closes the shared confirmation modal for campaign table actions', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
        'recipient_filter' => 'all',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('confirmAction', $campaign->id, 'send')
        ->assertSet('confirmingActionId', $campaign->id)
        ->assertSet('confirmingAction', 'send')
        ->assertSet('showActionModal', true)
        ->call('cancelAction')
        ->assertSet('confirmingActionId', null)
        ->assertSet('confirmingAction', '')
        ->assertSet('showActionModal', false);
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
        ->assertSeeHtml('wire:click="confirmAction(' . $draftCampaign->id . ',')
        ->assertSeeHtml("title=\"" . __('campaigns.admin.actions.duplicate') . "\"")
        ->assertSeeHtml("title=\"" . __('campaigns.admin.actions.send') . "\"")
        ->assertSeeHtml("title=\"" . __('campaigns.admin.actions.schedule') . "\"")
        ->assertSeeHtml('wire:click="editCampaign(' . $scheduledCampaign->id . ')"')
        ->assertSeeHtml('wire:click="confirmDelete(' . $scheduledCampaign->id . ')"')
        ->assertSeeHtml('wire:click="confirmAction(' . $scheduledCampaign->id . ',')
        ->assertSeeHtml("title=\"" . __('campaigns.admin.actions.cancel_schedule') . "\"")
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

it('saves the current campaign form as a reusable template', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('createCampaign')
        ->assertSee(__('campaigns.admin.actions.save_template'))
        ->assertSeeHtml('data-campaign-save-template')
        ->set('subjectEu', 'Batzarraren deialdia')
        ->set('subjectEs', 'Convocatoria de junta')
        ->set('bodyEu', 'Edukia prest dago.')
        ->set('bodyEs', 'El contenido está listo.')
        ->set('channel', 'email')
        ->call('saveAsTemplate')
        ->assertHasNoErrors();

    $template = CampaignTemplate::query()
        ->where('subject_eu', 'Batzarraren deialdia')
        ->where('subject_es', 'Convocatoria de junta')
        ->latest('id')
        ->first();

    expect($template)
        ->not->toBeNull()
        ->and($template?->body_eu)->toBe('Edukia prest dago.')
        ->and($template?->body_es)->toBe('El contenido está listo.')
        ->and($template?->channel)->toBe('email')
        ->and($template?->name)->not->toBe('');
});

it('stores uploaded campaign documents when saving a campaign', function () {
    Storage::fake('public');

    $user = adminUser();
    $attachment = UploadedFile::fake()->create('deialdia.pdf', 120, 'application/pdf');

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('createCampaign')
        ->set('subjectEu', 'Dokumentudun mezua')
        ->set('bodyEu', 'Dokumentua bidali da.')
        ->set('channel', 'email')
        ->set('recipientFilter', 'all')
        ->set('attachments', [$attachment])
        ->call('saveCampaign')
        ->assertHasNoErrors();

    $document = CampaignDocument::query()->latest('id')->first();

    expect($document)->not->toBeNull()
        ->and($document?->filename)->toBe('deialdia.pdf');

    expect(Storage::disk('public')->exists($document->path))->toBeTrue();
});

it('shows stored campaign documents when reopening the edit form', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
        'recipient_filter' => 'all',
    ]);

    CampaignDocument::factory()->create([
        'campaign_id' => $campaign->id,
        'filename' => 'acta-junta.pdf',
        'path' => 'campaign-documents/' . $campaign->id . '/acta-junta.pdf',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('editCampaign', $campaign->id)
        ->assertSee('acta-junta.pdf');
});

it('deletes a stored campaign document from the edit form', function () {
    Storage::fake('public');

    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
        'recipient_filter' => 'all',
    ]);

    Storage::disk('public')->put('campaign-documents/' . $campaign->id . '/acta-junta.pdf', 'dummy-content');

    $document = CampaignDocument::factory()->create([
        'campaign_id' => $campaign->id,
        'filename' => 'acta-junta.pdf',
        'path' => 'campaign-documents/' . $campaign->id . '/acta-junta.pdf',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('editCampaign', $campaign->id)
        ->call('removeStoredAttachment', $document->id)
        ->assertDontSee('acta-junta.pdf');

    expect(CampaignDocument::withTrashed()->find($document->id)?->trashed())->toBeTrue()
        ->and(Storage::disk('public')->exists($document->path))->toBeFalse();
});
