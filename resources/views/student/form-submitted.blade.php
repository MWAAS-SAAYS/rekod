@extends('layouts.my_app')

@section('title')
    Attachment Form Submitted
@endsection

@section('content')
<div class="min-h-[80vh] flex items-center justify-center bg-slate-50/50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl border border-slate-200/80 shadow-xl p-8 text-center relative overflow-hidden">
        
        <!-- SUBTLE BACKGROUND ACCENTS -->
        <div class="absolute -right-12 -top-12 w-40 h-40 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            
            <!-- SUCCESS ICON BADGE -->
            <div class="flex justify-center">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center border border-emerald-500/20 shadow-xs">
                    <i class="fas fa-circle-check text-3xl"></i>
                </div>
            </div>

            <!-- MESSAGE HEADING -->
            <div class="space-y-2">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-700 uppercase tracking-widest border border-amber-500/20">
                    <i class="fas fa-check"></i> Submission Confirmed
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Form Submitted Successfully
                </h2>
                <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                    Your external attachment details have been registered and saved to your academic record.
                </p>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="javascript:void(0)" 
                   data-id="{{ $attachment_student_id }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-xs font-bold rounded-xl bg-slate-900 text-amber-400 hover:bg-slate-800 transition-all shadow-md shadow-slate-900/10 open-student_attachment_details_modal-btn">
                    <i class="fas fa-eye mr-2 text-[10px]"></i> Preview Form
                </a>

                <a href="{{ route('student.portal') }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 text-xs font-bold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition-all">
                    <i class="fas fa-house mr-2 text-[10px]"></i> Go to Dashboard
                </a>
            </div>

        </div>

    </div>
</div>

@include('student.student_attachment_details_modal')
@endsection