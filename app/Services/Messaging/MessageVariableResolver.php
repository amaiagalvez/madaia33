<?php

namespace App\Services\Messaging;

use App\Models\Owner;

class MessageVariableResolver
{
    public function resolve(string $text, Owner $owner, string $slot): string
    {
        $owner->loadMissing('activeAssignments.property.location');

        $name = $slot === 'coprop2'
            ? ($owner->fullName2 !== '' ? $owner->fullName2 : $owner->fullName1)
            : $owner->fullName1;

        $properties = $owner->activeAssignments
            ->map(fn ($assignment) => (string) ($assignment->property->name ?? ''))
            ->filter()
            ->values()
            ->implode(', ');

        $portals = $owner->activeAssignments
            ->map(fn ($assignment) => (string) ($assignment->property->location->name ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        $resolvedText = str_replace(
            ['**nombre**', '**propiedad**', '**portal**'],
            [$name, $properties, $portals],
            $text,
        );

        return (string) preg_replace('/\*\*[^*]+\*\*/', '', $resolvedText);
    }
}
