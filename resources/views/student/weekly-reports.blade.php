@extends('layouts.my_app')

@section('title', 'Weekly Reports')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-8">

        <!-- HEADER BANNER -->
        <div class="bg-[#0b1329] rounded-2xl p-6 sm:p-8 text-white border border-amber-500/20 shadow-xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-widest">
                    <i class="fas fa-calendar-check"></i> Industrial Attachment Progress
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-amber-400 uppercase tracking-wider">
                    Submit Weekly Attachment Report
                </h1>
                <p class="text-xs text-slate-300 max-w-2xl">
                    Log and submit your weekly attachment activities and progress summary. Supervisor and lecturer assessments, feedback, and approvals will be tracked below.
                </p>
            </div>
        </div>

        <!-- SUCCESS ALERT -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 flex items-center gap-3 text-xs font-bold shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0">
                    <i class="fas fa-circle-check text-sm"></i>
                </div>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- VALIDATION ERRORS ALERT -->
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-700 space-y-2 text-xs shadow-xs">
                <div class="flex items-center gap-2 font-bold">
                    <i class="fas fa-triangle-exclamation text-rose-600"></i>
                    <span>Please correct the errors listed below:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 pl-2 text-rose-600 font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- REPORT SUBMISSION FORM CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-sm font-black uppercase text-slate-900 tracking-wider flex items-center gap-2">
                    <i class="fas fa-pen-to-square text-amber-500"></i> Weekly Progress Form
                </h2>
            </div>

            <form method="POST" action="{{ route('student.weekly-reports.store') }}" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Week Number -->
                    <div class="space-y-1.5">
                        <label for="week_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Week Number <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-list-ol absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <select name="week_id" id="week_id" required
                                class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all disabled:bg-slate-100 disabled:text-slate-500 cursor-pointer"
                                @if($user_role !== 'student') disabled @endif>
                                <option value="">Select Week</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('week_id') == $i ? 'selected' : '' }}>Week {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Week Start Date -->
                    <div class="space-y-1.5">
                        <label for="week_start_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Week Start Date <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-calendar-day absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="date" name="week_start_date" id="week_start_date" value="{{ old('week_start_date') }}" required
                                class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all disabled:bg-slate-100 disabled:text-slate-500"
                                @if($user_role !== 'student') disabled @endif>
                        </div>
                    </div>

                    <!-- Week End Date -->
                    <div class="space-y-1.5">
                        <label for="week_end_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Week End Date <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fas fa-calendar-check absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="date" name="week_end_date" id="week_end_date" value="{{ old('week_end_date') }}" required
                                class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all disabled:bg-slate-100 disabled:text-slate-500"
                                @if($user_role !== 'student') disabled @endif>
                        </div>
                    </div>
                </div>

                <!-- Report Description -->
                <div class="space-y-1.5">
                    <label for="weekly_report" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Report Description <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="weekly_report" id="weekly_report" rows="5" required
                        placeholder="Write what you accomplished this week..."
                        class="w-full p-3.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all resize-none disabled:bg-slate-100 disabled:text-slate-500"
                        @if($user_role !== 'student') disabled @endif>{{ old('weekly_report') }}</textarea>
                </div>

                {{-- Approval Checkbox for Industrial Supervisor --}}
                @if($user_role === 'industrial_supervisor')
                    <div class="p-4 rounded-xl bg-amber-500/5 border border-amber-500/20 flex items-center space-x-3">
                        <input type="checkbox" name="is_approved" id="is_approved" value="1" 
                            class="w-4 h-4 rounded text-amber-500 focus:ring-amber-500/20 border-slate-300 cursor-pointer"
                            {{ old('is_approved') ? 'checked' : '' }}>
                        <label for="is_approved" class="text-xs font-bold text-slate-800 cursor-pointer">
                            Approve Weekly Report for Academic Assessment
                        </label>
                    </div>
                @endif

                {{-- Submit Button --}}
                @if(in_array($user_role, ['student', 'industrial_supervisor', 'lecturer']))
                    <div class="flex justify-end pt-2">
                        <button type="submit" 
                            class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-900 text-amber-400 hover:bg-slate-800 text-xs font-bold transition-all shadow-md shadow-slate-900/10 cursor-pointer">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Weekly Report
                        </button>
                    </div>
                @endif
            </form>
        </div>

        <!-- SUBMITTED REPORTS TABLE CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase text-slate-900 tracking-wider flex items-center gap-2">
                    <i class="fas fa-clock-rotate-left text-amber-500"></i> Submitted Reports History
                </h2>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#0b1329] text-amber-400 text-[10px] uppercase font-bold tracking-wider">
                            <th class="p-3 border-b border-amber-500/20">Week</th>
                            <th class="p-3 border-b border-amber-500/20">Start Date</th>
                            <th class="p-3 border-b border-amber-500/20">End Date</th>
                            <th class="p-3 border-b border-amber-500/20">Weekly Report</th>
                            <th class="p-3 border-b border-amber-500/20">Supervisor Comment</th>
                            <th class="p-3 border-b border-amber-500/20">Lecturer Comment</th>
                            <th class="p-3 border-b border-amber-500/20 text-center">Approved</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($weeklyReports as $report)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-3 font-bold text-slate-900 whitespace-nowrap">Week {{ $report->week_id }}</td>
                                <td class="p-3 font-medium whitespace-nowrap text-slate-600">
                                    {{ optional(\Carbon\Carbon::parse($report->week_start_date))->format('Y-m-d') }}
                                </td>
                                <td class="p-3 font-medium whitespace-nowrap text-slate-600">
                                    {{ optional(\Carbon\Carbon::parse($report->week_end_date))->format('Y-m-d') }}
                                </td>
                                <td class="p-3 text-slate-800 max-w-xs truncate">
                                    {{ \Illuminate\Support\Str::limit($report->weekly_report, 50) }}
                                </td>
                                <td class="p-3 text-slate-600 italic">
                                    {{ $report->industrial_supervisor_comment ?? '-' }}
                                </td>
                                <td class="p-3 text-slate-600 italic">
                                    {{ $report->lecturer_comment ?? '-' }}
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    @if($report->is_approved)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fas fa-check text-[9px]"></i> Yes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i class="fas fa-xmark text-[9px]"></i> No
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400 bg-slate-50/50">
                                    <div class="flex flex-col items-center justify-center space-y-1">
                                        <i class="fas fa-folder-open text-2xl text-slate-300"></i>
                                        <span class="text-xs font-semibold">No reports submitted yet.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection