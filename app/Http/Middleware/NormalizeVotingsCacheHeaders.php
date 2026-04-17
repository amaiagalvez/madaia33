<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\PublicVotingController;

class NormalizeVotingsCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->route()?->getActionName() !== PublicVotingController::class . '@index') {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-cache, private, must-revalidate');

        return $response;
    }
}
