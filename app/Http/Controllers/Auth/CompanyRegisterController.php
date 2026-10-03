<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCompanyRequest;
use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;

final class CompanyRegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.company-register');
    }

    public function store(RegisterCompanyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated): void {
                $user = User::create([
                    'name'     => $validated['contact_person_name'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role'     => 'industry',
                ]);

                CompanyProfile::create([
                    'user_id'             => $user->id,
                    'company_name'        => $validated['company_name'],
                    'registration_number' => $validated['registration_number'],
                    'industry_sector'     => $validated['industry_sector'],
                    'phone_number'        => $validated['phone_number'],
                    'address'             => $validated['address'],
                    'placement_capacity'  => $validated['placement_capacity'],
                    'status'              => 'pending',
                ]);

                Auth::login($user);
            });
        } catch (Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Registration failed. Please try again.']);
        }

        return redirect()->route('company.verification-pending');
    }
}