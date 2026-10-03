<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

final class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $units = DB::table('administrative_units')
            ->select(['id', 'name'])
            ->orderBy('name', 'asc')
            ->get();

        $counties = DB::table('locations')
            ->where('level', 1)
            ->select(['id', 'name'])
            ->orderBy('name', 'asc')
            ->get();

        $towns = DB::table('locations')
            ->where('level', 3)
            ->select(['id', 'name'])
            ->orderBy('name', 'asc')
            ->get();

        return view('auth.register', [
            'departments' => $units,
            'programs'    => $units,
            'counties'    => $counties,
            'towns'       => $towns,
        ]);
    }

    /**
     * Endpoint to dynamically fetch towns based on selected county ID.
     */
    public function getTowns(int $countyId): JsonResponse
    {
        $towns = DB::table('locations')
            ->where('level', 3)
            ->where('parent_id', $countyId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return response()->json($towns);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Pre-Validation Input Normalization
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'company_email' => $request->filled('company_email') 
                ? strtolower(trim((string) $request->input('company_email'))) 
                : null,
            'staff_number' => $request->filled('staff_number') 
                ? strtoupper(trim((string) $request->input('staff_number'))) 
                : null,
            'reg_no' => $request->filled('reg_no') 
                ? strtoupper(trim((string) $request->input('reg_no'))) 
                : null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:student,lecturer,company'],

            // Lecturer Specific Validation
            'staff_number' => ['required_if:role,lecturer', 'nullable', 'string', 'max:50', 'unique:lecturers,staff_number'],
            'job_grade' => ['required_if:role,lecturer', 'nullable', 'string', 'max:50'],
            'office_phone' => ['required_if:role,lecturer', 'nullable', 'string', 'max:20'],
            'department_id' => ['required_if:role,lecturer', 'nullable', 'integer', 'exists:administrative_units,id'],

            // Student Specific Validation
            'reg_no' => ['required_if:role,student', 'nullable', 'string', 'max:50', 'unique:students,reg_no'],
            'year_of_study' => ['required_if:role,student', 'nullable', 'string', 'max:20'],
            'program_id' => ['required_if:role,student', 'nullable', 'integer', 'exists:administrative_units,id'],
            'phone_number' => ['required_if:role,student', 'nullable', 'string', 'max:20'],

            // Company Specific Validation
            'company_name' => ['required_if:role,company', 'nullable', 'string', 'max:255', 'unique:companies,name'],
            'alias' => ['required_if:role,company', 'nullable', 'string', 'max:100', 'unique:companies,alias'],
            'contact' => ['required_if:role,company', 'nullable', 'string', 'max:20'],
            'company_email' => ['required_if:role,company', 'nullable', 'email:rfc,dns', 'max:255'],
            'address' => ['required_if:role,company', 'nullable', 'string', 'max:255'],
            'county_id' => ['required_if:role,company', 'nullable', 'integer', 'exists:locations,id'],
            'town_id' => ['required_if:role,company', 'nullable', 'integer', 'exists:locations,id'],
            'street' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
        ]);

        // 2. Transactional Execution for Multi-Table User Creation
        /** @var User $user */
        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => trim((string) $validated['name']),
                'email' => $validated['email'],
                'password' => Hash::make((string) $validated['password']),
                'role' => $validated['role'],
            ]);

            match ($validated['role']) {
                'lecturer' => Lecturer::create([
                    'user_id' => $user->id,
                    'staff_number' => $validated['staff_number'],
                    'department_id' => $validated['department_id'],
                    'job_grade' => trim((string) $validated['job_grade']),
                    'office_phone' => trim((string) $validated['office_phone']),
                ]),
                'student' => Student::create([
                    'user_id' => $user->id,
                    'reg_no' => $validated['reg_no'],
                    'year_of_study' => $validated['year_of_study'],
                    'program_id' => $validated['program_id'],
                    'phone_number' => trim((string) $validated['phone_number']),
                ]),
                'company' => Company::create([
                    'user_id' => $user->id,
                    'name' => trim((string) $validated['company_name']),
                    'alias' => trim((string) $validated['alias']),
                    'contact' => trim((string) $validated['contact']),
                    'email' => !empty($validated['company_email']) ? $validated['company_email'] : $validated['email'],
                    'address' => trim((string) $validated['address']),
                    'county_id' => $validated['county_id'],
                    'town_id' => $validated['town_id'],
                    'street' => isset($validated['street']) ? trim((string) $validated['street']) : null,
                    'building' => isset($validated['building']) ? trim((string) $validated['building']) : null,
                ]),
                default => null,
            };

            return $user;
        });

        event(new Registered($user));

        return redirect()->route('login')
            ->with('success', 'Registration successful. Please wait for approval.');
    }
}