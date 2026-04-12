<?php

use Illuminate\Support\Facades\Schema;

test('settings table has the required columns', function () {
    expect(Schema::hasTable('settings'))->toBeTrue();
    expect(Schema::hasColumns('settings', ['key', 'value']))->toBeTrue();
});
