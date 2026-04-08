<?php

namespace App\Http\Middleware;

use Closure;
use App\SupportedLocales;
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
        $locale = SupportedLocales::normalize(
            $request->session()->get('locale', SupportedLocales::default())
        );

        App::setLocale($locale);

        return $next($request);
    }
}
