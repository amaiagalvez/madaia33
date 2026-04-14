<?php

use App\Models\Owner;
use Livewire\Livewire;
use App\Models\Campaign;
use App\Models\CampaignDocument;
use App\Models\CampaignRecipient;
use App\Models\CampaignTrackingEvent;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Messaging\SendCampaignMessageJob;

it('shows unique campaign metrics and recipient detail rows', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'subject_eu' => 'Jarraipen kanpaina',
        'channel' => 'email',
        'status' => 'completed',
    ]);

    $openedRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'contact' => 'open@example.com',
        'status' => 'sent',
    ]);

    $downloaderRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'contact' => 'download@example.com',
        'status' => 'sent',
    ]);

    $failedRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'contact' => 'fail@example.com',
        'status' => 'failed',
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $openedRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'open',
        'url' => null,
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $openedRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'open',
        'url' => null,
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $openedRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'click',
        'url' => 'https://example.test/info',
    ]);

    $document = CampaignDocument::factory()->create([
        'campaign_id' => $campaign->id,
        'filename' => 'acta-marzo.pdf',
    ]);

    $secondDocument = CampaignDocument::factory()->create([
        'campaign_id' => $campaign->id,
        'filename' => 'presupuesto-2026.pdf',
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $downloaderRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'open',
        'url' => null,
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $downloaderRecipient->id,
        'campaign_document_id' => $document->id,
        'event_type' => 'download',
        'url' => null,
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $downloaderRecipient->id,
        'campaign_document_id' => $document->id,
        'event_type' => 'download',
        'url' => null,
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $downloaderRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'click',
        'url' => 'https://example.test/info',
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $downloaderRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'click',
        'url' => 'https://example.test/docs',
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $openedRecipient->id,
        'campaign_document_id' => $secondDocument->id,
        'event_type' => 'download',
        'url' => null,
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $failedRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'error',
        'url' => null,
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaign])
        ->assertSet('metrics.total', 3)
        ->assertSet('metrics.opens', 2)
        ->assertSet('metrics.clicks', 2)
        ->assertSet('metrics.downloads', 2)
        ->assertSet('metrics.failures', 1)
        ->assertSee('66,7%')
        ->assertSee('https://example.test/info')
        ->assertSee('https://example.test/docs')
        ->assertSee('acta-marzo.pdf')
        ->assertSee('presupuesto-2026.pdf')
        ->assertSee('2×')
        ->assertSee('open@example.com')
        ->assertSee('download@example.com')
        ->assertSee('fail@example.com')
        ->call('toggleRecipientDetails', $openedRecipient->id)
        ->assertSet('expandedRecipientId', $openedRecipient->id)
        ->assertSee('https://example.test/info')
        ->call('toggleRecipientDetails', $downloaderRecipient->id)
        ->assertSet('expandedRecipientId', $downloaderRecipient->id)
        ->assertSee('acta-marzo.pdf');
});

it('renders the admin detail page with breadcrumb context for a campaign', function () {
    $user = adminUser();
    $campaign = Campaign::factory()->create([
        'subject_eu' => 'Xehetasunak',
    ]);

    test()->actingAs($user)
        ->get(route('admin.campaigns.show', $campaign))
        ->assertOk()
        ->assertSee('Xehetasunak')
        ->assertSee(route('admin.campaigns'), false)
        ->assertSee('data-campaign-breadcrumb', false);
});

it('shows an edit shortcut for recipients with an owner profile', function () {
    $user = adminUser();
    $owner = Owner::factory()->create([
        'coprop1_name' => 'Amaia',
        'coprop1_surname' => 'Arregi',
    ]);

    $campaign = Campaign::factory()->create([
        'subject_eu' => 'Kanpaina propietariarekin',
    ]);

    CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'contact' => 'amaia@example.com',
        'slot' => 'coprop1',
    ]);

    test()->actingAs($user)
        ->get(route('admin.campaigns.show', $campaign))
        ->assertOk()
        ->assertSee(route('admin.owners.index', ['editOwner' => $owner->id]), false)
        ->assertSee('target="_blank"', false)
        ->assertSee('data-campaign-owner-edit-' . $owner->id, false);
});

it('shows resend action only when campaign is completed and has unopened recipients', function () {
    $user = adminUser();

    $campaignWithUnopened = Campaign::factory()->create([
        'status' => 'completed',
        'channel' => 'email',
    ]);

    $openedRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaignWithUnopened->id,
    ]);

    CampaignRecipient::factory()->create([
        'campaign_id' => $campaignWithUnopened->id,
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $openedRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'open',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaignWithUnopened])
        ->assertSee('50,0%')
        ->assertSeeHtml('data-campaign-resend-unopened');

    $campaignAllOpened = Campaign::factory()->create([
        'status' => 'completed',
        'channel' => 'email',
    ]);

    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaignAllOpened->id,
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $recipient->id,
        'campaign_document_id' => null,
        'event_type' => 'open',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaignAllOpened])
        ->assertDontSeeHtml('data-campaign-resend-unopened')
        ->assertSeeHtml('data-campaign-all-opened-notice');
});

it('opens and closes resend confirmation modal before requeueing unopened recipients', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'completed',
        'channel' => 'email',
    ]);

    CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'status' => 'sent',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaign])
        ->call('confirmResendToUnopened')
        ->assertSet('showResendModal', true)
        ->call('cancelResendToUnopened')
        ->assertSet('showResendModal', false);
});

it('resends only unopened recipients in the same campaign', function () {
    Queue::fake();

    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'status' => 'completed',
        'channel' => 'email',
        'subject_eu' => 'Jatorrizko gaia',
    ]);

    $openedOwner = Owner::factory()->create();
    $unopenedOwner = Owner::factory()->create();

    $openedRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $openedOwner->id,
        'slot' => 'coprop1',
        'contact' => 'opened@example.test',
        'status' => 'sent',
    ]);

    $unopenedRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $unopenedOwner->id,
        'slot' => 'coprop2',
        'contact' => 'unopened@example.test',
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $openedRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'open',
    ]);

    CampaignDocument::factory()->create([
        'campaign_id' => $campaign->id,
        'filename' => 'deialdia.pdf',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaign])
        ->call('resendToUnopened');

    $campaign->refresh();
    $openedRecipient->refresh();
    $unopenedRecipient->refresh();

    expect($campaign->status)->toBe('sending')
        ->and(Campaign::query()->count())->toBe(1)
        ->and(CampaignDocument::query()->where('campaign_id', $campaign->id)->count())->toBe(1)
        ->and($openedRecipient->status)->not->toBe('pending')
        ->and($unopenedRecipient->status)->toBe('pending');

    Queue::assertPushed(SendCampaignMessageJob::class, 1);
    Queue::assertPushed(SendCampaignMessageJob::class, fn(SendCampaignMessageJob $job): bool => $job->recipientId === $unopenedRecipient->id);
});
