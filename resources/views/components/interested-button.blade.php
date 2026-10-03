@props(['innovation', 'hasExpressedInterest' => false])

<div x-data="{ openModal: false, loading: false }" class="w-full">
    @if($hasExpressedInterest)
        <button 
            disabled 
            class="w-full py-3 px-4 rounded-lg text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 cursor-not-allowed flex items-center justify-center gap-2"
        >
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Interest Submitted (Under Liaison Review)
        </button>
    @else
        <button 
            @click="openModal = true" 
            class="w-full py-3 px-4 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 transition-colors shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-2 group"
        >
            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Express Corporate Interest
        </button>

        <!-- Express Interest Modal -->
        <div 
            x-show="openModal" 
            x-cloak 
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <div 
                @click.away="openModal = false" 
                class="bg-slate-900 border border-slate-800 rounded-xl max-w-lg w-full p-6 text-left shadow-2xl space-y-5"
            >
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-100">Initiate University Legal Channel</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $innovation->title }}</p>
                    </div>
                    <button @click="openModal = false" class="text-slate-500 hover:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('company.innovations.interest', $innovation) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Proposed Engagement Scope</label>
                        <select name="proposed_scope" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-slate-200 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="licensing">Technology Licensing / Transfer</option>
                            <option value="joint_r_and_d">Joint R&D Collaboration</option>
                            <option value="scouting_acquisition">IP Acquisition Inquiry</option>
                            <option value="student_internship">Talent Recruitment / Internship</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Scout Intent & Non-Confidential Inquiry</label>
                        <textarea 
                            name="message" 
                            rows="4" 
                            required 
                            placeholder="Detail your industrial application requirements or research objectives..."
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-xs text-slate-200 placeholder-slate-600 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                        ></textarea>
                        <span class="text-[10px] text-slate-500 mt-1 block">
                            This inquiry is mediated by the University Liaison Office prior to releasing IP documents.
                        </span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button 
                            type="button" 
                            @click="openModal = false" 
                            class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-slate-200 bg-slate-800/50"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-colors shadow-md shadow-indigo-600/20"
                        >
                            Submit Interest Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>