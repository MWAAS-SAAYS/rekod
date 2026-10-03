@extends('layouts.my_app')

@section('title')
    Student Logbook
@endsection

@section('content')
<div class="min-h-screen bg-slate-50/50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto space-y-6">

        <!-- PAGE HEADER BANNER -->
        <div class="bg-[#0b1329] rounded-2xl p-6 sm:p-8 text-white border border-amber-500/20 shadow-xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-widest">
                    <i class="fas fa-book-open"></i> Industrial Attachment Logbook
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-amber-400 uppercase tracking-wider">
                    Weekly Logbook Entry
                </h1>
                <p class="text-xs text-slate-300 max-w-xl">
                    Document your daily tasks, practical engineering/technical activities, challenges faced, and new skills acquired during your attachment.
                </p>
            </div>
        </div>

        <!-- FORM CONTAINER -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            <form action="{{ route('logbook.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Week Field -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Attachment Week <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fas fa-calendar-week absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" 
                               name="week" 
                               required 
                               placeholder="e.g. Week 1 or Week 12" 
                               class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                    </div>
                </div>

                <!-- Activities Field -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Tasks & Key Activities Executed <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="activities" 
                              rows="4" 
                              required 
                              placeholder="Detail the practical tasks, systems installed/serviced, wiring, testing, or field assignments undertaken during this week..." 
                              class="w-full p-3 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all resize-none"></textarea>
                </div>

                <!-- Challenges Field -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Challenges & Obstacles Encountered
                    </label>
                    <textarea name="challenges" 
                              rows="3" 
                              placeholder="Describe any technical bugs, hardware issues, missing tools, or logistical obstacles faced and how you handled them..." 
                              class="w-full p-3 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all resize-none"></textarea>
                </div>

                <!-- Skills Gained Field -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Skills & Competencies Acquired
                    </label>
                    <textarea name="skills" 
                              rows="3" 
                              placeholder="Highlight new technical knowledge, software/hardware proficiency, safety protocols, or troubleshooting techniques learned..." 
                              class="w-full p-3 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all resize-none"></textarea>
                </div>

                <!-- Action Button -->
                <div class="pt-2 flex items-center justify-end">
                    <button type="submit" 
                            class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-slate-900 text-amber-400 hover:bg-slate-800 text-xs font-bold transition-all shadow-md shadow-slate-900/10 cursor-pointer">
                        <i class="fas fa-floppy-disk mr-2"></i> Save Entry
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection