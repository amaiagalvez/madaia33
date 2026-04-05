<?php

use App\Rules\NoScriptTags;
use Illuminate\Support\Facades\Validator;

it('rejects script tags in text input', function (string $value) {
    $validator = Validator::make(
        ['content' => $value],
        ['content' => ['required', new NoScriptTags]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('content');
})->with([
    '<script>alert("xss")</script>',
    '<ScRiPt src="https://evil.test/x.js"></sCrIpT>',
]);

it('allows safe plain text content', function (string $value) {
    $validator = Validator::make(
        ['content' => $value],
        ['content' => ['required', new NoScriptTags]],
    );

    expect($validator->fails())->toBeFalse();
})->with([
    'Consulta normal de la comunidad',
    "' OR 1=1 --",
    '<b>texto en negrita permitido por esta regla</b>',
]);
