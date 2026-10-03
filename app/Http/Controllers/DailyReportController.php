<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AttachmentStudent;
use App\Models\DailyReport;
use DateTimeInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

final class DailyReportController extends Controller
{
    /**
     * Display the daily reports calendar view.
     */
    public function index(Request $request, ?int $id = null): View
    {
        $userRole = Auth::user()?->role;

        // Determine attachment student ID based on user role
        if ($userRole === 'student') {
            $attachmentStudentId = $request->session()->get('attachment_student_id');
        } elseif ($id !== null) {
            $attachmentStudentId = $id;
        } else {
            abort(404);
        }

        // Fetch daily reports for the attachment student
        $dailyReports = DailyReport::where('attachment_student_id', $attachmentStudentId)
            ->orderBy('report_date', 'desc')
            ->get();

        // Map daily reports to calendar event objects
        $events = $dailyReports->map(fn (DailyReport $report) => $this->formatCalendarEvent($report))->values();

        // Get attachment student details with eager-loaded nested relationships
        $attachmentStudent = AttachmentStudent::with(['student.user'])
            ->find($attachmentStudentId);

        // Determine submission route (only available for students)
        $reportRoute = $userRole === 'student'
            ? route('student.daily_activities.store')
            : '#';

        return view('daily_activities.index', [
            'events' => $events,
            'user_role' => $userRole,
            'attachment_student' => $attachmentStudent,
            'report_route' => $reportRoute,
        ]);
    }

    /**
     * Store or update a daily activity report.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $attachmentStudentId = $request->session()->get('attachment_student_id');
            $attachmentStudent = $attachmentStudentId ? AttachmentStudent::find($attachmentStudentId) : null;

            if (!$attachmentStudent || !$attachmentStudent->company_id || !$attachmentStudent->start_date) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Fill in your attachment form first.',
                ], 422);
            }

            // Validate the request input payload
            $validated = $request->validate([
                'daily_report_id' => ['nullable', 'exists:daily_reports,id'],
                'report_date' => [
                    'required',
                    'date',
                    'after_or_equal:' . $attachmentStudent->start_date,
                    'before_or_equal:' . $attachmentStudent->end_date,
                ],
                'task_title' => ['required', 'string', 'max:255'],
                'tasks' => ['required', 'string'],
                'skills_learned' => ['required', 'string'],
                'challenges' => ['nullable', 'string'],
            ]);

            $data = [
                'attachment_student_id' => $attachmentStudentId,
                'report_date' => $validated['report_date'],
                'task_title' => $validated['task_title'],
                'tasks' => $validated['tasks'],
                'skills_learned' => $validated['skills_learned'],
                'challenges' => $validated['challenges'] ?? null,
            ];

            // Scope existing record updates strictly to the authenticated student
            if (!empty($validated['daily_report_id'])) {
                $dailyReport = DailyReport::where('attachment_student_id', $attachmentStudentId)
                    ->findOrFail($validated['daily_report_id']);

                $dailyReport->update($data);
            } else {
                $dailyReport = DailyReport::create($data);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Daily activity recorded successfully.',
                'data' => [$this->formatCalendarEvent($dailyReport)],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Daily report save error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format a single daily report record into a standard calendar event array structure.
     */
    private function formatCalendarEvent(DailyReport $report): array
    {
        $formattedDate = $report->report_date instanceof DateTimeInterface
            ? $report->report_date->format('Y-m-d')
            : date('Y-m-d', strtotime((string) $report->report_date));

        return [
            'id' => $report->id,
            'title' => $report->task_title,
            'start' => $formattedDate,
            'end' => $formattedDate,
            'tasks' => $report->tasks,
            'skills_learned' => $report->skills_learned,
            'challenges' => $report->challenges,
            'backgroundColor' => '#3b82f6',
            'textColor' => 'white',
            'extendedProps' => [
                'daily_report_id' => $report->id,
                'task_title' => $report->task_title,
                'tasks' => $report->tasks,
                'skills_learned' => $report->skills_learned,
                'challenges' => $report->challenges,
                'report_date' => $formattedDate,
            ],
        ];
    }
}