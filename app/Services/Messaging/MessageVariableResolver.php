<?php

namespace App\Services\Messaging;

use App\Models\Owner;

class MessageVariableResolver
{
    public function resolve(string $text, Owner $owner, string $slot): string
    {
        $owner->loadMissing('activeAssignments.property.location');

        $name = $slot === 'coprop2'
            ? (string) ($owner->coprop2_name ?? '')
            : (string) ($owner->coprop1_name ?? '');

        $properties = $owner->activeAssignments
            ->map(fn ($assignment) => (string) ($assignment->property->name ?? ''))
            ->filter()
            ->values()
            ->implode(', ');

        $portals = $owner->activeAssignments
            ->map(fn ($assignment) => (string) ($assignment->property->location->code ?? ''))
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
