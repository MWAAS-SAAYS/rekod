<?php

declare(strict_types=1);

namespace App\Http\Controllers\IndustrialSupervisor;

use App\Http\Controllers\Controller;
use App\Imports\AttachmentStudentsImport;
use App\Models\AttachmentStudent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class AttachmentStudentController extends Controller
{
    /**
     * Display a listing of attachment students or handle DataTables request.
     */
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('manage-attachment-students');

        if ($request->ajax()) {
            $data = AttachmentStudent::with([
                'attachment:id,name,status',
                'student:id,user_id,reg_no',
                'student.user:id,name',
                'department:id,name',
                'lecturer.user:id,name',
            ])->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', fn ($row) => $row->student?->user?->name ?? '-')
                ->addColumn('reg_no', fn ($row) => $row->student?->reg_no ?? '-')
                ->addColumn('attachment', fn ($row) => $row->attachment?->name ?? '-')
                ->addColumn('department', fn ($row) => $row->department?->name ?? '-')
                ->addColumn('lecturer', fn ($row) => $row->lecturer?->user?->name ?? '-')
                ->addColumn('status', fn ($row) => $row->attachment?->status ?? '-')
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-danger delete" data-id="' . (int) $row->id . '">Delete</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('industrial_supervisor.attachment_students');
    }

    /**
     * Import attachment students via Excel or CSV file.
     */
    public function upload(Request $request): JsonResponse
    {
        Gate::authorize('manage-attachment-students');

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ]);

        try {
            $import = new AttachmentStudentsImport();

            Excel::import($import, $request->file('file'));

            return response()->json([
                'status' => 'success',
                'message' => 'File processed successfully.',
                'stats' => [
                    'success_count' => $import->successCount ?? 0,
                    'fail_count' => count($import->failedRecords ?? []),
                    'failed_records' => $import->failedRecords ?? [],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Attachment students import failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error processing file import. Please verify spreadsheet formatting.',
            ], 500);
        }
    }

    /**
     * Add a single attachment student manually.
     */
    public function add(Request $request): JsonResponse
    {
        Gate::authorize('manage-attachment-students');

        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'reg_no' => ['required', 'string', 'max:50', 'unique:attachment_students,reg_no'],
        ]);

        $student = AttachmentStudent::create([
            'name' => trim($validated['student_name']),
            'reg_no' => strtoupper(trim($validated['reg_no'])),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Student added successfully',
            'student' => $student,
        ], 201);
    }
}