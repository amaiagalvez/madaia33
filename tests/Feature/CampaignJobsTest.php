<?php

use App\Models\Owner;
use App\Models\Campaign;
use App\Models\Location;
use App\Models\Property;
use App\Models\CampaignRecipient;
use App\Models\PropertyAssignment;
use App\Models\CampaignTrackingEvent;
use Illuminate\Support\Facades\Queue;
use App\Contracts\Messaging\EmailProvider;
use App\Jobs\Messaging\DispatchCampaignJob;
use App\Services\Messaging\RecipientResolver;
use App\Jobs\Messaging\SendCampaignMessageJob;
use App\Services\Messaging\MessageVariableResolver;

it('dispatch campaign job enqueues one send job per resolved recipient', function () {
    Queue::fake();

    $owner = Owner::factory()->create([
        'coprop1_email' => 'one@example.test',
        'coprop2_email' => 'two@example.test',
        'coprop1_email_invalid' => false,
        'coprop2_email_invalid' => false,
    ]);

    $location = Location::factory()->portal()->create(['code' => 'P-20']);
    $property = Property::factory()->create(['location_id' => $location->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
        'recipient_filter' => 'all',
    ]);

    (new DispatchCampaignJob($campaign->id))->handle(new RecipientResolver);

    Queue::assertPushed(SendCampaignMessageJob::class, 2);
    expect(CampaignRecipient::query()->where('campaign_id', $campaign->id)->count())->toBe(2);
});

it('records tracking event and increments owner counter on failed send', function () {
    app()->bind(EmailProvider::class, fn () => new class implements EmailProvider {
        public function send(CampaignRecipient $recipient, string $subject, string $body): void
        {
            throw new RuntimeException('delivery failed');
        }
    });

    $owner = Owner::factory()->create([
        'coprop1_email_error_count' => 0,
        'coprop1_email_invalid' => false,
    ]);

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
        'subject_eu' => 'Kaixo **nombre**',
        'body_eu' => 'Mezua',
        'status' => 'sending',
    ]);

    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'contact' => 'owner@example.test',
        'status' => 'pending',
    ]);

    (new SendCampaignMessageJob($recipient->id))->handle(new MessageVariableResolver, app(EmailProvider::class));

    $owner->refresh();
    $recipient->refresh();

    expect($recipient->status)->toBe('failed')
        ->and($owner->coprop1_email_error_count)->toBe(1)
        ->and($owner->coprop1_email_invalid)->toBeFalse();

    expect(CampaignTrackingEvent::query()
        ->where('campaign_recipient_id', $recipient->id)
        ->where('event_type', 'error')
        ->exists())->toBeTrue();
});

it('resets owner counter on successful send and marks contact invalid on third failure', function () {
    app()->bind(EmailProvider::class, fn () => new class implements EmailProvider {
        public function send(CampaignRecipient $recipient, string $subject, string $body): void {}
    });

    $owner = Owner::factory()->create([
        'coprop1_email_error_count' => 2,
        'coprop1_email_invalid' => true,
    ]);

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
        'subject_eu' => 'Kaixo',
        'body_eu' => 'Mezua',
        'status' => 'sending',
    ]);

    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'status' => 'pending',
    ]);

    (new SendCampaignMessageJob($recipient->id))->handle(new MessageVariableResolver, app(EmailProvider::class));

    $owner->refresh();

    expect($owner->coprop1_email_error_count)->toBe(0)
        ->and($owner->coprop1_email_invalid)->toBeFalse();

    app()->bind(EmailProvider::class, fn () => new class implements EmailProvider {
        public function send(CampaignRecipient $recipient, string $subject, string $body): void
        {
            throw new RuntimeException('delivery failed');
        }
    });

    $owner->update([
        'coprop1_email_error_count' => 2,
        'coprop1_email_invalid' => false,
    ]);

    $secondRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'status' => 'pending',
    ]);

    (new SendCampaignMessageJob($secondRecipient->id))->handle(new MessageVariableResolver, app(EmailProvider::class));

    $owner->refresh();

    expect($owner->coprop1_email_error_count)->toBe(3)
        ->and($owner->coprop1_email_invalid)->toBeTrue();
});

it('marks campaign as completed when all recipients are processed', function () {
    app()->bind(EmailProvider::class, fn () => new class implements EmailProvider {
        public function send(CampaignRecipient $recipient, string $subject, string $body): void {}
    });

    $owner = Owner::factory()->create();

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
        'subject_eu' => 'Kaixo',
        'body_eu' => 'Mezua',
        'status' => 'sending',
        'sent_at' => null,
    ]);

    $recipientOne = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'status' => 'pending',
    ]);

    $recipientTwo = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'status' => 'pending',
    ]);

    (new SendCampaignMessageJob($recipientOne->id))->handle(new MessageVariableResolver, app(EmailProvider::class));

    $campaign->refresh();
    expect($campaign->status)->toBe('sending');

    (new SendCampaignMessageJob($recipientTwo->id))->handle(new MessageVariableResolver, app(EmailProvider::class));

    $campaign->refresh();

    expect($campaign->status)->toBe('completed')
        ->and($campaign->sent_at)->not->toBeNull();
});
