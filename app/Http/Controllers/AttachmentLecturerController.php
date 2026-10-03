<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Imports\AttachmentLecturersImport;
use App\Models\AdministrativeUnit;
use App\Models\Attachment;
use App\Models\AttachmentLecturer;
use App\Models\Lecturer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

final class AttachmentLecturerController extends Controller
{
    /**
     * Level ID corresponding to Department administrative units.
     */
    private const DEPARTMENT_LEVEL = 2;

    /**
     * Display listing of attachment lecturers or return DataTables JSON.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = AttachmentLecturer::query()->with([
                'attachment',
                'lecturer.user',
                'department',
            ]);

            if ($request->filled('attachment_id')) {
                $query->where('attachment_id', $request->input('attachment_id'));
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('name', static fn (AttachmentLecturer $row): string => $row->lecturer?->user?->name ?? '-')
                ->addColumn('staff_no', static fn (AttachmentLecturer $row): string => $row->lecturer?->staff_number ?? '-')
                ->addColumn('job_grade', static fn (AttachmentLecturer $row): string => $row->job_grade ?? '-')
                ->addColumn('attachment', static fn (AttachmentLecturer $row): string => $row->attachment?->name ?? '-')
                ->addColumn('department', static fn (AttachmentLecturer $row): string => $row->department?->name ?? '-')
                ->addColumn('students', static fn (AttachmentLecturer $row): string => (string) ($row->department?->slug ?? '0'))
                ->addColumn('action', static fn (): string => '')
                ->rawColumns(['action'])
                ->make(true);
        }

        $attachments = Attachment::query()->orderBy('start_date', 'desc')->get();
        $lecturers = Lecturer::query()->with('user')->orderBy('staff_number')->get();
        $departments = AdministrativeUnit::query()
            ->where('level', self::DEPARTMENT_LEVEL)
            ->orderBy('name')
            ->get();

        return view('admin.attachment_lecturers', compact('attachments', 'lecturers', 'departments'));
    }

    /**
     * Upload and import attachment lecturers from Excel/CSV file.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ]);

        try {
            $import = new AttachmentLecturersImport();

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
            Log::error('Error uploading lecturer attachment file: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while uploading and processing the file.',
            ], 500);
        }
    }

    /**
     * Store a new attachment lecturer record manually.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'staff_no' => ['required', 'string', 'max:255'],
            'job_grade' => ['required', 'string', 'max:255'],
            'attachment' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'students' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $record = DB::transaction(static fn () => AttachmentLecturer::create($validated));

            return response()->json([
                'status' => 'success',
                'message' => 'Record stored successfully.',
                'data' => $record,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Failed to store attachment lecturer record: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the record.',
            ], 500);
        }
    }

    /**
     * Allocate a lecturer to an attachment and department.
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lecturer_id' => ['required', 'exists:lecturers,id'],
            'attachment_id' => ['required', 'exists:attachments,id'],
            'department_id' => ['required', 'exists:administrative_units,id'],
        ]);

        $exists = AttachmentLecturer::query()
            ->where('lecturer_id', $validated['lecturer_id'])
            ->where('attachment_id', $validated['attachment_id'])
            ->where('department_id', $validated['department_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'This allocation record already exists in the system.',
            ], 422);
        }

        try {
            $record = DB::transaction(static fn () => AttachmentLecturer::create([
                'lecturer_id' => $validated['lecturer_id'],
                'attachment_id' => $validated['attachment_id'],
                'department_id' => $validated['department_id'],
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Lecturer allocated successfully.',
                'data' => $record,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Failed to allocate lecturer: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the allocation.',
            ], 500);
        }
    }
}