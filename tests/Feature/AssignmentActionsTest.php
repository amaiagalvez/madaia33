<?php

use App\Models\Owner;
use App\Models\Property;
use App\Mail\OwnerWelcomeMail;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Actions\Properties\AssignPropertyAction;
use App\Actions\Properties\UnassignPropertyAction;

describe('AssignPropertyAction', function () {
    beforeEach(fn () => Mail::fake());

    it('creates an active assignment for an unassigned property and activates owner user', function () {
        $property = Property::factory()->create();
        $owner = Owner::factory()->create();
        $owner->user()->update(['is_active' => false]);

        $action = app(AssignPropertyAction::class);
        $assignment = $action->execute($property, $owner, '2026-01-01');

        expect($assignment)->toBeInstanceOf(PropertyAssignment::class)
            ->and($assignment->property_id)->toBe($property->id)
            ->and($assignment->owner_id)->toBe($owner->id)
            ->and($assignment->end_date)->toBeNull()
            ->and($assignment->admin_validated)->toBeFalse()
            ->and($assignment->owner_validated)->toBeFalse()
            ->and($owner->user->fresh()->is_active)->toBeTrue();
    });

    it('throws a validation exception when property already has an active assignment', function () {
        $property = Property::factory()->create();
        $owner1 = Owner::factory()->create();
        $owner2 = Owner::factory()->create();

        PropertyAssignment::factory()->create([
            'property_id' => $property->id,
            'owner_id' => $owner1->id,
            'end_date' => null,
        ]);

        $action = app(AssignPropertyAction::class);

        expect(fn () => $action->execute($property, $owner2, '2026-06-01'))
            ->toThrow(ValidationException::class);
    });

    it('allows assigning a property whose previous assignment is closed', function () {
        $property = Property::factory()->create();
        $owner1 = Owner::factory()->create();
        $owner2 = Owner::factory()->create();

        PropertyAssignment::factory()->closed()->create([
            'property_id' => $property->id,
            'owner_id' => $owner1->id,
        ]);

        $action = app(AssignPropertyAction::class);
        $assignment = $action->execute($property, $owner2, '2026-06-01');

        expect($assignment->owner_id)->toBe($owner2->id)
            ->and($assignment->end_date)->toBeNull();
    });

    it('sends the welcome email using the owner language when creating a new active assignment', function () {
        createSetting('owners_welcome_subject_eu', 'Ongi etorri');
        createSetting('owners_welcome_subject_es', 'Bienvenida');
        createSetting('owners_welcome_text_eu', '<p>Kaixo ##izena##</p>##info##');
        createSetting('owners_welcome_text_es', '<p>Hola ##izena##</p>##info##');

        $property = Property::factory()->create();
        $owner = Owner::factory()->create([
            'language' => 'es',
            'coprop1_email' => 'owner@example.com',
        ]);

        $action = app(AssignPropertyAction::class);
        $action->execute($property, $owner, '2026-06-01');

        Mail::assertSent(OwnerWelcomeMail::class, function (OwnerWelcomeMail $mail): bool {
            return $mail->hasTo('owner@example.com')
                && $mail->subjectLine === 'Bienvenida'
                && str_contains($mail->bodyHtml, 'Hola');
        });
    });
});

describe('UnassignPropertyAction', function () {
    it('sets end_date on an active assignment and deactivates user when no active assignments remain', function () {
        $assignment = PropertyAssignment::factory()->create([
            'start_date' => '2026-03-01',
            'end_date' => null,
        ]);
        $assignment->owner->user()->update(['is_active' => true]);

        $action = new UnassignPropertyAction;
        $result = $action->execute($assignment, '2026-03-31');

        expect($result->end_date->format('Y-m-d'))->toBe('2026-03-31')
            ->and($assignment->owner->user->fresh()->is_active)->toBeFalse();
    });

    it('keeps user active when owner still has at least one active assignment', function () {
        $owner = Owner::factory()->create();
        $owner->user()->update(['is_active' => true]);

        $assignmentToClose = PropertyAssignment::factory()->create([
            'owner_id' => $owner->id,
            'start_date' => '2026-03-01',
            'end_date' => null,
        ]);

        PropertyAssignment::factory()->create([
            'owner_id' => $owner->id,
            'start_date' => '2026-03-01',
            'end_date' => null,
        ]);

        $action = new UnassignPropertyAction;
        $action->execute($assignmentToClose, '2026-03-31');

        expect($owner->user->fresh()->is_active)->toBeTrue();
    });

    it('throws a validation exception when assignment is already closed', function () {
        $assignment = PropertyAssignment::factory()->closed()->create();

        $action = new UnassignPropertyAction;

        expect(fn () => $action->execute($assignment, '2026-06-01'))
            ->toThrow(ValidationException::class);
    });

    it('throws a validation exception when end date is before start date', function () {
        $assignment = PropertyAssignment::factory()->create(['start_date' => '2026-03-10', 'end_date' => null]);

        $action = new UnassignPropertyAction;

        expect(fn () => $action->execute($assignment, '2026-03-09'))
            ->toThrow(ValidationException::class);
    });
});
