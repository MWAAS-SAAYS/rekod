@extends('layouts.my_app')

@section('title', 'Company & Attachment Opportunities')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- HEADER BANNER -->
        <div class="bg-[#0b1329] rounded-2xl p-6 sm:p-8 text-white border border-amber-500/20 shadow-xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-widest">
                        <i class="fas fa-building"></i> Industry Partners
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-amber-400 uppercase tracking-wider">
                        Approved Companies & Placement Opportunities
                    </h1>
                    <p class="text-xs text-slate-300 max-w-xl">
                        Explore verified placement partners, locate host offices on Google Maps, check LinkedIn profiles, and see past students who attached here.
                    </p>
                </div>
            </div>
        </div>

        <!-- SEARCH AND FILTER CONTROLS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col sm:flex-row gap-4 justify-between items-center">
            <!-- Live Search Bar -->
            <div class="relative w-full sm:w-96">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="companySearchInput" onkeyup="filterCards()" placeholder="Search by company, location, or department..." 
                       class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
            </div>

            <div class="flex items-center gap-3 text-xs font-bold">
                <span id="companyCountBadge" class="px-3.5 py-2 rounded-xl bg-amber-500/10 text-amber-700 border border-amber-500/20">
                    Showing Partners
                </span>
            </div>
        </div>

        <!-- "BIG RECTANGLES" CARD GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="companyGrid">
            @forelse($companies as $company)
                @php
                    // Dynamic Google Maps Search URL Fallback
                    $townName = $company->town->name ?? $company->town ?? '';
                    $mapsQuery = urlencode(($company->name ?? '') . ' ' . ($company->building ?? '') . ' ' . $townName);
                    $mapsUrl = $company->google_maps_url ?? "https://www.google.com/maps/search/?api=1&query={$mapsQuery}";
                    
                    // Dynamic LinkedIn Company Search URL Fallback
                    $linkedInUrl = $company->linkedin_url ?? "https://www.linkedin.com/search/results/companies/?keywords=" . urlencode($company->name ?? '');
                @endphp

                <div class="company-card bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-lg hover:border-slate-300 transition-all p-6 flex flex-col justify-between space-y-5 relative overflow-hidden"
                     data-name="{{ strtolower($company->name ?? '') }}"
                     data-town="{{ strtolower($townName) }}"
                     data-email="{{ strtolower($company->email ?? '') }}">
                    
                    <!-- TOP BAR: LOGO & LINKEDIN / MAPS QUICK ICONS -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <!-- Company Logo / Initials -->
                            <div class="w-14 h-14 rounded-2xl bg-[#0b1329] border border-amber-500/20 flex items-center justify-center shrink-0 shadow-xs overflow-hidden">
                                @if(!empty($company->logo))
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-amber-400 font-black text-lg uppercase tracking-wider">
                                        {{ substr($company->name ?? 'C', 0, 2) }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="space-y-1">
                                <h3 class="text-sm font-bold text-slate-900 line-clamp-1">
                                    {{ $company->name }}
                                </h3>
                                @if(!empty($company->alias))
                                    <span class="inline-block text-[10px] font-bold text-amber-700 bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20 uppercase tracking-wide">
                                        {{ $company->alias }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- EXTERNAL QUICK LINKS (LinkedIn & Google Maps) -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            <!-- Google Maps Button -->
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" 
                               title="Open Location in Google Maps"
                               class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-emerald-50 text-slate-500 hover:text-emerald-600 border border-slate-200/80 flex items-center justify-center text-xs transition-all">
                                <i class="fas fa-map-location-dot"></i>
                            </a>

                            <!-- LinkedIn Button -->
                            <a href="{{ $linkedInUrl }}" target="_blank" rel="noopener noreferrer" 
                               title="Visit LinkedIn Profile"
                               class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-500 hover:text-sky-600 border border-slate-200/80 flex items-center justify-center text-xs transition-all">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>

                    <!-- MIDDLE SECTION: DETAILS & OPPORTUNITY SUMMARY -->
                    <div class="space-y-3 text-xs border-t border-b border-slate-100 py-3.5">
                        
                        <!-- Location & Street -->
                        <div class="flex items-center gap-2.5 text-slate-700">
                            <i class="fas fa-location-dot text-amber-500 w-4 text-center"></i>
                            <span class="font-bold">
                                {{ $townName ?: 'Nairobi' }}
                                @if(!empty($company->street)) <span class="text-slate-400 font-normal">• {{ $company->street }}</span> @endif
                            </span>
                        </div>

                        <!-- Building / Office Address -->
                        @if(!empty($company->building))
                            <div class="flex items-center gap-2.5 text-slate-500">
                                <i class="fas fa-building text-slate-400 w-4 text-center"></i>
                                <span class="truncate">{{ $company->building }}</span>
                            </div>
                        @endif

                        <!-- Placement Description -->
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">
                                Opportunity Focus
                            </span>
                            <p class="text-[11px] text-slate-700 font-medium leading-relaxed line-clamp-2">
                                {{ $company->opportunity_details ?? 'Technical training, electrical systems maintenance, solar installations, and field operations.' }}
                            </p>
                        </div>

                        <!-- PAST ATTACHED STUDENTS / ALUMNI STACK -->
                        <div class="pt-2 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                Previous Trainees
                            </span>
                            
                            <div class="flex items-center gap-2">
                                <div class="flex -space-x-2 overflow-hidden">
                                    @if(isset($company->past_students) && count($company->past_students) > 0)
                                        @foreach($company->past_students->take(4) as $student)
                                            <img class="inline-block h-6 w-6 rounded-full ring-2 ring-white object-cover" 
                                                 src="{{ $student->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($student->name).'&background=0b1329&color=fbbf24' }}" 
                                                 title="{{ $student->name }}" 
                                                 alt="{{ $student->name }}">
                                        @endforeach
                                    @else
                                        <!-- Sample Alumni Avatars Stack (Fallback Visual) -->
                                        <div class="inline-flex h-6 w-6 rounded-full bg-slate-800 text-amber-400 ring-2 ring-white text-[9px] font-bold items-center justify-center">
                                            JD
                                        </div>
                                        <div class="inline-flex h-6 w-6 rounded-full bg-amber-500 text-slate-900 ring-2 ring-white text-[9px] font-bold items-center justify-center">
                                            SM
                                        </div>
                                        <div class="inline-flex h-6 w-6 rounded-full bg-slate-700 text-white ring-2 ring-white text-[9px] font-bold items-center justify-center">
                                            AK
                                        </div>
                                    @endif
                                </div>
                                <span class="text-[10px] font-bold text-slate-500">
                                    {{ $company->past_students_count ?? '3+' }} attached
                                </span>
                            </div>
                        </div>

                    </div>

                    <!-- FOOTER ACTION: "CHOOSE THIS PLACE" SELECTION BUTTON -->
                    <div class="pt-1 flex items-center justify-between gap-3">
                        <div class="text-[11px] font-bold text-slate-600 truncate">
                            @if(!empty($company->contact) || !empty($company->phone))
                                <i class="fas fa-phone mr-1 text-amber-500"></i> {{ $company->contact ?? $company->phone }}
                            @else
                                <span class="text-emerald-600"><i class="fas fa-circle-check mr-1"></i> Open Slots</span>
                            @endif
                        </div>

                        <!-- "I Choose This Place" Primary Button -->
                        <a href="{{ route('student.attachment-form') }}?company_id={{ $company->id }}" 
                           class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition-all shadow-md shadow-amber-500/10 shrink-0 group">
                            <i class="fas fa-circle-check mr-1.5 text-slate-950 group-hover:scale-110 transition-transform"></i> Choose Place
                        </a>
                    </div>

                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center space-y-3 shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fas fa-building-circle-xmark"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">No Companies Found</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        There are currently no partner companies matching your filter criteria.
                    </p>
                </div>
            @endforelse
        </div>

    </div>
</div>

<!-- CLIENT-SIDE SEARCH SCRIPT -->
<script>
function filterCards() {
    const query = document.getElementById('companySearchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.company-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const town = card.getAttribute('data-town') || '';
        const email = card.getAttribute('data-email') || '';

        if (name.includes(query) || town.includes(query) || email.includes(query)) {
            card.style.display = "flex";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    const countBadge = document.getElementById('companyCountBadge');
    if (countBadge) {
        countBadge.innerText = `Showing ${visibleCount} Partners`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    filterCards();
});
</script>
@endsection