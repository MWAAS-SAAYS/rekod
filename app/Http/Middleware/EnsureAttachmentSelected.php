<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\AttachmentSelectedController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAttachmentSelected
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $session = $request->session();

        if ($user?->role && $session->has('attachment_id') && $session->has('attachment_name')) {
            $hasRoleContext = match ($user->role) {
                'student'  => $session->has('attachment_student_id'),
                'lecturer' => $session->has('attachment_lecturer_id'),
                default    => true,
            };

            if ($hasRoleContext) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Please select an active attachment before proceeding.',
            ], 403);
        }

        $result = app(AttachmentSelectedController::class)->index($request);

        return $result instanceof Response ? $result : response($result);
    }
}