<?php

use App\Models\Owner;
use App\Models\Campaign;
use App\Models\Location;
use App\Models\Property;
use App\Models\CampaignLocation;
use App\Models\PropertyAssignment;
use App\Services\Messaging\RecipientResolver;

it('resolves all recipients with valid email contacts', function () {
    $ownerWithTwoContacts = Owner::factory()->create([
        'coprop1_email' => 'coprop1@example.test',
        'coprop2_email' => 'coprop2@example.test',
        'coprop1_email_invalid' => false,
        'coprop2_email_invalid' => false,
    ]);

    $ownerWithNoValidContact = Owner::factory()->create([
        'coprop1_email' => 'invalid@example.test',
        'coprop2_email' => null,
        'coprop1_email_invalid' => true,
        'coprop2_email_invalid' => false,
    ]);

    $location = Location::factory()->portal()->create(['code' => 'P-01']);

    $propertyOne = Property::factory()->create(['location_id' => $location->id]);
    $propertyTwo = Property::factory()->create(['location_id' => $location->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerWithTwoContacts->id,
        'property_id' => $propertyOne->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerWithNoValidContact->id,
        'property_id' => $propertyTwo->id,
        'end_date' => null,
    ]);

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
    ]);

    $resolver = new RecipientResolver;

    $recipients = $resolver->resolve($campaign);

    expect($recipients)->toHaveCount(2)
        ->and($recipients->pluck('slot')->all())->toBe(['coprop1', 'coprop2'])
        ->and($recipients->pluck('contact')->all())->toBe([
            'coprop1@example.test',
            'coprop2@example.test',
        ]);
});

it('filters recipients by portal code', function () {
    $portalIncluded = Location::factory()->portal()->create(['code' => 'P-02']);
    $portalExcluded = Location::factory()->portal()->create(['code' => 'P-03']);

    $ownerIncluded = Owner::factory()->create([
        'coprop1_email' => 'included@example.test',
        'coprop1_email_invalid' => false,
    ]);

    $ownerExcluded = Owner::factory()->create([
        'coprop1_email' => 'excluded@example.test',
        'coprop1_email_invalid' => false,
    ]);

    $includedProperty = Property::factory()->create(['location_id' => $portalIncluded->id]);
    $excludedProperty = Property::factory()->create(['location_id' => $portalExcluded->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerIncluded->id,
        'property_id' => $includedProperty->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerExcluded->id,
        'property_id' => $excludedProperty->id,
        'end_date' => null,
    ]);

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
    ]);

    CampaignLocation::factory()->create([
        'campaign_id' => $campaign->id,
        'location_id' => $portalIncluded->id,
    ]);

    $resolver = new RecipientResolver;

    $recipients = $resolver->resolve($campaign);

    expect($recipients)->toHaveCount(1)
        ->and($recipients->first()['owner_id'])->toBe($ownerIncluded->id)
        ->and($recipients->first()['contact'])->toBe('included@example.test');
});

it('filters recipients by garage code', function () {
    $garageIncluded = Location::factory()->garage()->create(['code' => 'G-10']);
    $garageExcluded = Location::factory()->garage()->create(['code' => 'G-11']);

    $ownerIncluded = Owner::factory()->create([
        'coprop1_email' => 'garage-included@example.test',
        'coprop1_email_invalid' => false,
    ]);

    $ownerExcluded = Owner::factory()->create([
        'coprop1_email' => 'garage-excluded@example.test',
        'coprop1_email_invalid' => false,
    ]);

    $includedProperty = Property::factory()->create(['location_id' => $garageIncluded->id]);
    $excludedProperty = Property::factory()->create(['location_id' => $garageExcluded->id]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerIncluded->id,
        'property_id' => $includedProperty->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerExcluded->id,
        'property_id' => $excludedProperty->id,
        'end_date' => null,
    ]);

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
    ]);

    CampaignLocation::factory()->create([
        'campaign_id' => $campaign->id,
        'location_id' => $garageIncluded->id,
    ]);

    $resolver = new RecipientResolver;

    $recipients = $resolver->resolve($campaign);

    expect($recipients)->toHaveCount(1)
        ->and($recipients->first()['owner_id'])->toBe($ownerIncluded->id)
        ->and($recipients->first()['contact'])->toBe('garage-included@example.test');
});

it('filters recipients by multiple locations', function () {
    $portalIncluded = Location::factory()->portal()->create(['code' => 'P-20']);
    $garageIncluded = Location::factory()->garage()->create(['code' => 'G-20']);
    $portalExcluded = Location::factory()->portal()->create(['code' => 'P-21']);

    $ownerPortal = Owner::factory()->create([
        'coprop1_email' => 'portal-multi@example.test',
        'coprop1_email_invalid' => false,
    ]);

    $ownerGarage = Owner::factory()->create([
        'coprop1_email' => 'garage-multi@example.test',
        'coprop1_email_invalid' => false,
    ]);

    $ownerExcluded = Owner::factory()->create([
        'coprop1_email' => 'excluded-multi@example.test',
        'coprop1_email_invalid' => false,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerPortal->id,
        'property_id' => Property::factory()->create(['location_id' => $portalIncluded->id])->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerGarage->id,
        'property_id' => Property::factory()->create(['location_id' => $garageIncluded->id])->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerExcluded->id,
        'property_id' => Property::factory()->create(['location_id' => $portalExcluded->id])->id,
        'end_date' => null,
    ]);

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
    ]);

    CampaignLocation::factory()->create([
        'campaign_id' => $campaign->id,
        'location_id' => $portalIncluded->id,
    ]);

    CampaignLocation::factory()->create([
        'campaign_id' => $campaign->id,
        'location_id' => $garageIncluded->id,
    ]);

    $resolver = new RecipientResolver;

    $recipients = $resolver->resolve($campaign);

    expect($recipients)->toHaveCount(2)
        ->and($recipients->pluck('owner_id')->all())->toBe([$ownerPortal->id, $ownerGarage->id]);
});

it('generates zero one or two recipients depending on available contacts', function () {
    $location = Location::factory()->portal()->create(['code' => 'P-04']);

    $ownerZero = Owner::factory()->create([
        'coprop1_email' => 'zero@example.test',
        'coprop2_email' => null,
        'coprop1_email_invalid' => true,
        'coprop2_email_invalid' => false,
    ]);

    $ownerOne = Owner::factory()->create([
        'coprop1_email' => 'one@example.test',
        'coprop2_email' => null,
        'coprop1_email_invalid' => false,
        'coprop2_email_invalid' => false,
    ]);

    $ownerTwo = Owner::factory()->create([
        'coprop1_email' => 'two-1@example.test',
        'coprop2_email' => 'two-2@example.test',
        'coprop1_email_invalid' => false,
        'coprop2_email_invalid' => false,
    ]);

    foreach ([$ownerZero, $ownerOne, $ownerTwo] as $owner) {
        $property = Property::factory()->create(['location_id' => $location->id]);

        PropertyAssignment::factory()->create([
            'owner_id' => $owner->id,
            'property_id' => $property->id,
            'end_date' => null,
        ]);
    }

    $campaign = Campaign::factory()->create([
        'channel' => 'email',
    ]);

    $resolver = new RecipientResolver;

    $recipients = $resolver->resolve($campaign);

    expect($recipients)->toHaveCount(3)
        ->and($recipients->where('owner_id', $ownerZero->id))->toHaveCount(0)
        ->and($recipients->where('owner_id', $ownerOne->id))->toHaveCount(1)
        ->and($recipients->where('owner_id', $ownerTwo->id))->toHaveCount(2);
});

it('only resolves whatsapp recipients for owners with whatsapp enabled', function () {
    $location = Location::factory()->portal()->create(['code' => 'P-09']);

    $ownerWithWhatsapp = Owner::factory()->create([
        'coprop1_phone' => '600100100',
        'coprop1_phone_invalid' => false,
        'coprop1_has_whatsapp' => true,
    ]);

    $ownerWithoutWhatsapp = Owner::factory()->create([
        'coprop1_phone' => '600200200',
        'coprop1_phone_invalid' => false,
        'coprop1_has_whatsapp' => false,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerWithWhatsapp->id,
        'property_id' => Property::factory()->create(['location_id' => $location->id])->id,
        'end_date' => null,
    ]);

    PropertyAssignment::factory()->create([
        'owner_id' => $ownerWithoutWhatsapp->id,
        'property_id' => Property::factory()->create(['location_id' => $location->id])->id,
        'end_date' => null,
    ]);

    $campaign = Campaign::factory()->create([
        'channel' => 'whatsapp',
    ]);

    $resolver = new RecipientResolver;

    $recipients = $resolver->resolve($campaign);

    expect($recipients)->toHaveCount(1)
        ->and($recipients->first()['owner_id'])->toBe($ownerWithWhatsapp->id)
        ->and($recipients->first()['contact'])->toBe('600100100');
});
