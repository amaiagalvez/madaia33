<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\SupportedLocales;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPanelAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if (! $user->canAccessAdminPanel()) {
            if ($user->hasAnyRole([Role::DELEGATED_VOTE, Role::PROPERTY_OWNER])) {
                return redirect()->route(SupportedLocales::routeName('home'));
            }

            abort(403);
        }

        return $next($request);
    }
}
