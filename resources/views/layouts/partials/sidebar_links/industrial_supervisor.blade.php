<li class="px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-amber-400/90">
    Industry Supervision
</li>

<li>
    <a href="{{ Route::has('industrial_supervisor.index') ? route('industrial_supervisor.index') : '#' }}" 
       class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg transition-all {{ request()->routeIs('industrial_supervisor.*') ? 'bg-amber-400 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
        <i class="fas fa-users-gear w-5 text-center mr-2 text-sm {{ request()->routeIs('industrial_supervisor.*') ? 'text-slate-950' : 'text-amber-400/80' }}"></i>
        <span>Assigned Trainees</span>
    </a>
</li>

<li>
    <a href="#" class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all">
        <i class="fas fa-stamp w-5 text-center mr-2 text-sm text-amber-400/80"></i>
        <span>Logbook Approvals</span>
    </a>
</li>