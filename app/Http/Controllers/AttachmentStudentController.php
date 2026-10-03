<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Imports\AttachmentStudentsImport;
use App\Models\Attachment;
use App\Models\AttachmentStudent;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

final class AttachmentStudentController extends Controller
{
    /**
     * Display listing of attachment students or return DataTables JSON.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = AttachmentStudent::query()->with([
                'attachment',
                'student.user',
                'department',
                'lecturer.user',
            ]);

            if ($request->filled('attachment_id')) {
                $query->where('attachment_id', $request->input('attachment_id'));
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', static fn (AttachmentStudent $row): string => $row->student?->user?->name ?? '-')
                ->addColumn('reg_no', static fn (AttachmentStudent $row): string => $row->student?->reg_no ?? '-')
                ->addColumn('attachment', static fn (AttachmentStudent $row): string => $row->attachment?->name ?? '-')
                ->addColumn('department', static fn (AttachmentStudent $row): string => $row->department?->name ?? '-')
                ->addColumn('lecturer', static fn (AttachmentStudent $row): string => $row->lecturer?->user?->name ?? '-')
                ->addColumn('status', static fn (AttachmentStudent $row): string => $row->attachment?->status ?? '-')
                ->addColumn('action', static fn (AttachmentStudent $row): string => '
                    <a href="javascript:void(0)"
                       data-id="' . $row->id . '"
                       class="w-auto text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium inline-flex items-center justify-center rounded-lg text-xs px-2 py-1 text-center open-student_attachment_details_modal-btn">
                       Profile
                    </a>
                ')
                ->rawColumns(['action'])
                ->make(true);
        }

        $attachments = Attachment::query()
            ->select(['id', 'name'])
            ->orderBy('start_date', 'desc')
            ->get();

        $students = Student::query()
            ->select(['id', 'user_id'])
            ->with('user:id,name')
            ->get();

        return view('admin.attachment_students', compact('attachments', 'students'));
    }

    /**
     * Upload and import attachment students from Excel/CSV file.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ]);

        try {
            $import = new AttachmentStudentsImport();

            DB::transaction(static function () use ($import, $request): void {
                Excel::import($import, $request->file('file'));
            });

            return response()->json([
                'status' => 'success',
                'message' => 'File processed successfully.',
                'stats' => [
                    'success_count' => $import->successCount ?? 0,
                    'fail_count' => count($import->failedRecords ?? []),
                    'failed_records' => $import->failedRecords ?? [],
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Error uploading student attachment file: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while uploading and processing the file.',
            ], 500);
        }
    }

    /**
     * Allocate a student to an attachment period.
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'attachment_id' => ['required', 'exists:attachments,id'],
        ]);

        $exists = AttachmentStudent::query()
            ->where('student_id', $validated['student_id'])
            ->where('attachment_id', $validated['attachment_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Attachment student record already exists.',
            ], 422);
        }

        try {
            $attachmentStudent = DB::transaction(
                static fn () => AttachmentStudent::create([
                    'student_id' => $validated['student_id'],
                    'attachment_id' => $validated['attachment_id'],
                ])
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Attachment student created successfully.',
                'data' => $attachmentStudent,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Failed to create attachment student record: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the student attachment.',
            ], 500);
        }
    }

    /**
     * Store a new attachment student record manually.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'reg_no' => ['required', 'string', 'max:255'],
            'attachment_id' => ['required', 'exists:attachments,id'],
            'department_id' => ['nullable', 'integer', 'exists:administrative_units,id'],
            'industrial_supervisor_id' => ['nullable', 'integer', 'exists:industrial_supervisors,id'],
            'status' => ['required', 'string', 'max:255'],
        ]);

        $student = Student::query()->where('reg_no', $validated['reg_no'])->first();

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found.',
            ], 404);
        }

        try {
            $attachmentStudent = DB::transaction(
                static fn () => AttachmentStudent::create([
                    'student_id' => $student->id,
                    'attachment_id' => $validated['attachment_id'],
                    'department_id' => $validated['department_id'] ?? null,
                    'industrial_supervisor_id' => $validated['industrial_supervisor_id'] ?? null,
                    'status' => $validated['status'],
                ])
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Attachment student record created successfully.',
                'data' => $attachmentStudent,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Failed to store attachment student record: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the record.',
            ], 500);
        }
    }
}