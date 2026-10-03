<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PortalMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, string ...$portals): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED)
                : redirect()->route('login');
        }

        if (! empty($portals) && ! in_array($user->role, $portals, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized access to this portal.'], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Unauthorized access to this portal.');
        }

        return $next($request);
    }
}