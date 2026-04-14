<?php

use Livewire\Livewire;
use App\Models\Campaign;
use App\Models\Owner;
use App\Models\CampaignRecipient;
use App\Models\CampaignDocument;
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

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $downloaderRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'open',
        'url' => null,
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $downloaderRecipient->id,
        'campaign_document_id' => null,
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
        ->assertSet('metrics.clicks', 1)
        ->assertSet('metrics.downloads', 1)
        ->assertSet('metrics.failures', 1)
        ->assertSee('open@example.com')
        ->assertSee('download@example.com')
        ->assertSee('fail@example.com')
        ->call('toggleRecipientDetails', $openedRecipient->id)
        ->assertSet('expandedRecipientId', $openedRecipient->id)
        ->assertSee('https://example.test/info');
});

it('renders the admin detail page for a campaign', function () {
    $user = adminUser();
    $campaign = Campaign::factory()->create([
        'subject_eu' => 'Xehetasunak',
    ]);

    test()->actingAs($user)
        ->get(route('admin.campaigns.show', $campaign))
        ->assertOk()
        ->assertSee('Xehetasunak');
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
