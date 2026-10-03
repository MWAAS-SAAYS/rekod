<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use App\Models\AttachmentStudent;
use App\Models\FinalReport;
use App\Models\IndustrialSupervisor;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use App\Models\WeeklyReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

final class StudentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Student::class);

        if ($request->ajax()) {
            $data = Student::query()
                ->select(['id', 'user_id', 'program_id', 'created_at'])
                ->with([
                    'user:id,name,email',
                    'program:id,name,parent_id',
                    'program.parent:id,name',
                ])
                ->whereHas('user')
                ->orderBy(User::select('name')->whereColumn('users.id', 'students.user_id'));

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', static fn (Student $row): string => $row->user?->name ?? '-')
                ->addColumn('email', static fn (Student $row): string => $row->user?->email ?? '-')
                ->addColumn('department', static fn (Student $row): string => $row->program?->parent?->name ?? '-')
                ->addColumn('program', static fn (Student $row): string => $row->program?->name ?? '-')
                ->addColumn('action', static function (Student $row): string {
                    return sprintf('<button class="btn btn-sm btn-danger delete" data-id="%d">Delete</button>', $row->id);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.students');
    }

    public function upload(Request $request): JsonResponse
    {
        Gate::authorize('create', Student::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ]);

        try {
            $import = new StudentsImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'status'         => 'success',
                'message'        => 'Upload completed',
                'success_count'  => $import->successCount ?? 0,
                'fail_count'     => count($import->failedRecords ?? []),
                'failed_records' => $import->failedRecords ?? [],
            ]);
        } catch (Throwable $e) {
            Log::error('Student excel import failed: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred while processing the student import.',
            ], 500);
        }
    }

    public function portal(): View
    {
        Gate::authorize('viewPortal', Student::class);

        return view('student.portal');
    }

    public function storeWeeklyReport(Request $request): RedirectResponse
    {
        Gate::authorize('create', WeeklyReport::class);

        $student = $request->user()?->student;
        if (!$student) {
            return back()->withErrors('Student record not found.');
        }

        $validated = $request->validate([
            'week_id'         => ['required', 'integer', 'between:1,10'],
            'week_start_date' => ['required', 'date'],
            'week_end_date'   => ['required', 'date', 'after_or_equal:week_start_date'],
            'weekly_report'   => ['required', 'string'],
        ]);

        $attachmentId = $request->session()->get('attachment_id');

        $attachmentStudent = AttachmentStudent::query()
            ->where('student_id', $student->id)
            ->where('attachment_id', $attachmentId)
            ->first();

        if (!$attachmentStudent) {
            return back()->withErrors('You are not registered for this attachment.');
        }

        $exists = WeeklyReport::query()
            ->where('attachment_student_id', $attachmentStudent->id)
            ->where('week_id', $validated['week_id'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors("A weekly report for Week {$validated['week_id']} has already been submitted.");
        }

        WeeklyReport::create([
            'attachment_student_id' => $attachmentStudent->id,
            'week_id'               => $validated['week_id'],
            'week_start_date'       => $validated['week_start_date'],
            'week_end_date'         => $validated['week_end_date'],
            'weekly_report'         => strip_tags($validated['weekly_report']),
            'is_approved'           => false,
        ]);

        return back()->with('success', 'Weekly report submitted successfully.');
    }

    public function weeklyReports(Request $request): View
    {
        $user = Auth::user();
        $userRole = 'guest';

        if ($user?->isStudent()) {
            $userRole = 'student';
            $student = $user->student;

            $enrollment = AttachmentStudent::query()
                ->where('student_id', $student?->id)
                ->where('attachment_id', $request->session()->get('attachment_id'))
                ->first();

            $weeklyReports = $enrollment
                ? WeeklyReport::query()
                    ->where('attachment_student_id', $enrollment->id)
                    ->orderBy('week_id', 'asc')
                    ->get()
                : collect();

        } elseif ($user?->role === 'industrial_supervisor') {
            $userRole = 'industrial_supervisor';

            $supervisor = IndustrialSupervisor::query()->where('user_id', $user->id)->first();

            $weeklyReports = $supervisor
                ? WeeklyReport::query()
                    ->select(['id', 'attachment_student_id', 'week_id', 'week_start_date', 'week_end_date', 'weekly_report', 'is_approved', 'created_at'])
                    ->whereIn('attachment_student_id', static fn (Builder $query): Builder => $query
                        ->select('id')
                        ->from('attachment_students')
                        ->where('industrial_supervisor_id', $supervisor->id)
                    )
                    ->with(['attachmentStudent.student.user:id,name,email'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();

        } elseif ($user?->role === 'lecturer') {
            $userRole = 'lecturer';

            $lecturer = Lecturer::query()->where('user_id', $user->id)->first();

            $weeklyReports = $lecturer
                ? WeeklyReport::query()
                    ->select(['id', 'attachment_student_id', 'week_id', 'week_start_date', 'week_end_date', 'weekly_report', 'is_approved', 'created_at'])
                    ->whereIn('attachment_student_id', static fn (Builder $query): Builder => $query
                        ->select('id')
                        ->from('attachment_students')
                        ->where('lecturer_id', $lecturer->id)
                    )
                    ->with(['attachmentStudent.student.user:id,name,email'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();

        } else {
            $weeklyReports = collect();
        }

        return view('student.weekly-reports', [
            'weeklyReports' => $weeklyReports,
            'user_role'     => $userRole,
        ]);
    }

    public function storeFinalReport(Request $request): RedirectResponse
    {
        Gate::authorize('create', FinalReport::class);

        $attachmentStudent = $request->user()?->student?->attachmentStudent;

        if (!$attachmentStudent) {
            return back()->withErrors('Attachment record not found.');
        }

        if (FinalReport::query()->where('attachment_student_id', $attachmentStudent->id)->exists()) {
            return back()->withErrors('You have already submitted your final report.');
        }

        $validated = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'content'           => ['required', 'string'],
            'final_report_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $path = $request->file('final_report_file')->store('reports/final', 'public');

        FinalReport::create([
            'attachment_student_id' => $attachmentStudent->id,
            'title'                 => trim($validated['title']),
            'content'               => strip_tags($validated['content']),
            'file_path'             => $path,
            'is_submitted'          => true,
        ]);

        return redirect()->back()->with('success', 'Final report submitted successfully.');
    }

    public function finalReport(Request $request): View|RedirectResponse
    {
        $student = $request->user()?->student;
        $attachmentStudent = $student?->attachmentStudent;

        if (!$attachmentStudent) {
            return back()->withErrors('Attachment record not found.');
        }

        $finalReport = FinalReport::query()
            ->where('attachment_student_id', $attachmentStudent->id)
            ->first();

        return view('student.final-report', [
            'final_report'      => $finalReport,
            'attachmentStudent' => $attachmentStudent,
        ]);
    }
}