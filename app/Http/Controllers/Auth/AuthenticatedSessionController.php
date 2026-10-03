<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AuthenticatedSessionController extends Controller
{
    /**
     * Whitelisted portal types to prevent session parameter manipulation.
     *
     * @var array<int, string>
     */
    private const ALLOWED_PORTALS = [
        User::ROLE_STUDENT,
        User::ROLE_LECTURER,
        User::ROLE_INDUSTRIAL_SUPERVISOR,
        User::ROLE_COMPANY,
        User::ROLE_ADMIN,
    ];

    /**
     * Display the login view.
     */
    public function create(?string $portal = null): View
    {
        // Parameter Whitelisting: Guard against invalid portal parameters
        if ($portal !== null && in_array($portal, self::ALLOWED_PORTALS, true)) {
            session(['portal' => $portal]);
        } else {
            /** @var string|null $portal */
            $portal = session('portal');
        }

        return view('auth.login', ['portal' => $portal]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Rate-limiting & credential verification
        $request->authenticate();

        // 2. Session Fixation Protection: Generate fresh session token
        $request->session()->regenerate();

        /** @var User|null $user */
        $user = Auth::user();

        // 3. Dynamic role-based dashboard resolution
        $userRole = $user?->role ?? $user?->portal;

        $redirectRoute = match ($userRole) {
            User::ROLE_STUDENT => 'student.portal',
            User::ROLE_LECTURER => 'lecturer.portal',
            User::ROLE_INDUSTRIAL_SUPERVISOR => 'industry.portal',
            User::ROLE_COMPANY => 'company.portal',
            User::ROLE_ADMIN => 'admin.portal',
            default => 'welcome',
        };

        // 4. Intended Redirection with route fallback
        return redirect()->intended(route($redirectRoute));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}