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

it('shows recipient message subject in contact column only for campaign id 1', function () {
    $user = adminUser();

    $campaignOne = Campaign::query()->find(1);

    if ($campaignOne === null) {
        $campaignOne = Campaign::factory()->create([
            'id' => 1,
            'channel' => 'email',
            'status' => 'completed',
        ]);
    } else {
        $campaignOne->forceFill([
            'channel' => 'email',
            'status' => 'completed',
        ])->save();
    }

    $campaignOneRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaignOne->id,
        'contact' => 'subject-one@example.com',
        'message_subject' => 'Asunto visible en campaña 1',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaignOne])
        ->assertSee('Asunto visible en campaña 1')
        ->assertSeeHtml('data-campaign-contact-subject-' . $campaignOneRecipient->id);

    $otherCampaign = Campaign::factory()->create([
        'channel' => 'email',
        'status' => 'completed',
    ]);

    $otherCampaignRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $otherCampaign->id,
        'contact' => 'subject-other@example.com',
        'message_subject' => 'Asunto no visible fuera de campaña 1',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $otherCampaign])
        ->assertDontSee('Asunto no visible fuera de campaña 1')
        ->assertDontSeeHtml('data-campaign-contact-subject-' . $otherCampaignRecipient->id);
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
    Queue::assertPushed(SendCampaignMessageJob::class, fn (SendCampaignMessageJob $job): bool => $job->recipientId === $unopenedRecipient->id);
});

it('shows whatsapp click-to-chat with tracked links and marks recipient as sent', function () {
    $user = adminUser();
    $owner = Owner::factory()->create([
        'coprop1_phone' => '+34 600 11 22 33',
        'coprop1_phone_error_count' => 2,
        'coprop1_phone_invalid' => false,
    ]);

    $campaign = Campaign::factory()->create([
        'status' => 'completed',
        'channel' => 'whatsapp',
        'body_eu' => '<p>Kaixo komunitatea</p><p><a href="https://example.org/info">Informazioa</a></p>',
        'body_es' => null,
    ]);

    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'contact' => '+34 600 11 22 33',
        'tracking_token' => 'token-whatsapp-1',
        'status' => 'pending',
    ]);

    $document = CampaignDocument::factory()->create([
        'campaign_id' => $campaign->id,
        'filename' => 'deialdia.pdf',
    ]);

    $trackedClickUrl = route('tracking.click', [
        'token' => $recipient->tracking_token,
        'url' => 'https://example.org/info',
    ]);

    $trackedDocumentUrl = route('tracking.document', [
        'token' => $recipient->tracking_token,
        'document' => $document->id,
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaign])
        ->assertSeeHtml('data-campaign-whatsapp-send-' . $recipient->id)
        ->call('sendWhatsappMessage', $recipient->id)
        ->assertDispatched('open-whatsapp')
        ->assertSee(__('campaigns.admin.messages.whatsapp_marked_sent'));

    $recipient->refresh();
    $owner->refresh();
    $campaign->refresh();

    $trackingEvent = CampaignTrackingEvent::query()
        ->where('campaign_recipient_id', $recipient->id)
        ->where('event_type', 'whatsapp_sent')
        ->latest()
        ->first();

    expect($recipient->status)->toBe('sent')
        ->and($trackingEvent)->not->toBeNull()
        ->and($trackingEvent?->url)->toStartWith('https://wa.me/34600112233?text=')
        ->and($trackingEvent?->url)->toContain(rawurlencode($trackedClickUrl))
        ->and($trackingEvent?->url)->toContain(rawurlencode($trackedDocumentUrl))
        ->and($campaign->sent_at)->not->toBeNull()
        ->and($owner->coprop1_phone_error_count)->toBe(0)
        ->and($owner->coprop1_phone_invalid)->toBeFalse();
});

it('marks whatsapp contact as invalid after the third failed attempt and blocks future sends', function () {
    $user = adminUser();
    $owner = Owner::factory()->create([
        'coprop1_phone' => 'abc',
        'coprop1_phone_error_count' => 2,
        'coprop1_phone_invalid' => false,
    ]);

    $campaign = Campaign::factory()->create([
        'status' => 'completed',
        'channel' => 'whatsapp',
        'body_eu' => '<p>Kaixo komunitatea</p>',
        'body_es' => null,
    ]);

    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'contact' => 'abc',
        'tracking_token' => 'token-whatsapp-error',
        'status' => 'pending',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaign])
        ->call('sendWhatsappMessage', $recipient->id)
        ->assertSee(__('campaigns.admin.messages.whatsapp_contact_blocked'));

    $recipient->refresh();
    $owner->refresh();

    expect($recipient->status)->toBe('failed')
        ->and($recipient->error_message)->toBe(__('campaigns.admin.messages.whatsapp_invalid_contact'))
        ->and($owner->coprop1_phone_error_count)->toBe(3)
        ->and($owner->coprop1_phone_invalid)->toBeTrue()
        ->and(CampaignTrackingEvent::query()
            ->where('campaign_recipient_id', $recipient->id)
            ->where('event_type', 'error')
            ->exists())->toBeTrue();

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaign])
        ->assertDontSeeHtml('data-campaign-whatsapp-send-' . $recipient->id)
        ->assertSeeHtml('data-campaign-whatsapp-blocked-' . $recipient->id);
});

it('shows only whatsapp_sent count in total metric for whatsapp campaigns', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'channel' => 'whatsapp',
        'status' => 'sending',
    ]);

    $sentRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'contact' => '+34600111222',
        'status' => 'sent',
    ]);

    $pendingRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'contact' => '+34600333444',
        'status' => 'pending',
    ]);

    CampaignTrackingEvent::factory()->create([
        'campaign_recipient_id' => $sentRecipient->id,
        'campaign_document_id' => null,
        'event_type' => 'whatsapp_sent',
        'url' => 'https://wa.me/34600111222?text=Kaixo',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaign])
        ->assertSet('metrics.total', 1);

    expect($pendingRecipient->status)->toBe('pending');
});

it('shows mark-sent button for manual channel pending recipients and marks them as sent', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'channel' => 'manual',
        'status' => 'sending',
    ]);

    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'contact' => 'manual',
        'status' => 'pending',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaign])
        ->assertSeeHtml('data-campaign-manual-mark-' . $recipient->id)
        ->assertDontSeeHtml('data-campaign-manual-sent-' . $recipient->id)
        ->call('markManualRecipientSent', $recipient->id)
        ->assertDontSeeHtml('data-campaign-manual-mark-' . $recipient->id)
        ->assertSeeHtml('data-campaign-manual-sent-' . $recipient->id);

    $recipient->refresh();
    expect($recipient->status)->toBe('sent')
        ->and($recipient->sent_at)->not->toBeNull();
});

it('does not show manual mark-sent button for non-manual channels', function () {
    $user = adminUser();

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
        'status' => 'completed',
    ]);

    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'contact' => 'owner@example.com',
        'status' => 'pending',
    ]);

    Livewire::actingAs($user)
        ->test('admin-campaign-detail', ['campaign' => $campaign])
        ->assertDontSeeHtml('data-campaign-manual-mark-' . $recipient->id);
});
