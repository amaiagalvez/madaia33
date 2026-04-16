<?php

use App\Models\Owner;
use App\Models\Campaign;
use App\Models\Location;
use App\Models\Property;
use App\Models\CampaignRecipient;
use Illuminate\Cache\RateLimiter;
use App\Models\PropertyAssignment;
use App\Models\CampaignTrackingEvent;
use Illuminate\Support\Facades\Queue;
use Illuminate\Cache\RateLimiting\Limit;
use App\Contracts\Messaging\EmailProvider;
use App\Jobs\Messaging\DispatchCampaignJob;
use Illuminate\Queue\Middleware\RateLimited;
use App\Services\Messaging\RecipientResolver;
use App\Jobs\Messaging\SendCampaignMessageJob;
use App\Services\Messaging\MessageVariableResolver;
use App\Support\Messaging\RecipientContactHealthManager;

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
    ]);

    (new DispatchCampaignJob($campaign->id))->handle(new RecipientResolver);

    Queue::assertPushed(SendCampaignMessageJob::class, 2);
    expect(CampaignRecipient::query()->where('campaign_id', $campaign->id)->count())->toBe(2);
});

it('registers campaign email limiter at 10 messages per minute', function () {
    $limiter = app(RateLimiter::class)->limiter('campaign-email-send');

    expect($limiter)->not->toBeNull();

    $resolvedLimit = $limiter(new SendCampaignMessageJob(1));
    $limit = is_array($resolvedLimit) ? $resolvedLimit[0] : $resolvedLimit;

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->maxAttempts)->toBe(10)
        ->and($limit->decaySeconds)->toBe(60);
});

it('applies the campaign email queue rate limiter middleware', function () {
    $job = new SendCampaignMessageJob(1);
    $middleware = $job->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(RateLimited::class);

    $limiterName = (function (): string {
        return $this->limiterName;
    })->call($middleware[0]);

    expect($limiterName)->toBe('campaign-email-send');
});

it('dispatch campaign job reuses preloaded recipients without creating new rows', function () {
    Queue::fake();

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
        'status' => 'draft',
    ]);

    $firstRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'status' => 'pending',
    ]);

    $secondRecipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'status' => 'pending',
    ]);

    $countBeforeDispatch = CampaignRecipient::query()
        ->where('campaign_id', $campaign->id)
        ->count();

    (new DispatchCampaignJob($campaign->id))->handle(new RecipientResolver);

    Queue::assertPushed(SendCampaignMessageJob::class, 2);
    expect(CampaignRecipient::query()->where('campaign_id', $campaign->id)->count())->toBe($countBeforeDispatch);

    Queue::assertPushed(SendCampaignMessageJob::class, fn(SendCampaignMessageJob $job): bool => in_array($job->recipientId, [$firstRecipient->id, $secondRecipient->id], true));
});

it('dispatch campaign job creates whatsapp recipients without enqueueing send jobs', function () {
    Queue::fake();

    $owner = Owner::factory()->create([
        'coprop1_phone' => '600111222',
        'coprop2_phone' => '600333444',
        'coprop1_phone_invalid' => false,
        'coprop2_phone_invalid' => false,
    ]);

    $location = Location::factory()->portal()->create(['code' => 'P-21']);
    $property = Property::factory()->create(['location_id' => $location->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $owner->id,
        'property_id' => $property->id,
        'end_date' => null,
    ]);

    $campaign = Campaign::factory()->create([
        'channel' => 'whatsapp',
    ]);

    (new DispatchCampaignJob($campaign->id))->handle(new RecipientResolver);

    expect(CampaignRecipient::query()->where('campaign_id', $campaign->id)->count())->toBe(2);

    Queue::assertNotPushed(SendCampaignMessageJob::class);
});

it('dispatch campaign job reuses whatsapp recipients without enqueueing send jobs', function () {
    Queue::fake();

    $campaign = Campaign::factory()->create([
        'channel' => 'whatsapp',
        'status' => 'draft',
    ]);

    CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'status' => 'failed',
        'error_message' => 'old error',
    ]);

    CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'status' => 'sent',
        'error_message' => 'another old error',
    ]);

    (new DispatchCampaignJob($campaign->id))->handle(new RecipientResolver);

    $recipients = CampaignRecipient::query()
        ->where('campaign_id', $campaign->id)
        ->orderBy('id')
        ->get();

    expect($recipients)->toHaveCount(2)
        ->and($recipients->pluck('status')->all())->toBe(['pending', 'pending'])
        ->and($recipients->pluck('error_message')->all())->toBe([null, null]);

    Queue::assertNotPushed(SendCampaignMessageJob::class);
});

it('uses the owner language field for localized campaign content', function () {
    $sentPayload = (object) [
        'subject' => null,
        'body' => null,
    ];

    app()->bind(EmailProvider::class, fn() => new class($sentPayload) implements EmailProvider {
        public function __construct(private object $sentPayload) {}

        public function send(CampaignRecipient $recipient, string $subject, string $body): void
        {
            $this->sentPayload->subject = $subject;
            $this->sentPayload->body = $body;
        }
    });

    $owner = Owner::factory()->create([
        'language' => 'es',
    ]);

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
        'subject_eu' => 'Gaia EU',
        'subject_es' => 'Asunto ES',
        'body_eu' => 'Edukia EU',
        'body_es' => 'Contenido ES',
        'status' => 'sending',
    ]);

    $recipient = CampaignRecipient::factory()->create([
        'campaign_id' => $campaign->id,
        'owner_id' => $owner->id,
        'slot' => 'coprop1',
        'contact' => 'owner@example.test',
        'status' => 'pending',
    ]);

    (new SendCampaignMessageJob($recipient->id))->handle(
        new MessageVariableResolver,
        app(EmailProvider::class),
        app(RecipientContactHealthManager::class),
    );

    expect($sentPayload->subject)->toBe('Asunto ES')
        ->and($sentPayload->body)->toBe('Contenido ES');
});

it('records tracking event and increments owner counter on failed send', function () {
    app()->bind(EmailProvider::class, fn() => new class implements EmailProvider {
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

    (new SendCampaignMessageJob($recipient->id))->handle(
        new MessageVariableResolver,
        app(EmailProvider::class),
        app(RecipientContactHealthManager::class),
    );

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
    app()->bind(EmailProvider::class, fn() => new class implements EmailProvider {
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

    (new SendCampaignMessageJob($recipient->id))->handle(
        new MessageVariableResolver,
        app(EmailProvider::class),
        app(RecipientContactHealthManager::class),
    );

    $owner->refresh();

    expect($owner->coprop1_email_error_count)->toBe(0)
        ->and($owner->coprop1_email_invalid)->toBeFalse();

    app()->bind(EmailProvider::class, fn() => new class implements EmailProvider {
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

    (new SendCampaignMessageJob($secondRecipient->id))->handle(
        new MessageVariableResolver,
        app(EmailProvider::class),
        app(RecipientContactHealthManager::class),
    );

    $owner->refresh();

    expect($owner->coprop1_email_error_count)->toBe(3)
        ->and($owner->coprop1_email_invalid)->toBeTrue();
});

it('marks campaign as completed when all recipients are processed', function () {
    app()->bind(EmailProvider::class, fn() => new class implements EmailProvider {
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

    (new SendCampaignMessageJob($recipientOne->id))->handle(
        new MessageVariableResolver,
        app(EmailProvider::class),
        app(RecipientContactHealthManager::class),
    );

    $campaign->refresh();
    expect($campaign->status)->toBe('sending');

    (new SendCampaignMessageJob($recipientTwo->id))->handle(
        new MessageVariableResolver,
        app(EmailProvider::class),
        app(RecipientContactHealthManager::class),
    );

    $campaign->refresh();

    expect($campaign->status)->toBe('completed')
        ->and($campaign->sent_at)->not->toBeNull();
});
