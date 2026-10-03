<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

final class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Input Sanitization: Pre-normalize email address prior to validation
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc,dns'],
        ]);

        // 2. Dispatch Password Reset Link via Password Broker
        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        // 3. Strict Status Identity Verification
        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}