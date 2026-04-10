<?php

use App\Models\User;

test('legacy dashboard route is not available for guests', function () {
    test()->get('/dashboard')->assertNotFound();
});

test('legacy dashboard route is not available for authenticated users', function () {
    $user = User::factory()->create();
    test()->actingAs($user);

    test()->get('/dashboard')->assertNotFound();
});
