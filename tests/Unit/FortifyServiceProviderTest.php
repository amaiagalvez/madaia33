<?php

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use App\Providers\FortifyServiceProvider;
use Illuminate\Support\Facades\RateLimiter;

it('configura el rate limiter de two-factor con la clave de sesión login.id', function () {
    (new FortifyServiceProvider(app()))->boot();

    $limiter = RateLimiter::limiter('two-factor');

    expect($limiter)->toBeCallable();

    $request = Request::create('/two-factor-challenge', 'POST');
    $request->setLaravelSession(app('session')->driver());
    $request->session()->put('login.id', 'login-session-id');

    $limit = $limiter($request);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->maxAttempts)->toBe(5)
        ->and($limit->key)->toBe('login-session-id');
});
