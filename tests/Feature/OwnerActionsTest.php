<?php

use App\Models\User;
use App\Models\Owner;
use App\Models\Location;
use App\Models\Property;
use App\Mail\OwnerWelcomeMail;
use App\Actions\Owners\CreateOwnerAction;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\Mail;
use App\Actions\Owners\DeactivateOwnerAction;
use Illuminate\Validation\ValidationException;

describe('CreateOwnerAction', function () {
    it('creates an owner with the required data', function () {
        Mail::fake();

        $action = new CreateOwnerAction;
        $owner = $action->execute([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_dni' => '12345678Z',
            'coprop1_phone' => '600111222',
            'coprop1_email' => 'miren@example.com',
        ]);

        expect($owner)->toBeInstanceOf(Owner::class)
            ->and($owner->coprop1_name)->toBe('Miren Etxeberria')
            ->and($owner->coprop1_dni)->toBe('12345678Z')
            ->and($owner->coprop1_email)->toBe('miren@example.com');
    });

    it('creates a user linked to the owner with DNI as name', function () {
        Mail::fake();

        $action = new CreateOwnerAction;
        $owner = $action->execute([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_dni' => '12345678Z',
            'coprop1_email' => 'miren@example.com',
        ]);

        $user = User::find($owner->user_id);

        expect($user)->not->toBeNull()
            ->and($user->name)->toBe('12345678Z')
            ->and($user->email)->toBe('miren@example.com')
            ->and($user->is_active)->toBeTrue();
    });

    it('sends the configured owner welcome email to the new user', function () {
        Mail::fake();

        createSetting('owners_welcome_subject_eu', 'Ongi etorri Madaia 33ra');
        createSetting('owners_welcome_text_eu', '<p>Kaixo</p>##info##');

        $portal = Location::factory()->portal()->create(['code' => '33-A']);
        $property = Property::factory()->create([
            'location_id' => $portal->id,
            'name' => '1A',
        ]);

        $action = new CreateOwnerAction;
        $action->execute([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_dni' => '12345678Z',
            'coprop1_email' => 'miren@example.com',
            'assignments' => [
                [
                    'property_id' => $property->id,
                    'start_date' => '2026-01-01',
                    'end_date' => null,
                ],
            ],
        ]);

        Mail::assertSent(OwnerWelcomeMail::class, function (OwnerWelcomeMail $mail): bool {
            return $mail->hasTo('miren@example.com')
                && $mail->subjectLine === 'Ongi etorri Madaia 33ra'
                && str_contains($mail->bodyHtml, '33-A 1A');
        });
    });

    it('creates owner with optional second co-owner data', function () {
        Mail::fake();

        $action = new CreateOwnerAction;
        $owner = $action->execute([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_dni' => '12345678Z',
            'coprop1_email' => 'miren@example.com',
            'coprop2_name' => 'Jon Etxeberria',
            'coprop2_dni' => '87654321X',
            'coprop2_email' => 'jon@example.com',
        ]);

        expect($owner->coprop2_name)->toBe('Jon Etxeberria')
            ->and($owner->coprop2_dni)->toBe('87654321X');
    });

    it('creates initial assignments when provided', function () {
        Mail::fake();

        $portal = Location::factory()->portal()->create();
        $storage = Location::factory()->storage()->create();

        $activeProperty = Property::factory()->create([
            'location_id' => $portal->id,
            'name' => '1A',
        ]);

        $closedProperty = Property::factory()->forStorage()->create([
            'location_id' => $storage->id,
            'name' => '12',
        ]);

        $action = new CreateOwnerAction;
        $owner = $action->execute([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_dni' => '12345678Z',
            'coprop1_email' => 'miren@example.com',
            'assignments' => [
                [
                    'property_id' => $activeProperty->id,
                    'start_date' => '2026-01-01',
                    'end_date' => null,
                ],
                [
                    'property_id' => $closedProperty->id,
                    'start_date' => '2025-01-01',
                    'end_date' => '2025-12-31',
                ],
            ],
        ]);

        expect($owner->assignments()->count())->toBe(2);

        $activeAssignment = $owner->assignments()->where('property_id', $activeProperty->id)->first();
        $closedAssignment = $owner->assignments()->where('property_id', $closedProperty->id)->first();

        expect($activeAssignment)->not->toBeNull()
            ->and($activeAssignment->end_date)->toBeNull()
            ->and($closedAssignment)->not->toBeNull()
            ->and($closedAssignment->end_date?->format('Y-m-d'))->toBe('2025-12-31');
    });

    it('fails when creating an active assignment for a property that is already active', function () {
        Mail::fake();

        $property = Property::factory()->create();
        $existingOwner = Owner::factory()->create();

        PropertyAssignment::factory()->create([
            'property_id' => $property->id,
            'owner_id' => $existingOwner->id,
            'end_date' => null,
        ]);

        $action = new CreateOwnerAction;

        expect(fn() => $action->execute([
            'coprop1_name' => 'Miren Etxeberria',
            'coprop1_dni' => '12345678Z',
            'coprop1_email' => 'miren@example.com',
            'assignments' => [
                [
                    'property_id' => $property->id,
                    'start_date' => '2026-01-01',
                    'end_date' => null,
                ],
            ],
        ]))->toThrow(ValidationException::class);
    });
});

describe('DeactivateOwnerAction', function () {
    it('sets is_active to false on the linked user', function () {
        $owner = Owner::factory()->create();

        $action = new DeactivateOwnerAction;
        $action->execute($owner);

        expect(User::find($owner->user_id)->is_active)->toBeFalse();
    });

    it('does not delete the owner record', function () {
        $owner = Owner::factory()->create();

        $action = new DeactivateOwnerAction;
        $action->execute($owner);

        expect(Owner::find($owner->id))->not->toBeNull();
    });
});
