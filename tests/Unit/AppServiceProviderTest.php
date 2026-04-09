<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use App\Providers\AppServiceProvider;
use Illuminate\Validation\Rules\Password;

it('configures immutable dates and non-production password defaults on boot', function () {
    app()->detectEnvironment(fn (): string => 'testing');

    Password::$defaultCallback = null;

    $provider = new AppServiceProvider(app());
    $provider->boot();

    expect(Date::now())->toBeInstanceOf(CarbonImmutable::class);

    $passwordRule = Password::default();

    expect($passwordRule->appliedRules())->toMatchArray([
        'min' => 8,
        'mixedCase' => false,
        'letters' => false,
        'numbers' => false,
        'symbols' => false,
        'uncompromised' => false,
    ]);
});

it('configures strict password defaults in production environment', function () {
    app()->detectEnvironment(fn (): string => 'production');

    Password::$defaultCallback = null;

    $provider = new AppServiceProvider(app());
    $provider->boot();

    $passwordRule = Password::default();

    expect($passwordRule->appliedRules())->toMatchArray([
        'min' => 12,
        'mixedCase' => true,
        'letters' => true,
        'numbers' => true,
        'symbols' => true,
        'uncompromised' => true,
    ]);

    app()->detectEnvironment(fn (): string => 'testing');
});
