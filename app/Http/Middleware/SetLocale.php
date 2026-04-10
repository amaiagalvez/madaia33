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
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $firstSegment = (string) $request->segment(1);

        if (SupportedLocales::isSupported($firstSegment)) {
            return $firstSegment;
        }

        return SupportedLocales::normalize(
            $request->route('locale')
                ?? $request->session()->get('locale')
                ?? SupportedLocales::default()
        );
    }
}
