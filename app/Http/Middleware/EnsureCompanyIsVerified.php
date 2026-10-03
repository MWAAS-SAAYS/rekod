<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCompanyIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED)
                : redirect()->route('login');
        }

        // Allow non-industry accounts (students, lecturers, admins) to proceed without verification
        if (! $user->isIndustry()) {
            return $next($request);
        }

        $companyProfile = $user->companyProfile;

        if (! $companyProfile || $companyProfile->status !== 'verified') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your corporate identity verification is currently pending admin review.',
                    'status'  => $companyProfile?->status ?? 'uninitialized',
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()->route('company.verification-pending');
        }

        return $next($request);
    }
}