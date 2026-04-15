<?php

use App\Models\User;
use App\Models\Owner;
use App\Models\Campaign;
use App\Models\CampaignDocument;
use App\Models\CampaignRecipient;
use App\Models\CampaignTrackingEvent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('defines expected campaign relationships', function () {
    $campaign = new Campaign;

    expect($campaign->recipients())->toBeInstanceOf(HasMany::class)
        ->and($campaign->recipients()->getRelated())->toBeInstanceOf(CampaignRecipient::class)
        ->and($campaign->documents())->toBeInstanceOf(HasMany::class)
        ->and($campaign->documents()->getRelated())->toBeInstanceOf(CampaignDocument::class)
        ->and($campaign->createdBy())->toBeInstanceOf(BelongsTo::class)
        ->and($campaign->createdBy()->getRelated())->toBeInstanceOf(User::class);
});

it('defines expected campaign recipient relationships', function () {
    $recipient = new CampaignRecipient;

    expect($recipient->campaign())->toBeInstanceOf(BelongsTo::class)
        ->and($recipient->campaign()->getRelated())->toBeInstanceOf(Campaign::class)
        ->and($recipient->owner())->toBeInstanceOf(BelongsTo::class)
        ->and($recipient->owner()->getRelated())->toBeInstanceOf(Owner::class)
        ->and($recipient->trackingEvents())->toBeInstanceOf(HasMany::class)
        ->and($recipient->trackingEvents()->getRelated())->toBeInstanceOf(CampaignTrackingEvent::class);
});

it('does not use soft deletes in campaign tracking events', function () {
    expect(class_uses_recursive(CampaignTrackingEvent::class))->not->toContain(SoftDeletes::class);
});
