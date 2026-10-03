<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AttachmentAssessment;
use App\Models\Budget;
use App\Models\FinalReport;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display admin portal dashboard.
     */
    public function portal(): View
    {
        Gate::authorize('view-admin-dashboard');

        return view('admin.portal');
    }

    /**
     * Display students overview.
     */
    public function students(): View
    {
        Gate::authorize('manage-students');

        return view('admin.students');
    }

    /**
     * Display supervisors overview.
     */
    public function supervisors(): View
    {
        Gate::authorize('manage-supervisors');

        return view('admin.supervisors');
    }

    /**
     * Display industry overview.
     */
    public function industry(): View
    {
        Gate::authorize('manage-industry');

        return view('admin.industry');
    }

    /**
     * Display attachments overview.
     */
    public function attachments(): View
    {
        Gate::authorize('manage-attachments');

        return view('admin.attachments');
    }

    /**
     * Display budget management page.
     */
    public function budgets(): View
    {
        Gate::authorize('manage-budgets');

        $budgets = Budget::latest()->get();

        return view('admin.budgets', compact('budgets'));
    }

    /**
     * Store a new budget entry.
     */
    public function storeBudget(Request $request): RedirectResponse
    {
        Gate::authorize('manage-budgets');

        $validated = $request->validate([
            'staffnumber' => ['required', 'string', 'max:255'],
            'grade' => ['required', 'string', 'max:255'],
            'lecturer_name' => ['required', 'string', 'max:255'],
            'daily_allowance' => ['required', 'numeric', 'min:0'],
            'transport_town' => ['required', 'numeric', 'min:0'],
            'totals' => ['required', 'numeric', 'min:0'],
            'student_list_file' => ['nullable', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        try {
            $budget = new Budget();
            $budget->staffnumber = trim($validated['staffnumber']);
            $budget->grade = trim($validated['grade']);
            $budget->lecturer_name = trim($validated['lecturer_name']);
            $budget->daily_allowance = $validated['daily_allowance'];
            $budget->transport_town = $validated['transport_town'];
            $budget->totals = $validated['totals'];

            if ($request->hasFile('student_list_file')) {
                $file = $request->file('student_list_file');
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $file->move(public_path('uploads/student_lists'), $fileName);
                $budget->student_list_file = $fileName;
            }

            $budget->save();

            return redirect()->route('admin.budgets')->with('success', 'Budget saved successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to store budget: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while saving the budget. Please try again.');
        }
    }

    /**
     * Display specific budget details.
     */
    public function showBudget(int $id): View
    {
        Gate::authorize('manage-budgets');

        $budget = Budget::findOrFail($id);

        return view('admin.show-budget', compact('budget'));
    }

    /**
     * Delete a budget record.
     */
    public function destroyBudget(int $id): RedirectResponse
    {
        Gate::authorize('manage-budgets');

        $budget = Budget::find($id);

        if (!$budget) {
            return redirect()->route('admin.budgets')->with('error', 'Budget not found.');
        }

        $budget->delete();

        return redirect()->route('admin.budgets')->with('success', 'Budget deleted successfully.');
    }

    /**
     * Display reports overview.
     */
    public function reports(): View
    {
        Gate::authorize('view-admin-reports');

        return view('admin.reports');
    }

    /**
     * Display administrative settings.
     */
    public function settings(): View
    {
        Gate::authorize('manage-admin-settings');

        return view('admin.settings');
    }

    /**
     * Display attachment assessments summary and totals.
     */
    public function index(): View
    {
        Gate::authorize('view-admin-assessments');

        $assessments = AttachmentAssessment::with([
            'attachmentStudent.student.user',
            'attachmentStudent.student.program',
            'lecturer.user',
            'industrialSupervisor.user',
        ])->get();

        $totals = [
            'lecturer_total' => $assessments->sum(fn ($a) => $a->lecturer_total_marks ?? 0),
            'industrial_total' => $assessments->sum(fn ($a) => $a->industrial_supervisor_total_marks ?? 0),
            'combined_total' => $assessments->sum(
                fn ($a) => ($a->lecturer_total_marks ?? 0) + ($a->industrial_supervisor_total_marks ?? 0)
            ),
        ];

        return view('admin.assessments', compact('assessments', 'totals'));
    }

    /**
     * Display all student final reports.
     */
    public function allFinalReports(): View
    {
        Gate::authorize('view-admin-reports');

        $reports = FinalReport::whereHas('attachmentStudent.student.user')
            ->with(['attachmentStudent.student.user'])
            ->latest()
            ->get();

        return view('admin.final_index', compact('reports'));
    }

    /**
     * Display all student logbooks and entry stats.
     */
    public function allLogbooks(): View
    {
        Gate::authorize('view-admin-reports');

        $students = Student::whereHas('attachments.dailyReports')
            ->with([
                'user',
                'program.parent',
                'attachments' => function ($query) {
                    $query->with([
                        'company.town',
                        'dailyReports' => fn ($q) => $q->orderBy('report_date', 'desc'),
                    ]);
                },
            ])
            ->get()
            ->map(function ($student) {
                $companies = [];
                $attachmentInfo = null;
                $allDailyReports = collect();

                foreach ($student->attachments as $attachment) {
                    if ($attachment->company) {
                        $companies[] = [
                            'name' => $attachment->company->name,
                            'town' => $attachment->company->town->name ?? 'N/A',
                        ];
                    }

                    $attachmentInfo ??= $attachment;
                    $allDailyReports = $allDailyReports->merge($attachment->dailyReports);
                }

                $totalEntries = $allDailyReports->count();
                $latestReport = $allDailyReports->sortByDesc('report_date')->first();
                $latestEntry = $latestReport?->report_date;

                return [
                    'id' => $student->id,
                    'name' => $student->user?->name ?? 'Unknown',
                    'reg_no' => $student->reg_no,
                    'department' => $student->program?->parent?->name ?? 'N/A',
                    'email' => $student->user?->email ?? 'N/A',
                    'phone' => $student->user?->phone_number ?? 'N/A',
                    'companies' => $companies,
                    'company_names' => implode(', ', array_column($companies, 'name')),
                    'company_towns' => implode(', ', array_column($companies, 'town')),
                    'total_entries' => $totalEntries,
                    'latest_entry' => $latestEntry,
                    'latest_entry_formatted' => $latestEntry ? $latestEntry->format('Y-m-d') : null,
                    'has_logbook' => $totalEntries > 0,
                    'attachment_id' => $attachmentInfo?->id,
                ];
            })
            ->filter(fn ($student) => $student['has_logbook'])
            ->sortByDesc('latest_entry')
            ->values();

        return view('admin.logbooks_index', compact('students'));
    }
}