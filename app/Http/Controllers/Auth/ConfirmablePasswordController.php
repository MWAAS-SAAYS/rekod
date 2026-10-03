<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if ($user === null || ! Auth::guard('web')->validate([
            'email' => $user->email,
            'password' => (string) $request->input('password'),
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

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
}