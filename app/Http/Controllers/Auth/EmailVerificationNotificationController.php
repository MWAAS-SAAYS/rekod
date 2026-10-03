<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user?->hasVerifiedEmail()) {
            $userRole = $user->role ?? $user->portal;

            $defaultRoute = match ($userRole) {
                User::ROLE_STUDENT => 'student.portal',
                User::ROLE_LECTURER => 'lecturer.portal',
                User::ROLE_INDUSTRIAL_SUPERVISOR => 'industry.portal',
                User::ROLE_COMPANY => 'company.portal',
                User::ROLE_ADMIN => 'admin.portal',
                default => 'welcome',
            };

            return redirect()->intended(route($defaultRoute));
        }

        $user?->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}