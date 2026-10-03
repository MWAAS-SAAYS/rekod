<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AttachmentAssessment;
use App\Models\AttachmentStudent;
use App\Models\Company;
use App\Models\DailyReport;
use App\Models\FinalReport;
use App\Models\IndustrialSupervisor;
use App\Models\Lecturer;
use App\Models\Location;
use App\Models\Student;
use App\Models\User;
use App\Models\WeeklyReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class DashboardController extends Controller
{
    /**
     * Display the main dashboard with system analytics and recent activity metrics.
     */
    public function index(): View
    {
        $currentYear = (int) date('Y');

        // Total Counts
        $totalStudents = Student::count();
        $totalLecturers = Lecturer::count();
        $totalIndustrialSupervisors = IndustrialSupervisor::count();
        $totalUsers = User::count();
        $totalCompanies = Company::count();
        $totalTowns = Location::where('level', 3)->count();
        $totalDailyReports = DailyReport::count();
        $totalAssessments = AttachmentAssessment::count();
        $totalWeeklyReports = WeeklyReport::count();
        $totalFinalReports = FinalReport::count();

        // Activity metrics
        $weeklyReportsThisWeek = WeeklyReport::where('created_at', '>=', now()->subDays(7))->count();

        // Chart and Map metrics
        $monthlyTrends = $this->getMonthlyTrends($currentYear);
        $studentLocations = $this->getStudentLocations();
        $departmentDistribution = $this->getDepartmentDistribution();
        $topCompanies = $this->getTopCompanies();

        // Recent Activity Stream
        $recentDailyReports = $this->getRecentDailyReports();
        $recentWeeklyReports = $this->getRecentWeeklyReports();
        $recentAssessments = $this->getRecentAssessments();
        $recentFinalReports = $this->getRecentFinalReports();

        // Domain Statistics
        $supervisorStats = $this->getSupervisorStats();
        $attachmentStats = $this->getAttachmentStats();

        return view('dashboard', compact(
            'totalStudents',
            'totalLecturers',
            'totalIndustrialSupervisors',
            'totalUsers',
            'totalCompanies',
            'totalTowns',
            'totalDailyReports',
            'totalAssessments',
            'totalWeeklyReports',
            'weeklyReportsThisWeek',
            'totalFinalReports',
            'monthlyTrends',
            'studentLocations',
            'departmentDistribution',
            'topCompanies',
            'recentDailyReports',
            'recentWeeklyReports',
            'recentFinalReports',
            'recentAssessments',
            'supervisorStats',
            'attachmentStats',
            'currentYear'
        ));
    }

    /**
     * Fetch recent daily reports with student metadata using Eloquent eager loading.
     */
    private function getRecentDailyReports(): Collection
    {
        return DailyReport::with(['attachmentStudent.student.user', 'weeklyReport.attachmentStudent.student.user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(static function (DailyReport $report) {
                $student = $report->attachmentStudent->student ?? $report->weeklyReport->attachmentStudent->student ?? null;

                if ($student?->user) {
                    $report->student_name = $student->user->name ?? 'Unknown';
                    $report->reg_no = $student->reg_no ?? 'N/A';
                } else {
                    $report->student_name = 'Daily Report #' . $report->id;
                    $report->reg_no = 'N/A';
                }

                return $report;
            });
    }

    /**
     * Fetch recent weekly reports with eager-loaded student and user relations.
     */
    private function getRecentWeeklyReports(): Collection
    {
        return WeeklyReport::with(['student.user', 'attachmentStudent.student.user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(static function (WeeklyReport $report) {
                $student = $report->student ?? $report->attachmentStudent->student ?? null;

                if ($student?->user) {
                    $report->student_name = $student->user->name ?? 'Unknown';
                    $report->reg_no = $student->reg_no ?? 'N/A';
                } else {
                    $report->student_name = 'Unknown Student';
                    $report->reg_no = 'N/A';
                }

                return $report;
            });
    }

    /**
     * Fetch recent attachment assessments.
     */
    private function getRecentAssessments(): Collection
    {
        return AttachmentAssessment::with('attachmentStudent.student.user')
            ->latest()
            ->take(5)
            ->get()
            ->map(static function (AttachmentAssessment $assessment) {
                $assessment->student_name = $assessment->attachmentStudent->student->user->name ?? 'Unknown Student';
                $assessment->assessment_name = $assessment->assessment_name ?? null;
                $assessment->score = $assessment->score ?? null;
                $assessment->grade = $assessment->grade ?? null;

                return $assessment;
            });
    }

    /**
     * Fetch recent final reports.
     */
    private function getRecentFinalReports(): Collection
    {
        return FinalReport::with('attachmentStudent.student.user')
            ->latest()
            ->take(5)
            ->get()
            ->map(static function (FinalReport $report) {
                $report->student_name = $report->attachmentStudent->student->user->name ?? 'Unknown Student';

                return $report;
            });
    }

    /**
     * Aggregate monthly submission and registration trends for dashboard charts using grouped queries.
     */
    private function getMonthlyTrends(int $year): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $fetchMonthlyCounts = static function ($modelClass) use ($year): array {
            $counts = $modelClass::whereYear('created_at', $year)
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('month')
                ->pluck('count', 'month')
                ->all();

            $monthlyData = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthlyData[] = $counts[$m] ?? 0;
            }

            return $monthlyData;
        };

        return [
            'labels' => $months,
            'daily_reports' => $fetchMonthlyCounts(DailyReport::class),
            'weekly_reports' => $fetchMonthlyCounts(WeeklyReport::class),
            'final_reports' => $fetchMonthlyCounts(FinalReport::class),
            'students' => $fetchMonthlyCounts(Student::class),
            'attachments' => $fetchMonthlyCounts(AttachmentStudent::class),
            'assessments' => $fetchMonthlyCounts(AttachmentAssessment::class),
        ];
    }

    /**
     * Get student map coordinates based on company town locations.
     */
    private function getStudentLocations(): Collection
    {
        return AttachmentStudent::with(['company.town', 'student.user'])
            ->whereHas('company.town', static function ($q) {
                $q->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->where('latitude', '!=', 0)
                    ->where('longitude', '!=', 0);
            })
            ->get()
            ->map(static function (AttachmentStudent $attachment) {
                $lat = (float) ($attachment->company->town->latitude ?? 0);
                $lng = (float) ($attachment->company->town->longitude ?? 0);

                if (abs($lat) > 90 || abs($lng) > 180 || $lat === 0.0 || $lng === 0.0) {
                    return null;
                }

                return [
                    'student_name' => $attachment->student->user->name ?? 'Unknown',
                    'company_name' => $attachment->company->name ?? 'Unknown',
                    'town' => $attachment->company->town->name ?? 'Unknown',
                    'lat' => $lat,
                    'lng' => $lng,
                    'reg_no' => $attachment->student->reg_no ?? 'N/A',
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Get distribution of students per department.
     */
    private function getDepartmentDistribution(): Collection
    {
        try {
            $students = Student::select('department_id')
                ->selectRaw('count(*) as total')
                ->groupBy('department_id')
                ->get();

            if ($students->isNotEmpty()) {
                return $students->map(static function ($item) {
                    return (object) [
                        'name' => 'Department ' . ($item->department_id ?? 'N/A'),
                        'total' => $item->total,
                    ];
                });
            }

            return collect([(object) ['name' => 'All Students', 'total' => Student::count()]]);
        } catch (Throwable $e) {
            Log::error('Error in department distribution: ' . $e->getMessage());

            return collect([(object) ['name' => 'All Students', 'total' => Student::count()]]);
        }
    }

    /**
     * Fetch top companies hosting attachment students.
     */
    private function getTopCompanies(): Collection
    {
        try {
            return DB::table('companies')
                ->leftJoin('attachment_students', 'companies.id', '=', 'attachment_students.company_id')
                ->leftJoin('locations', 'companies.town_id', '=', 'locations.id')
                ->select(
                    'companies.id',
                    'companies.name',
                    'locations.name as town_name',
                    DB::raw('COUNT(attachment_students.id) as students_count')
                )
                ->whereNotNull('attachment_students.company_id')
                ->groupBy('companies.id', 'companies.name', 'locations.name')
                ->orderByDesc('students_count')
                ->limit(5)
                ->get()
                ->map(static function ($company) {
                    $company->town_name = $company->town_name ?? 'Unknown Location';

                    return $company;
                });
        } catch (Throwable $e) {
            Log::error('Error in top companies: ' . $e->getMessage());

            return collect([]);
        }
    }

    /**
     * Get industrial supervisor participation metrics.
     */
    private function getSupervisorStats(): array
    {
        try {
            return [
                'total' => IndustrialSupervisor::count(),
                'active' => IndustrialSupervisor::whereNotNull('company_id')->count(),
                'with_students' => DB::table('industrial_supervisors')
                    ->join('attachment_students', 'industrial_supervisors.id', '=', 'attachment_students.industrial_supervisor_id')
                    ->distinct('industrial_supervisors.id')
                    ->count('industrial_supervisors.id'),
                'companies_count' => IndustrialSupervisor::whereNotNull('company_id')
                    ->distinct('company_id')
                    ->count('company_id'),
            ];
        } catch (Throwable $e) {
            Log::error('Error in supervisor stats: ' . $e->getMessage());

            return [
                'total' => IndustrialSupervisor::count(),
                'active' => 0,
                'with_students' => 0,
                'companies_count' => 0,
            ];
        }
    }

    /**
     * Get overall attachment status metrics.
     */
    private function getAttachmentStats(): array
    {
        return [
            'total' => AttachmentStudent::count(),
            'ongoing' => AttachmentStudent::where(static function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })->count(),
            'completed' => AttachmentStudent::whereNotNull('end_date')
                ->where('end_date', '<=', now())
                ->count(),
            'pending' => AttachmentStudent::whereNull('attachment_lecturer_id')->count(),
        ];
    }

    /**
     * Generate and download PDF report based on requested type.
     */
    public function generateReport(Request $request): Response
    {
        $validated = $request->validate([
            'report_type' => ['required', 'string', 'in:students,daily,weekly,final,companies,attachments,supervisors,assessments'],
            'date_range' => ['nullable', 'string'],
            'format' => ['required', 'string', 'in:pdf'],
        ]);

        $type = $validated['report_type'];
        $data = $this->getReportData($type);

        $pdf = Pdf::loadView('reports.' . $type, compact('data'));

        return $pdf->download($type . '-report-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Retrieve report dataset based on type parameter.
     */
    private function getReportData(string $type): Collection
    {
        return match ($type) {
            'students' => Student::with('user')->get(),
            'daily' => DailyReport::with('weeklyReport.attachmentStudent.student.user')->get(),
            'weekly' => WeeklyReport::with('attachmentStudent.student.user')->get(),
            'final' => FinalReport::with('attachmentStudent.student.user')->get(),
            'companies' => Company::with(['town', 'attachmentStudents'])->get(),
            'attachments' => AttachmentStudent::with(['student.user', 'company', 'lecturer.user', 'industrialSupervisor.user'])->get(),
            'supervisors' => IndustrialSupervisor::with(['user', 'company', 'attachmentStudents'])->get(),
            'assessments' => AttachmentAssessment::with('attachmentStudent.student.user')->get(),
            default => collect([]),
        };
    }
}