@extends('layouts.my_app')

@section('title', 'Final Report Submission')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- PAGE HEADER BANNER -->
        <div class="bg-[#0b1329] rounded-2xl p-6 sm:p-8 text-white border border-amber-500/20 shadow-xl relative overflow-hidden text-center sm:text-left">
            <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-widest">
                    <i class="fas fa-file-pdf"></i> Academic Deliverable
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-amber-400 uppercase tracking-wider">
                    Final Attachment Report
                </h1>
                <p class="text-xs text-slate-300 max-w-2xl">
                    Upload your comprehensive industrial attachment report in PDF format. This document is required for final academic evaluation and grading.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            
            {{-- State 1: Student has already submitted --}}
            @if(isset($final_report) && $final_report)
                <div class="p-8 text-center space-y-6">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center mx-auto text-2xl border border-emerald-500/20">
                        <i class="fas fa-circle-check"></i>
                    </div>

                    <div class="space-y-1">
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">Report Successfully Submitted</h2>
                        <p class="text-xs text-slate-500">Your final attachment report has been logged and queued for faculty review.</p>
                    </div>

                    <!-- Submission Summary Card -->
                    <div class="max-w-md mx-auto bg-slate-50 rounded-xl border border-slate-200 p-5 text-left space-y-4">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Submitted Title</span>
                            <span class="text-xs font-bold text-slate-800 block">{{ $final_report->title }}</span>
                        </div>

                        <div class="pt-3 border-t border-slate-200/80 flex items-center justify-between gap-4">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Submission Date</span>
                                <span class="text-xs font-semibold text-slate-700">
                                    {{ $final_report->created_at->format('D, d M Y - H:i') }}
                                </span>
                            </div>

                            <a href="{{ asset('storage/' . $final_report->file_path) }}" 
                               target="_blank" 
                               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-slate-900 text-amber-400 hover:bg-slate-800 text-xs font-bold transition-all shadow-sm">
                                <i class="fas fa-eye mr-2 text-[10px]"></i> View Report
                            </a>
                        </div>
                    </div>
                </div>

            {{-- State 2: Student has NOT submitted yet --}}
            @else
                <div class="p-6 sm:p-8 space-y-6">
                    
                    <!-- One-Time Submission Notice -->
                    <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-start space-x-3 text-amber-900 text-xs">
                        <i class="fas fa-triangle-exclamation text-amber-600 text-sm mt-0.5 shrink-0"></i>
                        <p class="leading-relaxed">
                            <strong class="font-bold text-amber-950">Important Notice:</strong> Final report submission is permanent and can only be done <strong>once</strong>. Please verify that you are uploading the final, corrected PDF version before submitting.
                        </p>
                    </div>

                    <form action="{{ route('student.final-report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        
                        <!-- Report Title -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Report Title <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-heading absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" 
                                       name="title" 
                                       required 
                                       placeholder="e.g. Industrial Attachment Report at XYZ Company" 
                                       class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                            </div>
                        </div>

                        <!-- Executive Summary / Content -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Executive Summary / Brief Overview <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="content" 
                                      rows="4" 
                                      required 
                                      placeholder="Briefly describe the core activities, key achievements, and summary of your attachment experience..." 
                                      class="w-full p-3 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all resize-none"></textarea>
                        </div>

                        <!-- PDF Upload Container -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Upload Document (PDF Only) <span class="text-rose-500">*</span>
                            </label>
                            
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:border-amber-500/50 transition-colors bg-slate-50/50 relative">
                                <i class="fas fa-file-pdf text-3xl text-slate-400 mb-2"></i>
                                
                                <div class="space-y-2">
                                    <input id="file-upload" 
                                           name="final_report_file" 
                                           type="file" 
                                           required 
                                           accept=".pdf" 
                                           class="block w-full text-xs text-slate-500 
                                                  file:mr-4 file:py-2 file:px-4 
                                                  file:rounded-xl file:border-0 
                                                  file:text-xs file:font-bold 
                                                  file:bg-slate-900 file:text-amber-400 
                                                  hover:file:bg-slate-800 cursor-pointer">
                                    <p class="text-[11px] text-slate-400">Accepted format: PDF • Max allowed size: 10MB</p>
                                </div>
                            </div>

                            @error('final_report_file')
                                <p class="text-[11px] font-semibold text-rose-500 flex items-center gap-1 mt-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" 
                                    onclick="return confirm('Are you sure? You cannot edit or re-upload this report after submission.')" 
                                    class="w-full inline-flex items-center justify-center py-3 px-4 rounded-xl bg-slate-900 text-amber-400 hover:bg-slate-800 text-xs font-bold transition-all shadow-md shadow-slate-900/10">
                                <i class="fas fa-cloud-arrow-up mr-2"></i> Upload and Submit Final Report
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>

    </div>
</div>
@endsection