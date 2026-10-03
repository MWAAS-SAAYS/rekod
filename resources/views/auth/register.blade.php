@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-10">
    <div class="w-full max-w-4xl bg-white p-8 rounded shadow">
        <h2 class="text-2xl font-bold text-center mb-6">User Registration</h2>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="regForm">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium">Full Name <span class="text-red-600">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full mt-1 p-2 border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Email <span class="text-red-600">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full mt-1 p-2 border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Register As <span class="text-red-600">*</span></label>
                <select name="role" id="role" required class="w-full mt-1 p-2 border rounded">
                    <option value="">-- Select Role --</option>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                    <option value="lecturer" {{ old('role') == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                    <option value="company" {{ old('role') == 'company' ? 'selected' : '' }}>Company</option>
                </select>
            </div>

            <!-- Student Fields -->
            <div id="student-fields" class="hidden">
                <fieldset class="border rounded p-4 mb-4">
                    <legend class="font-semibold text-lg px-2">Student Details</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Registration Number <span class="text-red-600">*</span></label>
                            <input type="text" name="reg_no" value="{{ old('reg_no') }}" class="w-full mt-1 p-2 border rounded">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Year of Study <span class="text-red-600">*</span></label>
                            <select name="year_of_study" class="w-full mt-1 p-2 border rounded">
                                <option value="">-- Select Year --</option>
                                <option value="Year 1" {{ old('year_of_study') == 'Year 1' ? 'selected' : '' }}>Year 1</option>
                                <option value="Year 2" {{ old('year_of_study') == 'Year 2' ? 'selected' : '' }}>Year 2</option>
                                <option value="Year 3" {{ old('year_of_study') == 'Year 3' ? 'selected' : '' }}>Year 3</option>
                                <option value="Year 4" {{ old('year_of_study') == 'Year 4' ? 'selected' : '' }}>Year 4</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Program <span class="text-red-600">*</span></label>
                            <select name="program_id" class="w-full mt-1 p-2 border rounded">
                                <option value="">-- Select Program --</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Phone Number <span class="text-red-600">*</span></label>
                            <input type="text" name="phone_number" value="{{ old('phone_number') }}" class="w-full mt-1 p-2 border rounded">
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- Lecturer Fields -->
            <div id="lecturer-fields" class="hidden">
                <fieldset class="border rounded p-4 mb-4">
                    <legend class="font-semibold text-lg px-2">Lecturer Details</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Staff Number <span class="text-red-600">*</span></label>
                            <input type="text" name="staff_number" value="{{ old('staff_number') }}" class="w-full mt-1 p-2 border rounded">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Phone Number <span class="text-red-600">*</span></label>
                            <input type="text" name="office_phone" value="{{ old('office_phone') }}" class="w-full mt-1 p-2 border rounded">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Job Grade <span class="text-red-600">*</span></label>
                            <input type="text" name="job_grade" value="{{ old('job_grade') }}" class="w-full mt-1 p-2 border rounded">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Department <span class="text-red-600">*</span></label>
                            <select name="department_id" class="w-full mt-1 p-2 border rounded">
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>
            </div>

            <!-- Company Fields -->
            <div id="company-fields" class="hidden">
                <fieldset class="border rounded p-4 mb-4">
                    <legend class="font-semibold text-lg">Company Details</legend>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="company_name" class="block font-semibold">Company Name <span class="text-red-600">*</span></label>
                            <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" class="w-full border rounded p-2">
                        </div>

                        <div>
                            <label for="alias" class="block font-semibold">Company Alias <span class="text-red-600">*</span></label>
                            <input type="text" name="alias" id="alias" value="{{ old('alias') }}" class="w-full border rounded p-2">
                        </div>

                        <div>
                            <label for="contact" class="block font-semibold">Contact <span class="text-red-600">*</span></label>
                            <input type="text" name="contact" id="contact" value="{{ old('contact') }}" class="w-full border rounded p-2">
                        </div>

                        <div>
                            <label for="company_email" class="block font-semibold">Company Email <span class="text-red-600">*</span></label>
                            <input type="email" name="company_email" id="company_email" value="{{ old('company_email') }}" class="w-full border rounded p-2">
                        </div>

                        <div class="md:col-span-2">
                            <label for="address" class="block font-semibold">Address <span class="text-red-600">*</span></label>
                            <input type="text" name="address" id="address" value="{{ old('address') }}" class="w-full border rounded p-2">
                        </div>

                        <div>
                            <label for="county" class="block font-semibold">County <span class="text-red-600">*</span></label>
                            <select name="county_id" id="county" class="w-full border rounded p-2 select2">
                                <option value="">-- Select County --</option>
                                @foreach($counties as $county)
                                    <option value="{{ $county->id }}" data-code="{{ $county->code }}" {{ old('county_id') == $county->id ? 'selected' : '' }}>
                                        {{ $county->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="town" class="block font-semibold">Town <span class="text-red-600">*</span></label>
                            <select name="town_id" id="town" class="w-full border rounded p-2 select2">
                                <option value="">-- Select Town --</option>
                                @foreach($towns as $town)
                                    <option value="{{ $town->id }}" {{ old('town_id') == $town->id ? 'selected' : '' }}>
                                        {{ $town->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="street" class="block font-semibold">Street/Road <span class="text-gray-400 text-xs">(Optional)</span></label>
                            <input type="text" name="street" id="street" value="{{ old('street') }}" placeholder="e.g. Kimathi Way" class="w-full border rounded p-2">
                        </div>

                        <div>
                            <label for="building" class="block font-semibold">Building <span class="text-gray-400 text-xs">(Optional)</span></label>
                            <input type="text" name="building" id="building" value="{{ old('building') }}" placeholder="e.g. Resource Centre" class="w-full border rounded p-2">
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Password <span class="text-red-600">*</span></label>
                <input type="password" name="password" required class="w-full mt-1 p-2 border rounded">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium">Confirm Password <span class="text-red-600">*</span></label>
                <input type="password" name="password_confirmation" required class="w-full mt-1 p-2 border rounded">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">
                Register
            </button>
        </form>
    </div>
</div>

<!-- jQuery (Required for Select2 & AJAX) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const roleSelect = document.getElementById('role');
        const studentFields = document.getElementById('student-fields');
        const lecturerFields = document.getElementById('lecturer-fields');
        const companyFields = document.getElementById('company-fields');

        function toggleFields() {
            const role = roleSelect.value;
            
            // Hide all sections first
            studentFields.classList.add('hidden');
            lecturerFields.classList.add('hidden');
            companyFields.classList.add('hidden');
            
            // Disable all fields in hidden sections so they are omitted from form submit
            document.querySelectorAll('#student-fields input, #student-fields select, #lecturer-fields input, #lecturer-fields select, #company-fields input, #company-fields select').forEach(el => {
                el.disabled = true;
            });

            // Show and enable selected section
            if (role === 'student') {
                studentFields.classList.remove('hidden');
                studentFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
            } else if (role === 'lecturer') {
                lecturerFields.classList.remove('hidden');
                lecturerFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
            } else if (role === 'company') {
                companyFields.classList.remove('hidden');
                companyFields.querySelectorAll('input, select').forEach(el => el.disabled = false);
            }

            // Sync Select2 disabled states
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('.select2').trigger('change.select2');
            }
        }

        roleSelect.addEventListener('change', toggleFields);
        toggleFields(); // Initial run
    });

    // jQuery & Select2 Handlers
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({ width: '100%' });
        }

        // Dependent Dropdown: Fetch Towns when County changes
        $('#county').on('change', function() {
            let countyId = $(this).val();
            let townSelect = $('#town');

            townSelect.empty().append('<option value="">-- Select Town --</option>');

            if (countyId) {
                // Fetch towns via AJAX
                $.ajax({
                    url: '/get-towns/' + countyId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $.each(data, function(key, town) {
                            townSelect.append('<option value="' + town.id + '">' + town.name + '</option>');
                        });
                        townSelect.trigger('change'); // Update Select2 interface
                    },
                    error: function() {
                        console.log('Town fetch route /get-towns/' + countyId + ' not found or returned error.');
                    }
                });
            } else {
                townSelect.trigger('change');
            }
        });
    });
</script>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush
@endsection