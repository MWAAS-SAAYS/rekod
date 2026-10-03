@extends('layouts.my_app')

@section('title')
    Attachment Form
@endsection

@section('content')
<div class="min-h-screen bg-slate-50/50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- GLOBAL VALIDATION / FLASH ERRORS -->
        @if ($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                <div class="font-bold flex items-center gap-2 text-rose-900">
                    <i class="fas fa-triangle-exclamation text-rose-600"></i>
                    Please correct the errors below to submit your attachment form:
                </div>
                <ul class="list-disc list-inside pl-2 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="flex items-center p-4 text-xs font-medium text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200/80 shadow-xs" role="alert">
                <i class="fas fa-circle-check text-emerald-600 text-base mr-3"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- INSTITUTION HEADER & FORM BANNER -->
        <div class="bg-[#0b1329] rounded-2xl p-6 sm:p-8 text-white border border-amber-500/20 shadow-xl relative overflow-hidden text-center sm:text-left">
            <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-widest">
                    <i class="fas fa-graduation-cap"></i> Dedan Kimathi University of Technology
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-amber-400 uppercase tracking-wider">
                    External Attachment Information Form
                </h1>
                <p class="text-xs text-slate-300 max-w-2xl">
                    Complete all section details accurately. Once submitted, these records will be linked to your academic file for industrial supervision and verification.
                </p>
            </div>
        </div>

        <!-- FORM ENTRY CONTAINER -->
        <form action="{{ route('student.attachment-form.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- SECTION 1: STUDENT DETAILS -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-600 text-xs font-bold flex items-center justify-center">1</span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Student Details</h3>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium">Read-only profile data</span>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Full Name -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Full Name
                            </label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" 
                                       name="student_name" 
                                       value="{{ $logged_user->name }}" 
                                       placeholder="Full Name" 
                                       class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-600 cursor-not-allowed" 
                                       disabled>
                            </div>
                        </div>

                        <!-- Registration Number -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Registration Number
                            </label>
                            <div class="relative">
                                <i class="fas fa-id-card absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" 
                                       name="reg_no" 
                                       value="{{ $my_student_details->reg_no }}" 
                                       placeholder="Registration Number" 
                                       class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-semibold" 
                                       readonly>
                            </div>
                        </div>

                        <!-- Course / Academic Program -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Academic Program / Course
                            </label>
                            <div class="relative">
                                <i class="fas fa-book-bookmark absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" 
                                       name="course" 
                                       value="{{ $my_student_details->program->name }}" 
                                       placeholder="Course" 
                                       class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-semibold" 
                                       readonly>
                            </div>
                        </div>

                        <!-- Student Phone Number -->
                        <div class="space-y-1.5">
                            <label for="student_phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Phone Number <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-phone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="tel" 
                                       id="student_phone"
                                       name="student_phone" 
                                       placeholder="e.g. 0712345678" 
                                       value="{{ old('student_phone', $my_student_details->phone_number) }}" 
                                       class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border @error('student_phone') border-rose-400 bg-rose-50/30 @else border-slate-200 @enderror text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                       required>
                            </div>
                            @error('student_phone')
                                <p class="text-[11px] font-semibold text-rose-500 flex items-center gap-1 mt-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Student Email -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Email Address
                            </label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="email" 
                                       name="student_email" 
                                       value="{{ $logged_user->email }}" 
                                       placeholder="Email" 
                                       class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-semibold" 
                                       readonly>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- SECTION 2: COMPANY DETAILS -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-600 text-xs font-bold flex items-center justify-center">2</span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Company Details</h3>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium">* Required fields</span>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Company Name -->
                        <div class="space-y-1.5">
                            <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Company Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="e.g. Kenya Power"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                        </div>

                        <!-- Company Alias -->
                        <div class="space-y-1.5">
                            <label for="alias" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Company Alias / Abbreviation <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="alias" 
                                   id="alias" 
                                   value="{{ old('alias') }}" 
                                   placeholder="e.g. KPLC"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                        </div>

                        <!-- Contact -->
                        <div class="space-y-1.5">
                            <label for="contact" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Contact Phone <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="contact" 
                                   id="contact" 
                                   value="{{ old('contact') }}" 
                                   placeholder="Company Official Phone"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Company Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="info@company.com"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Postal / Physical Address <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="address" 
                                   id="address" 
                                   value="{{ old('address') }}" 
                                   placeholder="e.g. P.O. Box 12345-00100, Nairobi"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                        </div>

                        <!-- County -->
                        <div class="space-y-1.5">
                            <label for="county" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                County <span class="text-rose-500">*</span>
                            </label>
                            <select name="county_id" id="county" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs select2 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
                                <option value="">-- Select County --</option>
                                @foreach($counties as $county)
                                    <option value="{{ $county->id }}" {{ old('county_id') == $county->id ? 'selected' : '' }}>
                                        {{ $county->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Town -->
                        <div class="space-y-1.5">
                            <label for="town" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Town <span class="text-rose-500">*</span>
                            </label>
                            <select name="town_id" id="town" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs select2 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required>
                                <option value="">-- Select Town --</option>
                                @foreach($towns as $town)
                                    <option value="{{ $town->id }}" {{ old('town_id') == $town->id ? 'selected' : '' }}>
                                        {{ $town->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Street / Road -->
                        <div class="space-y-1.5">
                            <label for="street" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Street / Road <span class="text-slate-400 text-[10px] lowercase">(optional)</span>
                            </label>
                            <input type="text" 
                                   name="street" 
                                   id="street" 
                                   value="{{ old('street') }}" 
                                   placeholder="e.g. Kimathi Way" 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                        </div>

                        <!-- Building -->
                        <div class="space-y-1.5">
                            <label for="building" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Building <span class="text-slate-400 text-[10px] lowercase">(optional)</span>
                            </label>
                            <input type="text" 
                                   name="building" 
                                   id="building" 
                                   value="{{ old('building') }}" 
                                   placeholder="e.g. Resource Centre" 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                        </div>

                    </div>
                </div>
            </div>

            <!-- SECTION 3: ATTACHMENT DATES -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-600 text-xs font-bold flex items-center justify-center">3</span>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Attachment Timeline</h3>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Date Commenced -->
                        <div class="space-y-1.5">
                            <label for="date_commenced" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Date Commenced <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   id="date_commenced" 
                                   name="start_date" 
                                   value="{{ old('start_date', $attachment_student->start_date ?? '') }}" 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('start_date') border-rose-400 bg-rose-50/30 @else border-slate-200 @enderror text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                            @error('start_date')
                                <p class="text-[11px] font-semibold text-rose-500 flex items-center gap-1 mt-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Expected Finishing Date -->
                        <div class="space-y-1.5">
                            <label for="date_finished" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Expected Finishing Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   id="date_finished" 
                                   name="end_date" 
                                   value="{{ old('end_date', $attachment_student->end_date ?? '') }}" 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border @error('end_date') border-rose-400 bg-rose-50/30 @else border-slate-200 @enderror text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                            @error('end_date')
                                <p class="text-[11px] font-semibold text-rose-500 flex items-center gap-1 mt-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            <!-- SECTION 4: SUPERVISOR DETAILS -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-600 text-xs font-bold flex items-center justify-center">4</span>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Supervisor Details</h3>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Supervisor Name -->
                        <div class="space-y-1.5">
                            <label for="supervisor_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Supervisor Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="supervisor_name" 
                                   id="supervisor_name" 
                                   placeholder="Full Name" 
                                   value="{{ old('supervisor_name') }}" 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                            @error('supervisor_name') 
                                <p class="text-[11px] font-semibold text-rose-500 flex items-center gap-1 mt-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p> 
                            @enderror
                        </div>

                        <!-- Supervisor Email -->
                        <div class="space-y-1.5">
                            <label for="supervisor_email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Supervisor Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" 
                                   name="supervisor_email" 
                                   id="supervisor_email" 
                                   placeholder="supervisor@company.com" 
                                   value="{{ old('supervisor_email') }}" 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                            @error('supervisor_email') 
                                <p class="text-[11px] font-semibold text-rose-500 flex items-center gap-1 mt-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p> 
                            @enderror
                        </div>

                        <!-- Supervisor Phone -->
                        <div class="space-y-1.5">
                            <label for="supervisor_phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Supervisor Phone <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" 
                                   name="supervisor_phone" 
                                   id="supervisor_phone" 
                                   placeholder="Official Mobile / Landline" 
                                   value="{{ old('supervisor_phone') }}" 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                            @error('supervisor_phone') 
                                <p class="text-[11px] font-semibold text-rose-500 flex items-center gap-1 mt-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p> 
                            @enderror
                        </div>

                        <!-- Supervisor Position -->
                        <div class="space-y-1.5">
                            <label for="supervisor_position" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Position / Title <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="supervisor_position" 
                                   id="supervisor_position" 
                                   placeholder="e.g. Senior Electrical Engineer" 
                                   value="{{ old('supervisor_position') }}" 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all" 
                                   required>
                            @error('supervisor_position') 
                                <p class="text-[11px] font-semibold text-rose-500 flex items-center gap-1 mt-1">
                                    <i class="fas fa-circle-exclamation"></i> {{ $message }}
                                </p> 
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            <!-- SUBMIT BUTTON BAR -->
            <div class="flex items-center justify-end pt-2">
                <button type="submit" class="inline-flex items-center px-6 py-3 text-xs font-bold rounded-xl bg-slate-900 text-amber-400 hover:bg-slate-800 hover:text-amber-300 transition-all shadow-md shadow-slate-900/10 group">
                    <i class="fas fa-paper-plane mr-2 group-hover:translate-x-0.5 transition-transform"></i>
                    Submit External Attachment Form
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            if ($('#organization').val()) {
                handleOrganizationChange();
            }

            function handleOrganizationChange() {
                let selected = $('#organization').find('option:selected');

                $('#town').val(selected.data('town') || '');
                $('#street').val(selected.data('street') || '');
                $('#building').val(selected.data('building') || '');

                loadCompanySupervisors(selected.val());
            }

            $("#organization").on('change', handleOrganizationChange);

            function loadCompanySupervisors(companyId) {
                const url = `/get-company-industrial-supervisors/${companyId}`;

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        let $select = $('#industrial_supervisor');
                        $select.empty().append('<option value="">-- select Supervisor --</option>');

                        $.each(response, function (i, supervisor) {
                            $select.append(`<option value="${supervisor.id}" data-phone="${supervisor.user.phone_number}" data-email="${supervisor.user.email}">
                                                ${supervisor.user.name}
                                            </option>`);
                        });
                        let oldCompanyId  = @json(old('company_id', $attachment_student->company_id ?? null));
                        let oldSupervisor = @json(old('industrial_supervisor_id', $attachment_student->industrial_supervisor_id ?? null));

                        if (companyId === oldCompanyId && oldSupervisor) {
                            $select.val(oldSupervisor).trigger('change');
                        } else {
                            $select.val('').trigger('change');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error fetching supervisors:', xhr.responseText);
                    }
                });
            }

            $('#industrial_supervisor').on('change', function () {
                const selected = $(this).find(':selected');
                $('#supervisor_phone').val(selected.data('phone') || '');
                $('#supervisor_email').val(selected.data('email') || '');
            });

            $('#add_new_company').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#existing_company_section').hide();
                    $('#new_company_section').show();
                } else {
                    $('#existing_company_section').show();
                    $('#new_company_section').hide();
                }
            });

            // fill Town/Street/Building when existing company is selected
            $('#organization').on('change', function() {
                let selected = $(this).find('option:selected');
                $('#town').val(selected.data('town') || '');
                $('#street').val(selected.data('street') || '');
                $('#building').val(selected.data('building') || '');
            });

            $('#add_new_supervisor').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#existing_supervisor_section').hide();
                    $('#new_supervisor_section').show();
                } else {
                    $('#existing_supervisor_section').show();
                    $('#new_supervisor_section').hide();
                }
            });

            // Fill phone/email when selecting existing supervisor
            $('#industrial_supervisor').on('change', function() {
                let selected = $(this).find(':selected');
                $('#supervisor_phone').val(selected.data('phone') || '');
                $('#supervisor_email').val(selected.data('email') || '');
            });
        });
    </script>
@endsection