<aside id="sidebar" class="fixed hidden z-20 h-full top-0 left-0 pt-16 flex-shrink-0 flex flex-col w-64 transition-width duration-75 lg:flex bg-[#0b1329] border-r border-amber-500/20 text-slate-300" aria-label="Sidebar">
    <div class="relative flex-1 flex flex-col min-h-0 pt-0">
        <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-800">
            <div class="flex-1 px-3 space-y-1 divide-y divide-slate-800/80">
                
                <div class="flex flex-col justify-between" style="min-height: calc(100vh - 9rem);">
                    
                    <!-- MAIN NAVIGATION LIST -->
                    <ul class="space-y-1">
                        @if(Auth::check() && Auth::user()->role)
                            <li class="px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-amber-400/90">
                                Operations Menu
                            </li>

                            {{-- Dynamic Role-Based Links (Admin, Company, Supervisor, Lecturer, Student) --}}
                            @include('layouts.partials.sidebar_links.' . Auth::user()->role)

                            {{-- Attachment Selection Link --}}
                            <li class="pt-2">
                                <a href="{{ route('attachment_selected.select') }}" 
                                   class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg transition-all border border-amber-500/20 bg-amber-500/10 text-amber-300 hover:bg-amber-500/20 shadow-xs group">
                                    <i class="fas fa-layer-group w-5 text-center mr-2 text-sm text-amber-400 group-hover:rotate-12 transition-transform"></i>
                                    <span class="truncate">Attachment</span>
                                </a>
                            </li>
                        @else
                            <li class="px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-amber-400">
                                Public Portal
                            </li>
                            <li>
                                <a href="{{ route('login') }}" class="flex items-center px-3 py-2.5 text-xs font-semibold rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all">
                                    <i class="fas fa-right-to-bracket w-5 text-center mr-2 text-amber-400"></i>
                                    <span>Sign In</span>
                                </a>
                            </li>
                        @endif
                    </ul>

                    <!-- BOTTOM LOGOUT SECTION -->
                    @if(Auth::check())
                        <div class="pt-4 border-t border-slate-800/80 mt-auto mb-2">
                            <a href="{{ route('logout') }}" 
                               onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();"
                               class="flex items-center px-3 py-2.5 text-xs font-semibold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 rounded-lg transition-all group">
                                <i class="fas fa-power-off w-5 text-center mr-2 text-sm text-rose-400 group-hover:scale-110 transition-transform"></i>
                                <span>Log Out</span>
                            </a>
                            <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    @endif

                </div>

            </div>
        </div>

        <!-- FOOTER EMBLEM BADGE -->
        <div class="p-3 border-t border-slate-800/80 bg-[#070d1c] text-center">
            <div class="bg-slate-900/90 border border-amber-500/20 rounded-lg p-2.5">
                <div class="text-[10px] font-bold uppercase tracking-widest text-amber-400">REKOD Executive</div>
                <div class="text-[9px] text-slate-500 mt-0.5">Republic Placement System</div>
            </div>
        </div>
    </div>
</aside>