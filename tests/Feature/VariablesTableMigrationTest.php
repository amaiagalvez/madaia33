<?php

use Illuminate\Support\Facades\Schema;

test('variables table has the required columns', function () {
  expect(Schema::hasTable('variables'))->toBeTrue();
  expect(Schema::hasColumns('variables', ['name', 'value']))->toBeTrue();
});
