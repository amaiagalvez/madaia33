<?php

use Livewire\Livewire;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\CampaignTrackingEvent;

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
