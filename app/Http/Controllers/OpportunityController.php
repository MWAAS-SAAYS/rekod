<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Services\RegNoParser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

final class OpportunityController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Opportunity::class);

        $user = Auth::user();
        $isCompany = $user?->role === 'company';

        if ($request->ajax()) {
            if ($isCompany) {
                $query = Opportunity::query()
                    ->select(['id', 'company_id', 'title', 'location', 'expiry_date', 'link', 'is_paid', 'slots_count', 'created_at'])
                    ->where('company_id', $user?->id)
                    ->latest();
            } else {
                $query = Opportunity::query()
                    ->select(['id', 'company_id', 'title', 'location', 'expiry_date', 'link', 'is_paid', 'slots_count', 'program_code'])
                    ->with(['company:id,name'])
                    ->whereDate('expiry_date', '>=', now());

                $regNo = $user?->reg_no ?? $user?->student?->reg_no;
                if ($regNo) {
                    $parsed = RegNoParser::parse($regNo);
                    if (!empty($parsed['prefix'])) {
                        $query->where(static function (Builder $q) use ($parsed): void {
                            $q->where('program_code', $parsed['prefix'])
                              ->orWhereNull('program_code');
                        });
                    }
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('company_name', static fn (Opportunity $row): string => e($row->company?->name ?? 'N/A'))
                ->editColumn('expiry_date', static fn (Opportunity $row): string => $row->expiry_date ? Carbon::parse($row->expiry_date)->format('M d, Y') : '-')
                ->addColumn('link', static fn (Opportunity $row): string => e($row->link ?? ''))
                ->addColumn('is_paid', static fn (Opportunity $row): string => $row->is_paid
                    ? '<span class="text-green-600 font-semibold">Paid</span>'
                    : '<span class="text-gray-500">Unpaid</span>'
                )
                ->addColumn('slots_count', static fn (Opportunity $row): int => (int) ($row->slots_count ?? 1))
                ->addColumn('action', static function (Opportunity $row) use ($isCompany): string {
                    if ($isCompany) {
                        $destroyRoute = route('opportunities.destroy', $row->id);
                        $csrf = csrf_field();
                        $method = method_field('DELETE');

                        return <<<HTML
                        <form method="POST" action="{$destroyRoute}" class="inline-block" onsubmit="return confirm('Are you sure?');">
                            {$csrf}
                            {$method}
                            <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        HTML;
                    }

                    if ($row->is_expired) {
                        return '<span class="text-gray-400 font-semibold">Expired</span>';
                    }

                    if ($row->link) {
                        $link = e($row->link);
                        return <<<HTML
                        <a href="{$link}" target="_blank" rel="noopener noreferrer" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                            Apply
                        </a>
                        HTML;
                    }

                    return '<span class="text-gray-400">No Link</span>';
                })
                ->rawColumns(['action', 'is_paid'])
                ->make(true);
        }

        return view('opportunities.index', [
            'isCompany' => $isCompany,
        ]);
    }

    public function myOpportunities(): View
    {
        Gate::authorize('viewCompanyOpportunities', Opportunity::class);

        $opportunities = Opportunity::query()
            ->select(['id', 'company_id', 'title', 'location', 'expiry_date', 'slots_count', 'created_at'])
            ->where('company_id', Auth::id())
            ->latest()
            ->get();

        return view('opportunities.my-opportunities', compact('opportunities'));
    }

    public function create(): View
    {
        Gate::authorize('create', Opportunity::class);

        return view('opportunities.create');
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Opportunity::class);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'location'     => ['required', 'string', 'max:255'],
            'expiry_date'  => [
                'required',
                'date',
                'after:today',
                'before_or_equal:' . now()->addDays(90)->format('Y-m-d'),
            ],
            'link'         => ['required', 'url'],
            'program_code' => ['nullable', 'string', 'max:20'],
            'is_paid'      => ['nullable', 'boolean'],
            'slots_count'  => ['nullable', 'integer', 'min:1'],
        ]);

        Opportunity::create([
            'company_id'   => Auth::id(),
            'title'        => $validated['title'],
            'description'  => $validated['description'],
            'location'     => $validated['location'],
            'expiry_date'  => $validated['expiry_date'],
            'link'         => $validated['link'],
            'program_code' => $validated['program_code'] ?? null,
            'is_paid'      => $request->boolean('is_paid'),
            'slots_count'  => $validated['slots_count'] ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Opportunity posted successfully!',
        ]);
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        Gate::authorize('delete', $opportunity);

        $opportunity->delete();

        return back()->with('success', 'Opportunity deleted successfully.');
    }

    public function apply(int $id): RedirectResponse
    {
        $user = Auth::user();

        if ($user?->role !== 'student') {
            abort(403, 'Only students can apply to opportunities.');
        }

        $opportunity = Opportunity::findOrFail($id);

        $alreadyApplied = $opportunity->applicants()->where('user_id', $user->id)->exists();
        if ($alreadyApplied) {
            return redirect()->back()->with('error', 'You have already applied for this opportunity.');
        }

        $opportunity->applicants()->attach($user->id, [
            'applied_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Application submitted successfully!');
    }

    public function showApplyForm(int $id): View
    {
        if (Auth::user()?->role !== 'student') {
            abort(403, 'Only students can apply.');
        }

        $opportunity = Opportunity::findOrFail($id);

        return view('opportunities.apply', compact('opportunity'));
    }

    public function submitApplication(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();

        if ($user?->role !== 'student') {
            abort(403, 'Only students can apply.');
        }

        $opportunity = Opportunity::findOrFail($id);

        if ($opportunity->applicants()->where('user_id', $user->id)->exists()) {
            return redirect()->route('opportunities.index')->with('error', 'You have already applied for this opportunity.');
        }

        $data = $request->validate([
            'cover_letter' => ['required', 'string', 'max:2000'],
            'cv'           => ['required', 'file', 'mimes:pdf,doc,docx', 'max:2048'],
        ]);

        $cvPath = $request->file('cv')->store('cvs', 'private');

        $opportunity->applicants()->attach($user->id, [
            'cover_letter' => $data['cover_letter'],
            'cv_path'      => $cvPath,
            'applied_at'   => now(),
        ]);

        return redirect()->route('opportunities.index')->with('success', 'Application submitted successfully!');
    }

    public function showApplications(int $opportunityId): View
    {
        $opportunity = Opportunity::query()
            ->with(['applicants' => static fn ($query) => $query->select('users.id', 'users.name', 'users.email')])
            ->findOrFail($opportunityId);

        Gate::authorize('viewApplications', $opportunity);

        return view('opportunities.applications', compact('opportunity'));
    }
}