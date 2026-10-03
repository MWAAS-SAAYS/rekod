<x-app-layout>
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- Header & PDF Export Trigger --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Attachment Management Dashboard</h1>
                    <p class="text-sm text-gray-500">Overview of student placements, supervision, and report submissions for {{ $currentYear }}</p>
                </div>
                
                {{-- Report Generator Form --}}
                <form action="{{ route('dashboard.generate-report') }}" method="POST" class="flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200 shadow-sm">
                    @csrf
                    <select name="report_type" class="text-sm rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="students">Students Report</option>
                        <option value="daily">Daily Reports</option>
                        <option value="weekly">Weekly Reports</option>
                        <option value="final">Final Reports</option>
                        <option value="companies">Companies</option>
                        <option value="attachments">Attachments</option>
                        <option value="supervisors">Supervisors</option>
                        <option value="assessments">Assessments</option>
                    </select>
                    <input type="hidden" name="format" value="pdf">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm transition">
                        Export PDF
                    </button>
                </form>
            </div>

            {{-- Stat Cards Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Students</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($totalStudents) }}</p>
                    <span class="text-xs text-indigo-600 font-medium mt-2 inline-block">{{ $attachmentStats['ongoing'] }} Ongoing Attachments</span>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Partner Companies</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($totalCompanies) }}</p>
                    <span class="text-xs text-gray-500 mt-2 inline-block">Across {{ $totalTowns }} Locations</span>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Weekly Reports</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($totalWeeklyReports) }}</p>
                    <span class="text-xs text-emerald-600 font-medium mt-2 inline-block">+{{ $weeklyReportsThisWeek }} submitted this week</span>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Final Reports</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($totalFinalReports) }}</p>
                    <span class="text-xs text-gray-500 mt-2 inline-block">{{ $totalAssessments }} Total Assessments</span>
                </div>
            </div>

            {{-- Charts & Visualizations --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Monthly Activity Chart --}}
                <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Submission Trends ({{ $currentYear }})</h2>
                    <canvas id="monthlyTrendsChart" class="w-full h-64"></canvas>
                </div>

                {{-- Attachment Status Breakdown --}}
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 mb-4">Attachment Status</h2>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm font-medium mb-1">
                                    <span class="text-gray-600">Ongoing</span>
                                    <span class="text-gray-900">{{ $attachmentStats['ongoing'] }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $attachmentStats['total'] > 0 ? ($attachmentStats['ongoing'] / $attachmentStats['total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm font-medium mb-1">
                                    <span class="text-gray-600">Completed</span>
                                    <span class="text-gray-900">{{ $attachmentStats['completed'] }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $attachmentStats['total'] > 0 ? ($attachmentStats['completed'] / $attachmentStats['total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm font-medium mb-1">
                                    <span class="text-gray-600">Pending Academic Assignment</span>
                                    <span class="text-gray-900">{{ $attachmentStats['pending'] }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $attachmentStats['total'] > 0 ? ($attachmentStats['pending'] / $attachmentStats['total']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100 mt-6">
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Supervisor Coverage</p>
                        <p class="text-sm font-medium text-gray-700 mt-1">
                            {{ $supervisorStats['with_students'] }} of {{ $supervisorStats['total'] }} supervisors actively assigned to students.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Activity Data Grids --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Recent Weekly Submissions --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100">
                        <h3 class="text-md font-bold text-gray-800">Recent Weekly Reports</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($recentWeeklyReports as $report)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $report->student_name }}</p>
                                    <p class="text-xs text-gray-500">Reg: {{ $report->reg_no }}</p>
                                </div>
                                <span class="text-xs font-medium text-gray-400">{{ \Carbon\Carbon::parse($report->created_at)->diffForHumans() }}</span>
                            </div>
                        @empty
                            <p class="p-4 text-sm text-gray-500 text-center">No weekly reports submitted yet.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Top Partner Companies --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100">
                        <h3 class="text-md font-bold text-gray-800">Top Host Companies</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($topCompanies as $company)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $company->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $company->town_name }}</p>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded-full">
                                    {{ $company->students_count }} {{ Str::plural('Student', $company->students_count) }}
                                </span>
                            </div>
                        @empty
                            <p class="p-4 text-sm text-gray-500 text-center">No company data recorded.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Chart Initialization Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');
            const monthlyTrends = @json($monthlyTrends);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthlyTrends.labels,
                    datasets: [
                        {
                            label: 'Daily Reports',
                            data: monthlyTrends.daily_reports,
                            borderColor: '#3b82f6',
                            tension: 0.3,
                            fill: false
                        },
                        {
                            label: 'Weekly Reports',
                            data: monthlyTrends.weekly_reports,
                            borderColor: '#10b981',
                            tension: 0.3,
                            fill: false
                        },
                        {
                            label: 'Final Reports',
                            data: monthlyTrends.final_reports,
                            borderColor: '#8b5cf6',
                            tension: 0.3,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    </script>
</x-app-layout>