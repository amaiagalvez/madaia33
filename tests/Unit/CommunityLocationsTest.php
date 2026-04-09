<?php

use App\CommunityLocations;
use InvalidArgumentException;

it('resolves shared community location types from a single source of truth', function () {
    expect(CommunityLocations::PORTALS)->toContain('33-A', '33-J')
        ->and(CommunityLocations::GARAGES)->toContain('P-1', 'P-3')
        ->and(CommunityLocations::STORAGES)->toContain('A', 'J')
        ->and(CommunityLocations::typeForCode('33-A'))->toBe('portal')
        ->and(CommunityLocations::typeForCode('P-1'))->toBe('garage')
        ->and(CommunityLocations::typeForCode('A'))->toBe('storage');
});

it('builds labeled location options from the shared constants', function () {
    $options = CommunityLocations::options('Portal', 'Garaje', 'Trastero');

    $expectedCount = count(CommunityLocations::PORTALS)
        + count(CommunityLocations::GARAGES)
        + count(CommunityLocations::STORAGES);

    expect($options)->toHaveCount($expectedCount)
        ->and($options[0])->toBe(['code' => '33-A', 'type' => 'portal', 'label' => 'Portal 33-A'])
        ->and($options[array_key_last($options)])->toBe(['code' => 'J', 'type' => 'storage', 'label' => 'Trastero J']);
});

it('throws for unknown location codes', function () {
    expect(fn() => CommunityLocations::typeForCode('UNKNOWN-CODE'))
        ->toThrow(InvalidArgumentException::class);
});
