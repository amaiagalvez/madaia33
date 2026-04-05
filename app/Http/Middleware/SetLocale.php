<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', 'eu');

        if (! in_array($locale, ['eu', 'es'])) {
            $locale = 'eu';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
