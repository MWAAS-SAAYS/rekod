<li class="px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-amber-400/90">
    Industry Host Operations
</li>

<li>
    <a href="{{ Route::has('industry.index') ? route('industry.index') : '#' }}" 
       class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg transition-all {{ request()->routeIs('industry.*') ? 'bg-amber-400 text-slate-950 font-bold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
        <i class="fas fa-building w-5 text-center mr-2 text-sm {{ request()->routeIs('industry.*') ? 'text-slate-950' : 'text-amber-400/80' }}"></i>
        <span>Company Profile</span>
    </a>
</li>

<li>
    <a href="#" class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg text-slate-300 hover:bg-slate-800/80 hover:text-white transition-all">
        <i class="fas fa-plus-circle w-5 text-center mr-2 text-sm text-amber-400/80"></i>
        <span>Post Attachment Slots</span>
    </a>
</li>