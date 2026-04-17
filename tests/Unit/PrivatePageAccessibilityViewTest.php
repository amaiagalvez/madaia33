<?php

use Illuminate\Support\Facades\File;

it('private page template declares password toggle accessibility attributes', function () {
  $html = File::get(resource_path('views/public/private.blade.php'));

  expect($html)
    ->toContain('aria-controls="private-password"')
    ->toContain("x-bind:aria-pressed=\"showPassword ? 'true' : 'false'\"");
});

it('private page template binds email error state and message semantics', function () {
  $html = File::get(resource_path('views/public/private.blade.php'));

  expect($html)
    ->toContain('data-private-login-error')
    ->toContain('id="private-login-error"')
    ->toContain('aria-describedby="private-login-error"')
    ->toContain('aria-invalid="true"');
});
