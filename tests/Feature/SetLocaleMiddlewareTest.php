<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

it('uses eu as default locale when session locale is missing', function () {
  Route::middleware(['web', SetLocale::class])->get('/__test-set-locale-default', function () {
    return response()->json(['locale' => app()->getLocale()]);
  });

  $this->get('/__test-set-locale-default')
    ->assertSuccessful()
    ->assertJsonPath('locale', 'eu');
});

it('uses the session locale when it is valid', function () {
  Route::middleware(['web', SetLocale::class])->get('/__test-set-locale-valid', function () {
    return response()->json(['locale' => app()->getLocale()]);
  });

  $this->withSession(['locale' => 'es'])
    ->get('/__test-set-locale-valid')
    ->assertSuccessful()
    ->assertJsonPath('locale', 'es');
});

it('falls back to eu when the session locale is invalid', function () {
  Route::middleware(['web', SetLocale::class])->get('/__test-set-locale-invalid', function () {
    return response()->json(['locale' => app()->getLocale()]);
  });

  $this->withSession(['locale' => 'fr'])
    ->get('/__test-set-locale-invalid')
    ->assertSuccessful()
    ->assertJsonPath('locale', 'eu');
});
