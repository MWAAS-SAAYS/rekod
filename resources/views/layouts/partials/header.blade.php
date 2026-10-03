<nav class="bg-white border-b border-slate-200/80 fixed z-30 w-full top-0 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.03)]">
    <div class="px-3 py-2.5 lg:px-6">
        <div class="flex items-center justify-between">
            
            <!-- LEFT SECTION: MOBILE TOGGLE, NATIONAL BRANDING & ACTIVE SESSION -->
            <div class="flex items-center justify-start space-x-3">
                
                <!-- Mobile Hamburger Button (Preserved IDs for Windster JS) -->
                <button id="toggleSidebarMobile" aria-expanded="true" aria-controls="sidebar" 
                        class="lg:hidden text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 p-2 rounded-lg cursor-pointer focus:ring-2 focus:ring-slate-200 transition-colors">
                    <svg id="toggleSidebarMobileHamburger" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <svg id="toggleSidebarMobileClose" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>

                <!-- Sovereign State Emblem / Brand Logo -->
                <a href="{{ url('/') }}" class="flex items-center space-x-3 group mr-2">
                    <div class="w-9 h-9 rounded-lg bg-[#0b1329] border border-amber-500/40 flex items-center justify-center text-amber-400 shadow-sm group-hover:border-amber-400 transition-all">
                        <i class="fas fa-landmark text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-900 tracking-tight font-serif-header text-lg leading-none">
                            REKOD <span class="text-amber-600 font-sans text-[10px] uppercase tracking-widest font-bold align-top">Portal</span>
                        </span>
                        <span class="text-[9px] uppercase tracking-widest font-semibold text-slate-400 mt-0.5 hidden sm:inline">National Placement System</span>
                    </div>
                </a>

                <!-- ACTIVE ATTACHMENT SELECTION BADGE -->
                @if(session('attachment_name'))
                    <div class="hidden md:flex items-center">
                        <span class="text-slate-300 mx-2">|</span>
                        <a href="{{ route('attachment_selected.select') }}" 
                           class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-900 border border-amber-200/80 hover:bg-amber-100 transition-all shadow-2xs group"
                           title="Click to change active attachment cycle">
                            <span class="w-2 h-2 rounded-full bg-amber-500 mr-2 group-hover:animate-ping"></span>
                            <i class="fas fa-layer-group text-amber-700 mr-1.5 text-[11px]"></i>
                            <span>{{ session('attachment_name') }}</span>
                            <i class="fas fa-chevron-right text-[10px] text-amber-600 ml-2 group-hover:translate-x-0.5 transition-transform"></i>
                        </a>
                    </div>
                @endif

            </div>

            <!-- RIGHT SECTION: MOBILE SEARCH & OFFICIAL USER ROLE BADGE -->
            <div class="flex items-center space-x-3">

                <!-- Mobile Search Trigger (Preserved ID) -->
                <button id="toggleSidebarMobileSearch" type="button" 
                        class="lg:hidden text-slate-500 hover:text-slate-900 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                    <span class="sr-only">Search</span>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                </button>

                <!-- Contextual Attachment Selector Pill for Mobile Screens -->
                @if(session('attachment_name'))
                    <a href="{{ route('attachment_selected.select') }}" class="md:hidden text-xs bg-amber-50 text-amber-800 border border-amber-200 px-2 py-1 rounded font-medium">
                        <i class="fas fa-layer-group mr-1"></i> Cycle
                    </a>
                @endif

                <!-- Official Role Status Pill -->
                <div class="flex items-center space-x-2 bg-slate-900 text-slate-100 px-3 py-1.5 rounded-md border border-slate-800 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.8)]"></span>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-200">
                        @auth
                            {{ Auth::user()->role }} <span class="text-amber-400 text-[10px] font-normal">Portal</span>
                        @else
                            Guest <span class="text-slate-400 text-[10px] font-normal">Portal</span>
                        @endauth
                    </span>
                </div>

            </div>

        </div>
    </div>
</nav>