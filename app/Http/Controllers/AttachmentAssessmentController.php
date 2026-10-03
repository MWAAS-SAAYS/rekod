<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\AttachmentAssessment;
use App\Models\AttachmentStudent;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

final class AttachmentAssessmentController extends Controller
{
    /**
     * Display a listing of assigned students and assessment records.
     */
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('view-assessments');

        if ($request->ajax()) {
            $query = AttachmentStudent::with([
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
                ->addColumn('name', fn ($row) => $row->student?->user?->name ?? '—')
                ->addColumn('reg_no', fn ($row) => $row->student?->reg_no ?? '—')
                ->addColumn('attachment', fn ($row) => $row->attachment?->name ?? '—')
                ->addColumn('department', fn ($row) => $row->department?->name ?? '—')
                ->addColumn('lecturer', fn ($row) => $row->lecturer?->user?->name ?? '—')
                ->addColumn('action', fn ($row) => '<button class="text-blue-600 hover:underline assessment-btn" data-id="' . (int) $row->id . '">Assess</button>')
                ->rawColumns(['action'])
                ->make(true);
        }

        $attachments = Attachment::select('id', 'name')
            ->orderBy('start_date', 'desc')
            ->get();

        $students = Student::select('id', 'user_id', 'reg_no')
            ->with('user:id,name')
            ->get();

        return view('lecturer.my-students', compact('attachments', 'students'));
    }

    /**
     * Show form for creating industrial supervisor assessment.
     */
    public function createIndustrial(int $studentId): View
    {
        Gate::authorize('assess-industrial');

        $student = Student::with(['user', 'attachments'])->findOrFail($studentId);

        return view('attaches.industrial_supervisor', compact('student'));
    }

    /**
     * Store or update industrial supervisor assessment.
     */
    public function storeIndustrial(Request $request): JsonResponse
    {
        Gate::authorize('assess-industrial');

        $validated = $request->validate([
            'attachment_student_id' => ['required', 'exists:attachment_students,id'],
            'punctuality_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'punctuality_remarks' => ['required', 'string'],
            'attendance_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'attendance_remarks' => ['required', 'string'],
            'basic_skills_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'basic_skills_remarks' => ['required', 'string'],
            'general_office_applications_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'general_office_applications_remarks' => ['required', 'string'],
            'technical_applications_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'technical_applications_remarks' => ['required', 'string'],
            'area_of_specialization_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'area_of_specialization_remarks' => ['required', 'string'],
            'scientific_and_technical_knowledge_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'scientific_and_technical_knowledge_remarks' => ['required', 'string'],
            'intelligence_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'intelligence_remarks' => ['required', 'string'],
            'learning_ability_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'learning_ability_remarks' => ['required', 'string'],
            'responsibility_acceptance_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'responsibility_acceptance_remarks' => ['required', 'string'],
            'acceptability_to_colleagues_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'acceptability_to_colleagues_remarks' => ['required', 'string'],
            'improvisation_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'improvisation_remarks' => ['required', 'string'],
            'environment_adjustment_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'environment_adjustment_remarks' => ['required', 'string'],
            'dependability_and_reliability_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'dependability_and_reliability_remarks' => ['required', 'string'],
            'organization_and_planning_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'organization_and_planning_remarks' => ['required', 'string'],
            'effective_time_use_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'effective_time_use_remarks' => ['required', 'string'],
        ]);

        try {
            AttachmentAssessment::updateOrCreate(
                ['attachment_student_id' => $validated['attachment_student_id']],
                $validated
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Industrial assessment saved successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to store industrial assessment: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the industrial assessment.',
            ], 500);
        }
    }

    /**
     * Show form for creating school / lecturer assessment.
     */
    public function createSchool(int $studentId): View
    {
        Gate::authorize('assess-academic');

        $student = Student::with(['user', 'attachments'])->findOrFail($studentId);

        return view('my.lecturer', compact('student'));
    }

    /**
     * Store school / lecturer assessment.
     */
    public function storeSchool(Request $request): JsonResponse
    {
        Gate::authorize('assess-academic');

        $validated = $request->validate([
            'attachment_student_id' => ['required', 'exists:attachment_students,id'],
            'practical_orientation_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'practical_orientation_remarks' => ['required', 'string'],
            'intellectual_activity_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'intellectual_activity_remarks' => ['required', 'string'],
            'independence_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'independence_remarks' => ['required', 'string'],
            'communication_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'communication_remarks' => ['required', 'string'],
            'technology_and_skills_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'technology_and_skills_remarks' => ['required', 'string'],
            'innovativeness_marks' => ['required', 'integer', 'min:0', 'max:5'],
            'innovativeness_remarks' => ['required', 'string'],
        ]);

        try {
            $existing = AttachmentAssessment::where('attachment_student_id', $validated['attachment_student_id'])
                ->where('practical_orientation_marks', '>', 0)
                ->first();

            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lecturer assessment already submitted!',
                ], 422);
            }

            AttachmentAssessment::updateOrCreate(
                ['attachment_student_id' => $validated['attachment_student_id']],
                $validated
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Assessment saved successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to store school assessment: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the academic assessment.',
            ], 500);
        }
    }

    /**
     * Check if a lecturer assessment exists for a student and return component breakdown.
     */
    public function check(Request $request): JsonResponse
    {
        Gate::authorize('view-assessments');

        $studentId = $request->input('student_id');
        if (!$studentId) {
            return response()->json(['exists' => false]);
        }

        $assessment = AttachmentAssessment::where('attachment_student_id', $studentId)->first();

        if ($assessment && $assessment->practical_orientation_marks > 0) {
            return response()->json([
                'exists' => true,
                'total' => (int) $assessment->lecturer_total_marks,
                'assessment' => [
                    'Practical Orientation' => ['marks' => $assessment->practical_orientation_marks, 'remarks' => $assessment->practical_orientation_remarks],
                    'Intellectual Activity' => ['marks' => $assessment->intellectual_activity_marks, 'remarks' => $assessment->intellectual_activity_remarks],
                    'Independence'          => ['marks' => $assessment->independence_marks, 'remarks' => $assessment->independence_remarks],
                    'Communication'         => ['marks' => $assessment->communication_marks, 'remarks' => $assessment->communication_remarks],
                    'Technology & Skills'   => ['marks' => $assessment->technology_and_skills_marks, 'remarks' => $assessment->technology_and_skills_remarks],
                    'Innovativeness'        => ['marks' => $assessment->innovativeness_marks, 'remarks' => $assessment->innovativeness_remarks],
                ],
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Check if an industrial supervisor assessment exists for a student and return breakdown.
     */
    public function checkIndustry(Request $request): JsonResponse
    {
        Gate::authorize('view-assessments');

        $studentId = $request->input('student_id');
        if (!$studentId) {
            return response()->json(['exists' => false]);
        }

        $assessment = AttachmentAssessment::where('attachment_student_id', $studentId)->first();

        if ($assessment && $assessment->punctuality_marks > 0) {
            return response()->json([
                'exists' => true,
                'total' => (int) $assessment->industrial_supervisor_total_marks,
                'assessment' => [
                    'Punctuality'          => ['marks' => $assessment->punctuality_marks, 'remarks' => $assessment->punctuality_remarks],
                    'Attendance'           => ['marks' => $assessment->attendance_marks, 'remarks' => $assessment->attendance_remarks],
                    'Basic Skills'         => ['marks' => $assessment->basic_skills_marks, 'remarks' => $assessment->basic_skills_remarks],
                    'Office Apps'          => ['marks' => $assessment->general_office_applications_marks, 'remarks' => $assessment->general_office_applications_remarks],
                    'Technical Apps'       => ['marks' => $assessment->technical_applications_marks, 'remarks' => $assessment->technical_applications_remarks],
                    'Specialization'       => ['marks' => $assessment->area_of_specialization_marks, 'remarks' => $assessment->area_of_specialization_remarks],
                    'Scientific Knowledge' => ['marks' => $assessment->scientific_and_technical_knowledge_marks, 'remarks' => $assessment->scientific_and_technical_knowledge_remarks],
                    'Intelligence'         => ['marks' => $assessment->intelligence_marks, 'remarks' => $assessment->intelligence_remarks],
                    'Learning Ability'     => ['marks' => $assessment->learning_ability_marks, 'remarks' => $assessment->learning_ability_remarks],
                    'Responsibility'       => ['marks' => $assessment->responsibility_acceptance_marks, 'remarks' => $assessment->responsibility_acceptance_remarks],
                    'Colleague Acceptance' => ['marks' => $assessment->acceptability_to_colleagues_marks, 'remarks' => $assessment->acceptability_to_colleagues_remarks],
                    'Improvisation'        => ['marks' => $assessment->improvisation_marks, 'remarks' => $assessment->improvisation_remarks],
                    'Env Adjustment'       => ['marks' => $assessment->environment_adjustment_marks, 'remarks' => $assessment->environment_adjustment_remarks],
                    'Reliability'          => ['marks' => $assessment->dependability_and_reliability_marks, 'remarks' => $assessment->dependability_and_reliability_remarks],
                    'Organization'         => ['marks' => $assessment->organization_and_planning_marks, 'remarks' => $assessment->organization_and_planning_remarks],
                    'Time Use'             => ['marks' => $assessment->effective_time_use_marks, 'remarks' => $assessment->effective_time_use_remarks],
                ],
            ]);
        }

        return response()->json(['exists' => false]);
    }
}