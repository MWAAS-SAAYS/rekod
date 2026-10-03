<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
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

        return view('auth.verify-email');
    }
}