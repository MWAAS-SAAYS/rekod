<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UploadLecturersRequest;
use App\Imports\LecturersImport;
use App\Models\Attachment;
use App\Models\AttachmentStudent;
use App\Models\FinalReport;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\WeeklyReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

final class LecturerController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('viewAny', Lecturer::class);

        if ($request->ajax()) {
            $data = Lecturer::query()
                ->select(['id', 'user_id', 'department_id', 'job_grade_id', 'created_at'])
                ->with([
                    'user:id,name,email',
                    'department:id,name',
                    'jobGrade:id,dekut_grade',
                ])
                ->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', static fn (Lecturer $row): string => $row->user?->name ?? '-')
                ->addColumn('email', static fn (Lecturer $row): string => $row->user?->email ?? '-')
                ->addColumn('department', static fn (Lecturer $row): string => $row->department?->name ?? '-')
                ->addColumn('job_grade', static fn (Lecturer $row): string => $row->jobGrade?->dekut_grade ?? 'No Grade Assigned')
                ->addColumn('action', static fn (Lecturer $row): string => sprintf(
                    '<button class="btn btn-sm btn-danger delete" data-id="%d">Delete</button>',
                    $row->id
                ))
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.lecturers');
    }

    public function upload(UploadLecturersRequest $request): JsonResponse
    {
        Gate::authorize('create', Lecturer::class);

        try {
            $import = new LecturersImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'status'         => 'success',
                'message'        => 'Upload completed',
                'success_count'  => $import->successCount,
                'fail_count'     => count($import->failedRecords),
                'failed_records' => $import->failedRecords,
            ]);
        } catch (\Throwable $e) {
            Log::error('Lecturer import failed: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => auth()->id(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred while processing the import.',
            ], 500);
        }
    }

    public function studentsAssigned(): View
    {
        Gate::authorize('viewAssignedStudents', Student::class);

        $students = Student::query()
            ->select(['id', 'user_id', 'reg_no'])
            ->with(['user:id,name,email'])
            ->get();

        $attachments = Attachment::query()
            ->select(['id', 'title', 'status'])
            ->get();

        return view('lecturer.my-students', compact('students', 'attachments'));
    }

    public function showStudent(int $id): View
    {
        $student = Student::query()
            ->select(['id', 'user_id', 'company_id', 'reg_no'])
            ->with(['company:id,name', 'user:id,name,email,phone_number'])
            ->findOrFail($id);

        Gate::authorize('view', $student);

        return view('lecturer.student.show', compact('student'));
    }

    public function studentFeedback(int $id): View
    {
        $student = Student::query()
            ->select(['id', 'user_id', 'reg_no'])
            ->findOrFail($id);

        Gate::authorize('view', $student);

        return view('lecturer.student.feedback', compact('student'));
    }

    public function reports(): View
    {
        return view('lecturer.reports');
    }

    public function logbook(): View
    {
        return view('lecturer.logbook');
    }

    public function evaluate(): View
    {
        return view('lecturer.evaluate');
    }

    public function myStudents(Request $request): View|JsonResponse
    {
        $lecturerId = $this->resolveLecturerId($request);

        if ($request->ajax()) {
            $data = AttachmentStudent::query()
                ->select([
                    'id', 'attachment_id', 'student_id', 'company_id',
                    'industrial_supervisor_id', 'department_id', 'attachment_lecturer_id',
                ])
                ->with([
                    'attachment:id,status',
                    'student:id,user_id,reg_no',
                    'student.user:id,name,email',
                    'industrialSupervisor:id,user_id',
                    'industrialSupervisor.user:id,name,phone_number',
                    'company:id,name',
                    'department:id,name',
                ])
                ->where('attachment_lecturer_id', $lecturerId);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', static fn (AttachmentStudent $row): string => $row->student?->user?->name ?? '-')
                ->addColumn('reg_no', static fn (AttachmentStudent $row): string => $row->student?->reg_no ?? '-')
                ->addColumn('department', static fn (AttachmentStudent $row): string => $row->department?->name ?? '-')
                ->addColumn('status', static fn (AttachmentStudent $row): string => $row->attachment?->status ?? '-')
                ->addColumn('company', static fn (AttachmentStudent $row): string => $row->company?->name ?? '-')
                ->addColumn('industrial_supervisor', static fn (AttachmentStudent $row): string => $row->industrialSupervisor?->user?->name ?? '-')
                ->addColumn('industrial_supervisor_phone', static fn (AttachmentStudent $row): string => $row->industrialSupervisor?->user?->phone_number ?? '-')
                ->addColumn('action', static function (AttachmentStudent $row): string {
                    $regNo = e($row->student?->reg_no ?? '');
                    $name = e($row->student?->user?->name ?? '');
                    $fullName = "{$regNo} - {$name}";
                    $logbookRoute = route('logbook', [$row->id]);

                    return <<<HTML
                    <button class="assessBtn bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700" data-id="{$row->id}" data-name="{$fullName}">
                        Assess
                    </button>
                    <a href="{$logbookRoute}" class="w-auto text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium inline-flex items-center justify-center rounded-lg text-xs px-2 py-1 text-center">
                        Logbook
                    </a>
                    <a href="javascript:void(0)" data-id="{$row->id}" class="w-auto text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium inline-flex items-center justify-center rounded-lg text-xs px-2 py-1 text-center open-student_attachment_details_modal-btn">
                        Profile
                    </a>
                    HTML;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $attachments = Attachment::query()
            ->select(['id', 'title', 'status'])
            ->get();

        return view('lecturer.my-students', compact('attachments'));
    }

    public function weeklyReports(Request $request): View|RedirectResponse
    {
        $lecturerId = $this->resolveLecturerId($request);

        if (!$lecturerId) {
            return redirect()->route('lecturer.my-students')
                ->with('error', 'Please select or verify your lecturer profile first.');
        }

        $weeklyReports = WeeklyReport::query()
            ->select(['id', 'attachment_student_id', 'week_id', 'lecturer_comment', 'created_at'])
            ->whereHas('attachmentStudent', static function (Builder $query) use ($lecturerId): void {
                $query->where('attachment_lecturer_id', $lecturerId);
            })
            ->with(['attachmentStudent.student.user:id,name'])
            ->orderBy('week_id', 'desc')
            ->get();

        return view('lecturer.weekly-reports', [
            'weeklyReports' => $weeklyReports,
            'user_role'     => 'lecturer',
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'lecturer_comment' => 'nullable|string|max:1000',
        ]);

        $report = WeeklyReport::findOrFail($id);
        $report->update([
            'lecturer_comment' => strip_tags($validated['lecturer_comment'] ?? ''),
        ]);

        return redirect()->route('lecturer.weekly-reports')->with('success', 'Comment saved!');
    }

    public function viewStudentReports(Request $request): View|RedirectResponse
    {
        $lecturerId = $this->resolveLecturerId($request);

        if (!$lecturerId) {
            return redirect()->route('lecturer.my-students')
                ->with('error', 'Please select or verify your lecturer profile first.');
        }

        $reports = FinalReport::query()
            ->select(['id', 'attachment_student_id', 'file_path', 'created_at'])
            ->whereHas('attachmentStudent', static function (Builder $query) use ($lecturerId): void {
                $query->where('attachment_lecturer_id', $lecturerId);
            })
            ->with(['attachmentStudent.student.user:id,name'])
            ->latest()
            ->get();

        return view('lecturer.final-reports', [
            'reports'   => $reports,
            'user_role' => 'lecturer',
        ]);
    }

    private function resolveLecturerId(Request $request): ?int
    {
        return $request->session()->get('attachment_lecturer_id')
            ?? auth()->user()?->lecturer?->id;
    }
}