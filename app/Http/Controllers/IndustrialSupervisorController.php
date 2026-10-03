<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AttachmentStudent;
use App\Models\Company;
use App\Models\IndustrialSupervisor;
use App\Models\User;
use App\Models\WeeklyReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

final class IndustrialSupervisorController extends Controller
{
    /**
     * Display a listing of industrial supervisors or return DataTable JSON.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            $data = IndustrialSupervisor::with(['user'])->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', static fn (IndustrialSupervisor $row) => $row->user->name ?? '-')
                ->addColumn('email', static fn (IndustrialSupervisor $row) => $row->user->email ?? '-')
                ->addColumn('phone_number', static fn (IndustrialSupervisor $row) => $row->user->phone_number ?? '-')
                ->addColumn('action', static function (IndustrialSupervisor $row) {
                    return '
                        <button class="btn btn-sm btn-primary edit" data-id="' . $row->id . '">Edit</button>
                        <button class="btn btn-sm btn-danger delete" data-id="' . $row->id . '">Delete</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('company.indurstrial_supervisors');
    }

    /**
     * Store a newly created industrial supervisor in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $userCompany = Company::where('user_id', Auth::id())->first();

        if (!$userCompany) {
            return response()->json([
                'status' => 'error',
                'message' => 'Associated company profile not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:50', 'unique:users,phone_number'],
            'staff_number' => [
                'nullable',
                Rule::unique('industrial_supervisors')->where(
                    static fn ($query) => $query->where('company_id', $userCompany->id)
                ),
            ],
            'position_title' => ['required', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $user = User::updateOrCreate(
                ['email' => strtolower($validated['email'])],
                [
                    'name' => $validated['name'],
                    'phone_number' => $validated['phone_number'] ?? null,
                    'password' => Hash::make((string) config('app.default_password', 'password')),
                    'role' => 'industrial_supervisor',
                ]
            );

            IndustrialSupervisor::create([
                'user_id' => $user->id,
                'company_id' => $userCompany->id,
                'staff_number' => !empty($validated['staff_number']) ? Str::upper($validated['staff_number']) : null,
                'position_title' => $validated['position_title'],
                'phone_alt' => $validated['phone_number'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Supervisor created successfully.',
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Industrial Supervisor creation failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Supervisor creation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display students attached to the logged-in supervisor or return DataTable JSON.
     */
    public function attaches(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            $attachmentId = $request->session()->get('attachment_id');
            $supervisor = IndustrialSupervisor::where('user_id', Auth::id())->first();

            if (!$supervisor) {
                return DataTables::of(collect([]))->make(true);
            }

            $data = AttachmentStudent::with(['attachment', 'student.user', 'department', 'lecturer.user'])
                ->where('attachment_id', $attachmentId)
                ->where('industrial_supervisor_id', $supervisor->id);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', static fn (AttachmentStudent $row) => $row->student->user->name ?? '-')
                ->addColumn('reg_no', static fn (AttachmentStudent $row) => $row->student->reg_no ?? '-')
                ->addColumn('attachment', static fn (AttachmentStudent $row) => $row->attachment->name ?? '-')
                ->addColumn('department', static fn (AttachmentStudent $row) => $row->department->name ?? '-')
                ->addColumn('lecturer', static fn (AttachmentStudent $row) => $row->lecturer->user->name ?? '-')
                ->addColumn('status', static fn (AttachmentStudent $row) => $row->attachment->status ?? '-')
                ->addColumn('action', static function (AttachmentStudent $row) {
                    $studentName = e($row->student->user->name ?? 'Student');

                    return '
                        <div class="flex space-x-2">
                            <button
                                type="button"
                                class="assessBtn text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-200 rounded-lg text-xs px-2 py-1"
                                data-id="' . $row->id . '"
                                data-name="' . $studentName . '">
                                Assess
                            </button>
                            <a href="' . route('logbook', [$row->id]) . '"
                               class="w-auto text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium inline-flex items-center justify-center rounded-lg text-xs px-2 py-1 text-center">
                                Logbook
                            </a>
                            <a href="javascript:void(0)" data-id="' . $row->id . '"
                               class="w-auto text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 font-medium inline-flex items-center justify-center rounded-lg text-xs px-2 py-1 text-center open-student_attachment_details_modal-btn">
                                Profile
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('industrial_supervisor.attaches');
    }

    /**
     * Get list of industrial supervisors for a specific company in JSON format.
     */
    public function getCompanyIndustrialSupervisors(int|string $companyId): JsonResponse
    {
        $supervisors = IndustrialSupervisor::with('user:id,name,phone_number,email')
            ->where('company_id', $companyId)
            ->get(['id', 'user_id']);

        return response()->json($supervisors);
    }

    /**
     * Display weekly reports submitted by attached students.
     */
    public function weeklyReports(): View
    {
        $supervisor = Auth::user()?->industrialSupervisor;

        if (!$supervisor) {
            return view('industrial_supervisor.weekly-reports', ['weeklyReports' => collect()]);
        }

        $weeklyReports = WeeklyReport::whereHas('attachmentStudent', static function ($query) use ($supervisor) {
            $query->where('industrial_supervisor_id', $supervisor->id);
        })
            ->with(['attachmentStudent.student.user'])
            ->orderByDesc('week_id')
            ->get();

        return view('industrial_supervisor.weekly-reports', compact('weeklyReports'));
    }

    /**
     * Approve a student's weekly report and add supervisor comments.
     */
    public function approveWeeklyReport(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'industrial_supervisor_comment' => ['required', 'string'],
        ]);

        $supervisor = Auth::user()?->industrialSupervisor;

        if (!$supervisor) {
            return redirect()
                ->route('industrial_supervisor.weekly-reports')
                ->with('error', 'Industrial supervisor record not found.');
        }

        $weeklyReport = WeeklyReport::where('id', $id)
            ->whereHas('attachmentStudent', static function ($query) use ($supervisor) {
                $query->where('industrial_supervisor_id', $supervisor->id);
            })
            ->firstOrFail();

        $weeklyReport->update([
            'industrial_supervisor_comment' => $validated['industrial_supervisor_comment'],
            'is_approved' => true,
        ]);

        return redirect()
            ->route('industrial_supervisor.weekly-reports')
            ->with('success', 'Weekly report approved successfully.');
    }
}