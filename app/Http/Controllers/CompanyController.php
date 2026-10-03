<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Location;
use App\Models\Opportunity;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

final class CompanyController extends Controller
{
    /**
     * Display companies list or DataTable JSON response.
     */
    public function companies(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            $query = Company::with(['county', 'town'])->select('companies.*');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('county', static fn ($row) => $row->county->name ?? '-')
                ->editColumn('town', static fn ($row) => $row->town->name ?? '-')
                ->addColumn('action', static fn ($row) => '')
                ->rawColumns(['action'])
                ->make(true);
        }

        $counties = Location::where('level', 1)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select(['id', 'name', 'code'])
            ->get();

        $sub_counties = Location::where('level', 2)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select(['id', 'name', 'code', 'parent_code'])
            ->get();

        $towns = Location::where('level', 3)
            ->select(['id', 'name', 'code', 'parent_code'])
            ->orderBy('name', 'ASC')
            ->get();

        return view('companies', compact('counties', 'sub_counties', 'towns'));
    }

    /**
     * Store a newly created company and associated user account.
     */
    public function storeCompany(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:companies,name'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'alias' => ['required', 'string', 'max:50', 'unique:companies,alias'],
            'contact' => ['required', 'string', 'max:50', 'unique:companies,contact'],
            'parent_company' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'county_id' => ['required', 'exists:locations,id'],
            'town_id' => ['required', 'exists:locations,id'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $user = User::updateOrCreate(
                ['email' => Str::lower($validated['email'])],
                [
                    'name' => $validated['name'],
                    'phone_number' => $validated['contact'],
                    'password' => Hash::make((string) config('app.default_password')),
                    'role' => 'company',
                ]
            );

            $validated['user_id'] = $user->id;
            Company::create($validated);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Company created successfully',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Company creation failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Company Creation Failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display company portal dashboard view.
     */
    public function portal(): View
    {
        return view('company.portal');
    }

    /**
     * Display list of students attached to the authenticated company.
     */
    public function students(): View
    {
        $companyId = Auth::id();

        $students = Student::whereHas('placements', static function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->with('user')->get();

        return view('company.students', compact('students'));
    }

    /**
     * Display all active job/attachment opportunities.
     */
    public function opportunities(): View
    {
        $opportunities = Opportunity::orderBy('created_at', 'desc')->get();

        return view('company.opportunities', compact('opportunities'));
    }

    /**
     * Display company's owned opportunities list.
     */
    public function index(): View
    {
        $opportunities = Opportunity::where('industry_id', Auth::id())->get();

        return view('company.opportunities', compact('opportunities'));
    }

    /**
     * Show form to create a new opportunity.
     */
    public function createOpportunity(): View
    {
        return view('company.create-opportunity');
    }

    /**
     * Store a new opportunity in database.
     */
    public function storeOpportunity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string'],
            'deadline' => ['required', 'date'],
        ]);

        $opportunity = Opportunity::create([
            'industry_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'deadline' => $validated['deadline'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Opportunity created successfully.',
            'data' => $opportunity,
        ]);
    }

    /**
     * Display company documents page.
     */
    public function documents(): View
    {
        return view('company.documents');
    }

    /**
     * Display company reports page.
     */
    public function reports(): View
    {
        return view('company.reports');
    }

    /**
     * Remove the specified opportunity.
     */
    public function destroy(int $id): RedirectResponse
    {
        $opportunity = Opportunity::where('id', $id)
            ->where('industry_id', Auth::id())
            ->firstOrFail();

        $opportunity->delete();

        return back()->with('success', 'Opportunity deleted successfully.');
    }

    /**
     * View applications for a specific opportunity.
     */
    public function applications(Opportunity $opportunity): View
    {
        if ($opportunity->industry_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $opportunity->load(['applications.student.user']);
        $applications = $opportunity->applications;

        return view('company.opportunities.applications', compact('opportunity', 'applications'));
    }
}