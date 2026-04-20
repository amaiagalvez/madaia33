<?php

use App\Models\Owner;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Illuminate\Support\Collection;
use App\Services\Messaging\MessageVariableResolver;

it('replaces supported variables with owner data', function () {
    $owner = new Owner([
        'coprop1_name' => 'Ane',
        'coprop1_surname' => 'Etxeberria',
        'coprop2_name' => 'Miren',
    ]);

    $location = new Location(['name' => 'P-1']);
    $property = new Property(['name' => '1A']);
    $property->setRelation('location', $location);

    $assignment = new PropertyAssignment;
    $assignment->setRelation('property', $property);

    $owner->setRelation('activeAssignments', new Collection([$assignment]));

    $resolver = new MessageVariableResolver;

    $result = $resolver->resolve('Kaixo **nombre**: **propiedad** (**portal**)', $owner, 'coprop1');

    expect($result)->toBe('Kaixo Ane Etxeberria: 1A (P-1)');
});

it('handles multiple active properties and unique portal codes', function () {
    $owner = new Owner([
        'coprop1_name' => 'Ane',
        'coprop2_name' => 'Miren',
        'coprop2_surname' => 'Goikoetxea',
    ]);

    $locationOne = new Location(['name' => 'P-1']);
    $locationTwo = new Location(['name' => 'P-2']);

    $propertyOne = new Property(['name' => '1A']);
    $propertyOne->setRelation('location', $locationOne);

    $propertyTwo = new Property(['name' => '2B']);
    $propertyTwo->setRelation('location', $locationTwo);

    $propertyThree = new Property(['name' => '3C']);
    $propertyThree->setRelation('location', $locationTwo);

    $assignmentOne = new PropertyAssignment;
    $assignmentOne->setRelation('property', $propertyOne);

    $assignmentTwo = new PropertyAssignment;
    $assignmentTwo->setRelation('property', $propertyTwo);

    $assignmentThree = new PropertyAssignment;
    $assignmentThree->setRelation('property', $propertyThree);

    $owner->setRelation('activeAssignments', new Collection([
        $assignmentOne,
        $assignmentTwo,
        $assignmentThree,
    ]));

    $resolver = new MessageVariableResolver;

    $result = $resolver->resolve('**nombre** | **propiedad** | **portal**', $owner, 'coprop2');

    expect($result)->toBe('Miren Goikoetxea | 1A, 2B, 3C | P-1, P-2');
});

it('replaces unknown markers with empty string', function () {
    $owner = new Owner([
        'coprop1_name' => 'Ane',
        'coprop1_surname' => 'Agirre',
    ]);

    $owner->setRelation('activeAssignments', new Collection);

    $resolver = new MessageVariableResolver;

    $result = $resolver->resolve('Hola **nombre** **desconocida**', $owner, 'coprop1');

    expect($result)->toBe('Hola Ane Agirre ');
});
