<?php

use App\CommunityLocations;

it('resolves shared community location types from a single source of truth', function () {
    expect(CommunityLocations::PORTALS)->toContain('33-A', '33-J')
        ->and(CommunityLocations::GARAGES)->toContain('P-1', 'P-3')
        ->and(CommunityLocations::typeForCode('33-A'))->toBe('portal')
        ->and(CommunityLocations::typeForCode('P-1'))->toBe('garage');
});

it('builds labeled location options from the shared constants', function () {
    $options = CommunityLocations::options('Portal', 'Garaje');

    expect($options)->toHaveCount(count(CommunityLocations::PORTALS) + count(CommunityLocations::GARAGES))
        ->and($options[0])->toBe(['code' => '33-A', 'type' => 'portal', 'label' => 'Portal 33-A'])
        ->and($options[array_key_last($options)])->toBe(['code' => 'P-3', 'type' => 'garage', 'label' => 'Garaje P-3']);
});
