<?php

use Illuminate\Support\Facades\Validator;

it('accepts and rejects construction inquiry payloads', function () {
    $valid = fake()->boolean();

    $data = [
        'name' => $valid ? fake()->name() : '',
        'email' => $valid ? fake()->safeEmail() : 'email-okerra',
        'subject' => $valid ? fake()->sentence(4) : '',
        'message' => $valid ? fake()->paragraph() : '',
    ];

    $validator = Validator::make($data, [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|max:5000',
    ]);

    expect($validator->passes())->toBe($valid);
})->repeat(2);
