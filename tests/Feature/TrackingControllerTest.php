<?php

use App\Models\User;
use App\Models\Owner;
use App\Models\Campaign;
use App\Models\CampaignDocument;
use App\Models\CampaignRecipient;
use App\Models\CampaignTrackingEvent;
use Illuminate\Support\Facades\Storage;

it('returns 1x1 tracking image and stores open event', function () {
    $owner = Owner::factory()->create();
    $campaign = Campaign::factory()->create();
    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'tracking_token' => 'token-open-1',
    ]);

    $response = test()->get(route('tracking.open', ['token' => $recipient->tracking_token]));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'image/gif');

    expect(CampaignTrackingEvent::query()
        ->where('campaign_recipient_id', $recipient->id)
        ->where('event_type', 'open')
        ->exists())->toBeTrue();
});

it('redirects to destination URL and stores click event', function () {
    $owner = Owner::factory()->create();
    $campaign = Campaign::factory()->create();
    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'tracking_token' => 'token-click-1',
    ]);

    $response = test()->get(route('tracking.click', [
        'token' => $recipient->tracking_token,
        'url' => 'https://example.org/path',
    ]));

    $response->assertRedirect('https://example.org/path');

    expect(CampaignTrackingEvent::query()
        ->where('campaign_recipient_id', $recipient->id)
        ->where('event_type', 'click')
        ->where('url', 'https://example.org/path')
        ->exists())->toBeTrue()
        ->and(CampaignTrackingEvent::query()
            ->where('campaign_recipient_id', $recipient->id)
            ->where('event_type', 'open')
            ->exists())->toBeTrue();
});

it('serves public document without authentication and stores download event', function () {
    Storage::fake('public');

    $owner = Owner::factory()->create();
    $campaign = Campaign::factory()->create();
    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'tracking_token' => 'token-doc-public',
    ]);

    Storage::disk('public')->put('campaign-documents/test-public.pdf', 'dummy-content');

    $document = CampaignDocument::factory()->create([
        'campaign_id' => $campaign->id,
        'filename' => 'test-public.pdf',
        'path' => 'campaign-documents/test-public.pdf',
        'is_public' => true,
    ]);

    $response = test()->get(route('tracking.document', [
        'token' => $recipient->tracking_token,
        'document' => $document->id,
    ]));

    $response->assertSuccessful();

    expect(CampaignTrackingEvent::query()
        ->where('campaign_recipient_id', $recipient->id)
        ->where('campaign_document_id', $document->id)
        ->where('event_type', 'download')
        ->exists())->toBeTrue()
        ->and(CampaignTrackingEvent::query()
            ->where('campaign_recipient_id', $recipient->id)
            ->where('event_type', 'open')
            ->exists())->toBeTrue();
});

it('allows recipients with a valid tracking token to access private documents without login', function () {
    Storage::fake('public');

    $owner = Owner::factory()->create();
    $campaign = Campaign::factory()->create();
    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'tracking_token' => 'token-doc-private',
    ]);

    Storage::disk('public')->put('campaign-documents/test-private.pdf', 'dummy-content');

    $document = CampaignDocument::factory()->create([
        'campaign_id' => $campaign->id,
        'filename' => 'test-private.pdf',
        'path' => 'campaign-documents/test-private.pdf',
        'is_public' => false,
    ]);

    $response = test()->get(route('tracking.document', [
        'token' => $recipient->tracking_token,
        'document' => $document->id,
    ]));

    $response->assertSuccessful();

    expect(CampaignTrackingEvent::query()
        ->where('campaign_recipient_id', $recipient->id)
        ->where('campaign_document_id', $document->id)
        ->where('event_type', 'download')
        ->exists())->toBeTrue()
        ->and(CampaignTrackingEvent::query()
            ->where('campaign_recipient_id', $recipient->id)
            ->where('event_type', 'open')
            ->exists())->toBeTrue();
});

it('does not duplicate open events when click is tracked after an open', function () {
    $owner = Owner::factory()->create();
    $campaign = Campaign::factory()->create();
    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'tracking_token' => 'token-click-opened',
    ]);

    CampaignTrackingEvent::query()->create([
        'campaign_recipient_id' => $recipient->id,
        'campaign_document_id' => null,
        'event_type' => 'open',
        'url' => null,
        'ip_address' => '127.0.0.1',
    ]);

    $response = test()->get(route('tracking.click', [
        'token' => $recipient->tracking_token,
        'url' => 'https://example.org/already-opened',
    ]));

    $response->assertRedirect('https://example.org/already-opened');

    expect(CampaignTrackingEvent::query()
        ->where('campaign_recipient_id', $recipient->id)
        ->where('event_type', 'open')
        ->count())->toBe(1);
});

it('returns 404 for invalid tracking tokens', function () {
    $campaign = Campaign::factory()->create();
    $document = CampaignDocument::factory()->create([
        'campaign_id' => $campaign->id,
    ]);

    test()->get(route('tracking.open', ['token' => 'missing-token']))->assertNotFound();
    test()->get(route('tracking.click', ['token' => 'missing-token', 'url' => 'https://example.org']))->assertNotFound();
    test()->get(route('tracking.document', ['token' => 'missing-token', 'document' => $document->id]))->assertNotFound();
});

it('allows authenticated users to access private documents', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $owner = Owner::factory()->create();
    $campaign = Campaign::factory()->create();
    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'tracking_token' => 'token-doc-auth',
    ]);

    Storage::disk('public')->put('campaign-documents/test-auth.pdf', 'dummy-content');

    $document = CampaignDocument::factory()->create([
        'campaign_id' => $campaign->id,
        'filename' => 'test-auth.pdf',
        'path' => 'campaign-documents/test-auth.pdf',
        'is_public' => false,
    ]);

    $response = test()->actingAs($user)->get(route('tracking.document', [
        'token' => $recipient->tracking_token,
        'document' => $document->id,
    ]));

    $response->assertSuccessful();
});
