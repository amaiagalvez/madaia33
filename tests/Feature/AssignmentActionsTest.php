<?php

use App\Actions\AssignPropertyAction;
use App\Models\Owner;
use App\Models\Property;
use App\Models\PropertyAssignment;
use App\Actions\UnassignPropertyAction;
use Illuminate\Validation\ValidationException;

describe('AssignPropertyAction', function () {
    it('creates an active assignment for an unassigned property', function () {
        $property = Property::factory()->create();
        $owner = Owner::factory()->create();

        $action = new AssignPropertyAction;
        $assignment = $action->execute($property, $owner, '2026-01-01');

        expect($assignment)->toBeInstanceOf(PropertyAssignment::class)
            ->and($assignment->property_id)->toBe($property->id)
            ->and($assignment->owner_id)->toBe($owner->id)
            ->and($assignment->end_date)->toBeNull()
            ->and($assignment->admin_validated)->toBeFalse()
            ->and($assignment->owner_validated)->toBeFalse();
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

        $action = new AssignPropertyAction;

        expect(fn() => $action->execute($property, $owner2, '2026-06-01'))
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

        $action = new AssignPropertyAction;
        $assignment = $action->execute($property, $owner2, '2026-06-01');

        expect($assignment->owner_id)->toBe($owner2->id)
            ->and($assignment->end_date)->toBeNull();
    });
});

describe('UnassignPropertyAction', function () {
    it('sets end_date on an active assignment', function () {
        $assignment = PropertyAssignment::factory()->create(['end_date' => null]);

        $action = new UnassignPropertyAction;
        $result = $action->execute($assignment, '2026-03-31');

        expect($result->end_date->format('Y-m-d'))->toBe('2026-03-31');
    });

    it('throws a validation exception when assignment is already closed', function () {
        $assignment = PropertyAssignment::factory()->closed()->create();

        $action = new UnassignPropertyAction;

        expect(fn() => $action->execute($assignment, '2026-06-01'))
            ->toThrow(ValidationException::class);
    });
});
