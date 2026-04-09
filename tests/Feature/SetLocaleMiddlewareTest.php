<?php

use App\SupportedLocales;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

it('uses eu as default locale when session locale is missing', function () {
    Route::middleware(['web', SetLocale::class])->get('/__test-set-locale-default', function () {
        return response()->json(['locale' => app()->getLocale()]);
    });

    test()->get('/__test-set-locale-default')
        ->assertSuccessful()
        ->assertJsonPath('locale', SupportedLocales::default());
});

it('uses the session locale when it is valid', function () {
    Route::middleware(['web', SetLocale::class])->get('/__test-set-locale-valid', function () {
        return response()->json(['locale' => app()->getLocale()]);
    });

    test()->withSession(['locale' => SupportedLocales::SPANISH])
        ->get('/__test-set-locale-valid')
        ->assertSuccessful()
        ->assertJsonPath('locale', SupportedLocales::SPANISH);
});

it('falls back to eu when the session locale is invalid', function () {
    Route::middleware(['web', SetLocale::class])->get('/__test-set-locale-invalid', function () {
        return response()->json(['locale' => app()->getLocale()]);
    });

    test()->withSession(['locale' => 'fr'])
        ->get('/__test-set-locale-invalid')
        ->assertSuccessful()
        ->assertJsonPath('locale', SupportedLocales::default());
});
