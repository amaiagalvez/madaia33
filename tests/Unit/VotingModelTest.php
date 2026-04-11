<?php

use App\Models\Voting;
use App\SupportedLocales;
use Illuminate\Support\Facades\App;

describe('Voting::isOpen()', function () {
  it('returns true when today falls within the voting period', function () {
    $voting = new Voting([
      'starts_at' => today()->subDay()->toDateString(),
      'ends_at' => today()->addDay()->toDateString(),
    ]);

    expect($voting->isOpen())->toBeTrue();
  });

  it('returns true on the start day', function () {
    $voting = new Voting([
      'starts_at' => today()->toDateString(),
      'ends_at' => today()->addDay()->toDateString(),
    ]);

    expect($voting->isOpen())->toBeTrue();
  });

  it('returns true on the end day', function () {
    $voting = new Voting([
      'starts_at' => today()->subDay()->toDateString(),
      'ends_at' => today()->toDateString(),
    ]);

    expect($voting->isOpen())->toBeTrue();
  });

  it('returns false when the voting period has not started yet', function () {
    $voting = new Voting([
      'starts_at' => today()->addDay()->toDateString(),
      'ends_at' => today()->addDays(5)->toDateString(),
    ]);

    expect($voting->isOpen())->toBeFalse();
  });

  it('returns false when the voting period has already ended', function () {
    $voting = new Voting([
      'starts_at' => today()->subDays(5)->toDateString(),
      'ends_at' => today()->subDay()->toDateString(),
    ]);

    expect($voting->isOpen())->toBeFalse();
  });

  it('returns false when starts_at is null', function () {
    $voting = new Voting([
      'starts_at' => null,
      'ends_at' => today()->addDay()->toDateString(),
    ]);

    expect($voting->isOpen())->toBeFalse();
  });

  it('returns false when ends_at is null', function () {
    $voting = new Voting([
      'starts_at' => today()->subDay()->toDateString(),
      'ends_at' => null,
    ]);

    expect($voting->isOpen())->toBeFalse();
  });
});

describe('Voting bilingual accessors', function () {
  it('returns name in the active locale', function () {
    $voting = new Voting([
      'name_eu' => 'Boto-ematea EU',
      'name_es' => 'Votación ES',
    ]);

    App::setLocale(SupportedLocales::BASQUE);
    expect($voting->name)->toBe('Boto-ematea EU');

    App::setLocale(SupportedLocales::SPANISH);
    expect($voting->name)->toBe('Votación ES');
  });

  it('falls back for name when active locale is empty', function () {
    $voting = new Voting([
      'name_eu' => 'Boto-ematea EU',
      'name_es' => null,
    ]);

    App::setLocale(SupportedLocales::SPANISH);
    expect($voting->name)->toBe('Boto-ematea EU');
  });

  it('returns question in the active locale', function () {
    $voting = new Voting([
      'question_eu' => 'Galdera EU',
      'question_es' => 'Pregunta ES',
    ]);

    App::setLocale(SupportedLocales::BASQUE);
    expect($voting->question)->toBe('Galdera EU');

    App::setLocale(SupportedLocales::SPANISH);
    expect($voting->question)->toBe('Pregunta ES');
  });

  it('falls back for question when active locale is empty', function () {
    $voting = new Voting([
      'question_eu' => 'Galdera EU',
      'question_es' => '',
    ]);

    App::setLocale(SupportedLocales::SPANISH);
    expect($voting->question)->toBe('Galdera EU');
  });
});
