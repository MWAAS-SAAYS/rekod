<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

final class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
            'token'   => (string) $request->route('token'),
            'email'   => $request->query('email') !== null ? (string) $request->query('email') : null,
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Input Sanitization: Normalize email string prior to processing
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'string', 'email:rfc,dns'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Execute Password Reset via Broker
        $status = Password::reset(
            [
                'token'                 => $validated['token'],
                'email'                 => $validated['email'],
                'password'              => $validated['password'],
                'password_confirmation' => (string) $request->input('password_confirmation'),
            ],
            function (User $user) use ($validated): void {
                $user->forceFill([
                    'password'       => Hash::make($validated['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // 3. Strict Identity Comparison (===) against status constant
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}