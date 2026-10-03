<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

final class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        $targetRoute = match ($user?->role) {
            'admin' => 'admin.dashboard',
            'lecturer' => 'lecturer.portal',
            'student' => 'student.portal',
            'company' => 'company.portal',
            default => 'dashboard',
        };

        $redirectUrl = route($targetRoute, absolute: false) . '?verified=1';

        if ($user !== null && $user->hasVerifiedEmail()) {
            return redirect()->intended($redirectUrl);
        }

        if ($user !== null && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($redirectUrl);
    }
}