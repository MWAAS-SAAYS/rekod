<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\AttachmentLecturer;
use App\Models\AttachmentStudent;
use App\Models\IndustrialSupervisor;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

final class AttachmentSelectedController extends Controller
{
    /**
     * Display attachment selection or user-specific attachment records based on role.
     */
    public function index(): View
    {
        $user = Auth::user();
        $role = $user?->role;

        if ($role === 'student') {
            $student = Student::query()->where('user_id', $user->id)->first();

            if (! $student) {
                $attachments = Attachment::query()->orderBy('start_date', 'desc')->get();

                return view('attachment_selected.index', compact('attachments'));
            }

            $attachmentStudents = AttachmentStudent::query()
                ->with('attachment')
                ->where('student_id', $student->id)
                ->get();

            if ($attachmentStudents->isEmpty()) {
                $attachments = Attachment::query()->orderBy('start_date', 'desc')->get();

                return view('attachment_selected.index', compact('attachments'));
            }

            return view('attachment_selected.students', [
                'attachment_students' => $attachmentStudents,
            ]);
        }

        if ($role === 'lecturer') {
            $lecturer = Lecturer::query()->where('user_id', $user->id)->first();

            if (! $lecturer) {
                $attachments = Attachment::query()->orderBy('start_date', 'desc')->get();

                return view('attachment_selected.index', compact('attachments'));
            }

            $attachmentLecturers = AttachmentLecturer::query()
                ->with('attachment')
                ->where('lecturer_id', $lecturer->id)
                ->get();

            if ($attachmentLecturers->isEmpty()) {
                $attachments = Attachment::query()->orderBy('start_date', 'desc')->get();

                return view('attachment_selected.index', compact('attachments'));
            }

            return view('attachment_selected.lecturers', [
                'attachment_lecturers' => $attachmentLecturers,
            ]);
        }

        if ($role === 'industrial_supervisor') {
            $supervisor = IndustrialSupervisor::query()->where('user_id', $user->id)->first();

            if (! $supervisor) {
                $attachments = Attachment::query()->orderBy('start_date', 'desc')->get();

                return view('attachment_selected.index', compact('attachments'));
            }

            $assignedIds = AttachmentStudent::query()
                ->where('industrial_supervisor_id', $supervisor->id)
                ->distinct()
                ->pluck('attachment_id');

            $attachments = $assignedIds->isEmpty()
                ? Attachment::query()->orderBy('start_date', 'desc')->get()
                : Attachment::query()->whereIn('id', $assignedIds)->orderBy('start_date', 'desc')->get();

            return view('attachment_selected.index', compact('attachments'));
        }

        $attachments = Attachment::query()->orderBy('start_date', 'desc')->get();

        return view('attachment_selected.index', compact('attachments'));
    }

    /**
     * Store the selected attachment period in the active session and persist enrollment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'attachment_id' => ['required', 'exists:attachments,id'],
            'attachment_name' => ['required', 'string', 'max:255'],
        ]);

        try {
            $user = Auth::user();

            if (! $user) {
                return redirect()->route('login')->with('error', 'Please log in to continue.');
            }

            DB::transaction(static function () use ($user, $validated): void {
                if ($user->role === 'student') {
                    $student = Student::query()->where('user_id', $user->id)->first();

                    if (! $student) {
                        throw new RuntimeException('Student profile not found.');
                    }

                    $enrollment = AttachmentStudent::query()->updateOrCreate(
                        ['student_id' => $student->id],
                        [
                            'attachment_id' => $validated['attachment_id'],
                            'status' => 'active',
                        ]
                    );

                    session([
                        'attachment_id' => $validated['attachment_id'],
                        'attachment_name' => $validated['attachment_name'],
                        'attachment_student_id' => $enrollment->id,
                    ]);
                } elseif ($user->role === 'lecturer') {
                    $lecturer = Lecturer::query()->where('user_id', $user->id)->first();

                    if (! $lecturer) {
                        throw new RuntimeException('Lecturer profile not found.');
                    }

                    $enrollment = AttachmentLecturer::query()->updateOrCreate(
                        ['lecturer_id' => $lecturer->id],
                        [
                            'attachment_id' => $validated['attachment_id'],
                            'job_grade' => $lecturer->job_grade,
                            'department_id' => $lecturer->department_id,
                        ]
                    );

                    session([
                        'attachment_id' => $validated['attachment_id'],
                        'attachment_name' => $validated['attachment_name'],
                        'attachment_lecturer_id' => $enrollment->id,
                    ]);
                } elseif ($user->role === 'industrial_supervisor') {
                    session([
                        'attachment_id' => $validated['attachment_id'],
                        'attachment_name' => $validated['attachment_name'],
                    ]);
                }
            });

            return redirect()->route('welcome')->with('success', 'Attachment period selected successfully!');
        } catch (Throwable $e) {
            Log::error('Attachment period selection failed: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'An error occurred while saving your selection. Please try again.');
        }
    }
}