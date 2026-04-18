<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use App\Models\Setting;
use App\Models\Campaign;
use App\Models\Location;
use App\Mail\CampaignMail;
use Illuminate\Support\Carbon;

use function Pest\Laravel\mock;

use App\Models\CampaignDocument;
use App\Models\CampaignLocation;
use App\Models\CampaignTemplate;
use App\Models\CampaignRecipient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Models\CampaignTrackingEvent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Messaging\DispatchCampaignJob;
use App\Services\Messaging\RecipientResolver;

it('renders campaigns in the admin list', function () {
    $user = adminUser();

    Campaign::factory()->create([
        'subject_eu' => 'Kanpaina nagusia',
        'status' => 'draft',
        'channel' => 'email',
    ]);

    Campaign::factory()->create([
        'subject_eu' => 'Programatutako kanpaina',
        'status' => 'scheduled',
        'channel' => 'sms',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->assertSee('Kanpaina nagusia')
        ->assertSee('Programatutako kanpaina')
        ->assertSeeHtml('data-campaign-table');
});

it('shows translated recipient filters and the invalid contacts breadcrumb', function () {
    $user = adminUser();

    $location = Location::factory()->portal()->create(['code' => 'P-33']);

    $campaign = Campaign::factory()->create([
        'subject_eu' => 'Atariko mezua',
        'status' => 'draft',
        'channel' => 'email',
    ]);

    CampaignLocation::factory()->create([
        'campaign_id' => $campaign->id,
        'location_id' => $location->id,
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
        ->set('recipientFilters', [])
        ->call('saveCampaign')
        ->assertHasErrors([
            'subjectEu',
            'subjectEs',
            'bodyEu',
            'bodyEs',
            'channel',
            'recipientFilters',
        ]);
});

it('duplicates a campaign into a new draft and opens the new form', function () {
    $user = adminUser();

    $sourceCampaign = Campaign::factory()->create([
        'subject_eu' => 'Jatorrizko kanpaina',
        'status' => 'completed',
        'channel' => 'email',
    ]);

    CampaignRecipient::factory()->create([
        'campaign_id' => $sourceCampaign->id,
        'contact' => 'existing-recipient@example.test',
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
        ->and($duplicate?->status)->toBe('draft')
        ->and(CampaignRecipient::query()->where('campaign_id', $duplicate->id)->count())->toBe(0);

    $component->assertRedirect(route('admin.campaigns', ['editCampaign' => $duplicate->id]));
});

it('opens and closes the shared confirmation modal for campaign table actions', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
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

it('starts queue worker when sending a campaign from the manager', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
    ]);

    mock(RecipientResolver::class)
        ->shouldReceive('resolve')
        ->once()
        ->andReturn(Collection::make([['owner_id' => 1, 'slot' => 'A1', 'contact' => 'test@example.com']]));

    Bus::fake();

    Artisan::shouldReceive('call')
        ->once()
        ->with('queue:work', [
            '--stop-when-empty' => true,
        ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('sendCampaign', $campaign->id);

    Bus::assertDispatched(DispatchCampaignJob::class, fn(DispatchCampaignJob $job): bool => $job->campaignId === $campaign->id);
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
        ->assertSeeHtml('title="' . __('campaigns.admin.actions.duplicate') . '"')
        ->assertSeeHtml('title="' . __('campaigns.admin.actions.send') . '"')
        ->assertSeeHtml('title="' . __('campaigns.admin.actions.schedule') . '"')
        ->assertSeeHtml('wire:click="editCampaign(' . $scheduledCampaign->id . ')"')
        ->assertSeeHtml('wire:click="confirmDelete(' . $scheduledCampaign->id . ')"')
        ->assertSeeHtml('wire:click="confirmAction(' . $scheduledCampaign->id . ',')
        ->assertSeeHtml('title="' . __('campaigns.admin.actions.cancel_schedule') . '"')
        ->assertDontSeeHtml('wire:click="editCampaign(' . $completedCampaign->id . ')"')
        ->assertDontSeeHtml('wire:click="confirmDelete(' . $completedCampaign->id . ')"');
});

it('limits general admins to unfiltered campaigns only', function () {
    Role::query()->firstOrCreate(['name' => Role::GENERAL_ADMIN]);

    $generalAdmin = User::factory()->create();
    $generalAdmin->assignRole(Role::GENERAL_ADMIN);

    $managedLocation = Location::factory()->portal()->create([
        'code' => 'GA-01',
        'name' => 'Portal GA-01',
    ]);

    $hiddenCampaign = Campaign::factory()->create([
        'subject_eu' => 'Kanpaina murriztua',
        'status' => 'draft',
        'channel' => 'email',
    ]);

    CampaignLocation::factory()->create([
        'campaign_id' => $hiddenCampaign->id,
        'location_id' => $managedLocation->id,
    ]);

    // Verify campaign id=1 is hidden from general admin
    test()->actingAs($generalAdmin)
        ->get(route('admin.campaigns.show', 1))
        ->assertForbidden();

    // Verify campaign with location is hidden from general admin
    test()->actingAs($generalAdmin)
        ->get(route('admin.campaigns.show', $hiddenCampaign))
        ->assertForbidden();
});

it('allows superadmin to see campaign id=1', function () {
    $superAdmin = adminUser();

    // Create campaign id=1 explicitly (normally created by DirectMessagesCampaignSeeder)
    Campaign::factory()->create([
        'id' => 1,
        'subject_eu' => 'Web-etik Bidalitako Mezuak',
        'subject_es' => 'Mensajes enviados desde la web',
        'body_eu' => null,
        'body_es' => null,
        'channel' => 'email',
        'status' => 'sent',
        'created_by_user_id' => null,
    ]);

    test()->actingAs($superAdmin)
        ->get(route('admin.campaigns.show', 1))
        ->assertOk();

    Livewire::actingAs($superAdmin)
        ->test('admin-campaign-manager')
        ->assertSee('Web-etik Bidalitako Mezuak'); // Campaign id=1
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

it('does not preselect the all owners filter on new campaigns', function () {
    $user = adminUser();

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('createCampaign')
        ->assertSet('recipientFilters', []);
});

it('redirects property owners and delegated vote users away from campaigns', function (string $role) {
    Role::query()->firstOrCreate(['name' => $role]);

    $user = User::factory()->create();
    $user->assignRole($role);

    test()->actingAs($user)
        ->get(route('admin.campaigns'))
        ->assertRedirect();
})->with([
    Role::PROPERTY_OWNER,
    Role::DELEGATED_VOTE,
]);

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
        ->set('recipientFilters', ['all'])
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

it('stores multiple location recipient filters in a single campaign', function () {
    $user = adminUser();

    $portal = Location::factory()->portal()->create(['code' => 'P-01']);
    $garage = Location::factory()->garage()->create(['code' => 'G-02']);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('createCampaign')
        ->set('subjectEu', 'Filtro anizkoitza')
        ->set('bodyEu', 'Bi kokalekutarako mezua')
        ->set('channel', 'email')
        ->set('recipientFilters', [(string) $portal->id, (string) $garage->id])
        ->call('saveCampaign')
        ->assertHasNoErrors();

    $campaign = Campaign::query()->latest('id')->first();

    expect($campaign)->not->toBeNull();

    $savedLocationIds = CampaignLocation::query()
        ->where('campaign_id', $campaign->id)
        ->whereNull('deleted_at')
        ->pluck('location_id')
        ->sort()
        ->values()
        ->all();

    expect($savedLocationIds)->toBe([(int) $portal->id, (int) $garage->id]);
});

it('shows campaign test-email button only while editing and opens modal', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('createCampaign')
        ->assertDontSeeHtml('data-campaign-test-email-button')
        ->call('editCampaign', $campaign->id)
        ->assertSeeHtml('data-campaign-test-email-button')
        ->call('openTestEmailModal')
        ->assertSet('showTestEmailModal', true)
        ->assertSeeHtml('data-campaign-test-email-modal')
        ->call('closeTestEmailModal')
        ->assertSet('showTestEmailModal', false);
});

it('sends campaign test emails in basque and spanish to the provided address', function () {
    Mail::fake();

    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
        'subject_eu' => 'Gaia EU',
        'subject_es' => 'Asunto ES',
        'body_eu' => '<p>Edukia EU</p>',
        'body_es' => '<p>Contenido ES</p>',
    ]);

    Setting::query()->updateOrCreate(
        ['key' => 'smtp_host'],
        ['value' => 'smtp.example.test', 'section' => Setting::SECTION_EMAIL_CONFIGURATION],
    );

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('editCampaign', $campaign->id)
        ->set('testEmailAddress', 'preview@example.com')
        ->call('sendTestEmail')
        ->assertHasNoErrors()
        ->assertSet('showTestEmailModal', false);

    Mail::assertSent(CampaignMail::class, 2);

    Mail::assertSent(CampaignMail::class, function (CampaignMail $mail): bool {
        return $mail->hasTo('preview@example.com')
            && $mail->subjectText === '[FROGA] Gaia EU'
            && $mail->htmlBody === '<p>Edukia EU</p>';
    });

    Mail::assertSent(CampaignMail::class, function (CampaignMail $mail): bool {
        return $mail->hasTo('preview@example.com')
            && $mail->subjectText === '[PRUEBA] Asunto ES'
            && $mail->htmlBody === '<p>Contenido ES</p>';
    });
});

it('blocks campaign test emails while the edit form has unsaved changes', function () {
    Mail::fake();

    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
        'subject_eu' => 'Gordetako gaia',
        'body_eu' => 'Gordetako edukia',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('editCampaign', $campaign->id)
        ->assertSet('hasUnsavedChanges', false)
        ->set('subjectEu', 'Aldatutako gaia')
        ->assertSet('hasUnsavedChanges', true)
        ->call('openTestEmailModal')
        ->assertSet('showTestEmailModal', false)
        ->assertHasErrors(['sendTestEmail'])
        ->set('testEmailAddress', 'preview@example.com')
        ->call('sendTestEmail')
        ->assertHasErrors(['sendTestEmail'])
        ->assertSet('showTestEmailModal', false);

    Mail::assertNothingSent();
});

it('schedules a draft campaign using the selected date and time', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
        'scheduled_at' => null,
    ]);

    $when = now()->addDay()->setSecond(0);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('openScheduleModal', $campaign->id)
        ->assertSet('showScheduleModal', true)
        ->set('scheduleAtInput', $when->format('Y-m-d\TH:i'))
        ->call('saveSchedule')
        ->assertHasNoErrors()
        ->assertSet('showScheduleModal', false);

    $campaign->refresh();

    expect($campaign->status)->toBe('scheduled')
        ->and($campaign->scheduled_at?->format('Y-m-d H:i'))->toBe(Carbon::parse($when)->format('Y-m-d H:i'));
});

it('rejects schedule dates earlier than now with translated message', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'draft',
        'channel' => 'email',
        'scheduled_at' => null,
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('openScheduleModal', $campaign->id)
        ->set('scheduleAtInput', now()->subMinute()->format('Y-m-d\TH:i'))
        ->call('saveSchedule')
        ->assertHasErrors(['scheduleAtInput'])
        ->assertSee(__('campaigns.admin.schedule_modal.after_or_equal'));
});

it('does not dispatch job and shows warning when campaign has no recipients', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create(['status' => 'draft', 'channel' => 'email']);

    mock(RecipientResolver::class)
        ->shouldReceive('resolve')
        ->once()
        ->andReturn(Collection::make([]));

    Bus::fake();

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->call('sendCampaign', $campaign->id)
        ->assertSee(__('campaigns.admin.no_recipients_warning'));

    Bus::assertNotDispatched(DispatchCampaignJob::class);
    $campaign->refresh();
    expect($campaign->status)->toBe('draft');
});

it('shows opened recipients count in campaigns list', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'subject_eu' => 'Irekierak neurtu',
        'status' => 'completed',
    ]);

    $openedRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'tracking_token' => 'open-token-1',
    ]);

    CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'tracking_token' => 'open-token-2',
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $openedRecipient->id,
        'event_type' => 'open',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-manager')
        ->assertSee(__('campaigns.admin.opened_messages_count'))
        ->assertSee('1');
});
