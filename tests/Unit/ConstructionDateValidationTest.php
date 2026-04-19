<?php

use Illuminate\Support\Facades\Validator;

it('validates construction date pairs', function () {
  $start = fake()->dateTimeBetween('-10 days', '+10 days');
  $isInvalid = fake()->boolean();
  $end = $isInvalid
    ? (clone $start)->modify('-1 day')
    : (clone $start)->modify('+1 day');

  $validator = Validator::make([
    'startsAt' => $start->format('Y-m-d'),
    'endsAt' => $end->format('Y-m-d'),
  ], [
    'startsAt' => ['required', 'date'],
    'endsAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
  ]);

  expect($validator->fails())->toBe($isInvalid);
})->repeat(2);
