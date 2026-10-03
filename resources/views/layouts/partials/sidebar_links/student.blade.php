<li class="px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-amber-400/90">
    Student Operations
</li>

<!-- Attachment Form -->
<li>
    <a href="{{ route('student.attachment-form') }}" 
       class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg transition-all {{ request()->routeIs('student.attachment-form*') ? 'bg-amber-400 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
        <i class="fas fa-file-signature w-5 text-center mr-2 text-sm {{ request()->routeIs('student.attachment-form*') ? 'text-slate-950' : 'text-amber-400/80' }}"></i>
        <span>Attachment Form</span>
    </a>
</li>

<!-- Daily Activities -->
<li>
    <a href="{{ route('student.daily_activities.index') }}" 
       class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg transition-all {{ request()->routeIs('student.daily_activities.*') ? 'bg-amber-400 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
        <i class="fas fa-book-journal w-5 text-center mr-2 text-sm {{ request()->routeIs('student.daily_activities.*') ? 'text-slate-950' : 'text-amber-400/80' }}"></i>
        <span>Daily Activities</span>
    </a>
</li>

<!-- Weekly Reports -->
<li>
    <a href="{{ route('student.weekly-reports') }}" 
       class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg transition-all {{ request()->routeIs('student.weekly-reports*') ? 'bg-amber-400 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
        <i class="fas fa-calendar-week w-5 text-center mr-2 text-sm {{ request()->routeIs('student.weekly-reports*') ? 'text-slate-950' : 'text-amber-400/80' }}"></i>
        <span>Weekly Reports</span>
    </a>
</li>

<!-- Final Report -->
<li>
    <a href="{{ route('student.final-report') }}" 
       class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg transition-all {{ request()->routeIs('student.final-report*') ? 'bg-amber-400 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
        <i class="fas fa-file-export w-5 text-center mr-2 text-sm {{ request()->routeIs('student.final-report*') ? 'text-slate-950' : 'text-amber-400/80' }}"></i>
        <span>Final Report</span>
    </a>
</li>

<!-- Companies -->
<li>
    <a href="{{ route('companies') }}" 
       class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg transition-all {{ request()->routeIs('companies*') ? 'bg-amber-400 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
        <i class="fas fa-building w-5 text-center mr-2 text-sm {{ request()->routeIs('companies*') ? 'text-slate-950' : 'text-amber-400/80' }}"></i>
        <span>Companies</span>
    </a>
</li>

<!-- Opportunities -->
<li>
    <a href="{{ route('student.opportunities.index') }}" 
       class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg transition-all {{ request()->routeIs('student.opportunities.*') ? 'bg-amber-400 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
        <i class="fas fa-compass w-5 text-center mr-2 text-sm {{ request()->routeIs('student.opportunities.*') ? 'text-slate-950' : 'text-amber-400/80' }}"></i>
        <span>Opportunities</span>
    </a>
</li>