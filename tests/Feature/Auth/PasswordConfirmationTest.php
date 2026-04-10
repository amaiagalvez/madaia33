<?php

use App\Models\User;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $response = test()->actingAs($user)->get(route('password.confirm'));

    $response->assertOk()
        ->assertSee('data-auth-shell', false)
        ->assertSee(__('Confirm password'));
});
