<?php

use App\Models\User;
use App\Models\PropertyAssignment;

describe('User::initials()', function () {
  it('returns the first letter of each word in the name', function () {
    $user = new User(['name' => 'Miren Etxeberria']);

    expect($user->initials())->toBe('ME');
  });

  it('returns only the first two words when name has more', function () {
    $user = new User(['name' => 'Miren Arrate Etxeberria']);

    expect($user->initials())->toBe('MA');
  });

  it('returns a single initial when name has one word', function () {
    $user = new User(['name' => 'Miren']);

    expect($user->initials())->toBe('M');
  });

  it('handles a DNI-style name (no spaces)', function () {
    $user = new User(['name' => '12345678Z']);

    expect($user->initials())->toBe('1');
  });
});

describe('PropertyAssignment::isActive()', function () {
  it('returns true when end_date is null', function () {
    $assignment = new PropertyAssignment(['end_date' => null]);

    expect($assignment->isActive())->toBeTrue();
  });

  it('returns false when end_date is set', function () {
    $assignment = new PropertyAssignment(['end_date' => '2026-03-31']);

    expect($assignment->isActive())->toBeFalse();
  });
});
