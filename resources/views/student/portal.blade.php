@extends('layouts.my_app')

@section('title')
    Student Portal Dashboard
@endsection

@section('content')
<div class="min-h-screen bg-slate-50/50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- WELCOME BANNER -->
        <div class="bg-[#0b1329] rounded-2xl p-6 sm:p-8 text-white border border-amber-500/20 shadow-xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-widest">
                        <i class="fas fa-graduation-cap"></i> Industrial Attachment Portal
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-wide">
                        Welcome to Your Student Portal
                    </h1>
                    <p class="text-xs text-slate-300 max-w-xl">
                        Manage your industrial placement, track weekly logbook submissions, explore partner companies, and upload your final attachment report.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 shrink-0">
                    <a href="{{ route('student.logbook') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all shadow-md shadow-amber-500/10">
                        <i class="fas fa-pen-to-square mr-2"></i> Logbook Entry
                    </a>
                </div>
            </div>
        </div>

        <!-- QUICK STATUS OVERVIEW CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Registration Status -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Attachment Form</span>
                    <span class="text-xs font-black text-emerald-600 flex items-center gap-1.5">
                        <i class="fas fa-circle-check text-[10px]"></i> Registered
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shrink-0 border border-emerald-100">
                    <i class="fas fa-file-signature"></i>
                </div>
            </div>

            <!-- Card 2: Logbook Activity -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Logbook Progress</span>
                    <span class="text-xs font-black text-slate-800">
                        Weekly Log Active
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm shrink-0 border border-amber-100">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>

            <!-- Card 3: Partner Companies -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Company Partners</span>
                    <span class="text-xs font-black text-slate-800">
                        Directory Available
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm shrink-0 border border-sky-100">
                    <i class="fas fa-building"></i>
                </div>
            </div>

            <!-- Card 4: Final Report Status -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Final Deliverable</span>
                    <span class="text-xs font-black text-slate-500">
                        Pending Submission
                    </span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-sm shrink-0 border border-slate-200">
                    <i class="fas fa-file-pdf"></i>
                </div>
            </div>

        </div>

        <!-- PORTAL MODULES & QUICK ACTIONS GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Card 1: Attachment Registration -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between hover:shadow-md hover:border-slate-300 transition-all">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-[#0b1329] text-amber-400 flex items-center justify-center text-lg font-bold border border-amber-500/20 shadow-xs">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Attachment Registration</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Register your host organization, industry supervisor contact details, and location for site evaluation routing.
                        </p>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 mt-5">
                    <a href="{{ route('student.attachment-form') }}" class="inline-flex items-center text-xs font-bold text-slate-900 hover:text-amber-600 transition-colors">
                        Register / View Details <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card 2: Weekly Logbook Entry -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between hover:shadow-md hover:border-slate-300 transition-all">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-[#0b1329] text-amber-400 flex items-center justify-center text-lg font-bold border border-amber-500/20 shadow-xs">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Weekly Logbook</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Log weekly technical activities, hands-on field tasks, key technical competencies gained, and obstacles encountered.
                        </p>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 mt-5">
                    <a href="{{ route('student.logbook') }}" class="inline-flex items-center text-xs font-bold text-slate-900 hover:text-amber-600 transition-colors">
                        Add New Log Entry <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card 3: Partner Companies Directory -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between hover:shadow-md hover:border-slate-300 transition-all">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-[#0b1329] text-amber-400 flex items-center justify-center text-lg font-bold border border-amber-500/20 shadow-xs">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Approved Companies</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Browse verified attachment placement partners, search by county or town, and view available supervisor contacts.
                        </p>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 mt-5">
                    <a href="{{ route('student.companies') }}" class="inline-flex items-center text-xs font-bold text-slate-900 hover:text-amber-600 transition-colors">
                        Explore Directory <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card 4: Final Report Submission -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between hover:shadow-md hover:border-slate-300 transition-all">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-[#0b1329] text-amber-400 flex items-center justify-center text-lg font-bold border border-amber-500/20 shadow-xs">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Final Attachment Report</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Upload your completed, single PDF final attachment report at the conclusion of your industrial training period.
                        </p>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 mt-5">
                    <a href="{{ route('student.final-report') }}" class="inline-flex items-center text-xs font-bold text-slate-900 hover:text-amber-600 transition-colors">
                        Submit Final Report <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Card 5: Important Instructions Notice -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between md:col-span-2 lg:col-span-2">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-700 flex items-center justify-center text-sm font-bold border border-amber-500/20">
                            <i class="fas fa-circle-info"></i>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] uppercase tracking-wider">
                            Guidelines
                        </span>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-slate-900">Attachment Instructions & Evaluation</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Keep your weekly logbook entries complete and accurate. Academic supervisors will reference these logs during field assessment visits. Ensure your organization details remain updated in the registration form.
                        </p>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 mt-5 text-[11px] text-slate-400 flex items-center gap-2">
                    <i class="fas fa-shield-halved text-amber-500"></i>
                    <span>For technical support or placement changes, contact the department attachment coordinator.</span>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection