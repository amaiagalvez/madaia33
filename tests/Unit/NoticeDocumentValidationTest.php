<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

it('accepts allowed notice document mimes and rejects others', function () {
  $allowed = fake()->boolean();
  $mime = $allowed
    ? fake()->randomElement(['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/jpeg', 'image/png'])
    : fake()->randomElement(['application/x-msdownload', 'text/plain', 'application/zip']);

  $file = UploadedFile::fake()->create('sample', 128, $mime);

  $validator = Validator::make([
    'attachment' => $file,
  ], [
    'attachment' => ['required', 'file', 'mimes:pdf,docx,xlsx,jpg,jpeg,png', 'max:20480'],
  ]);

  expect($validator->passes())->toBe($allowed);
})->repeat(2);
