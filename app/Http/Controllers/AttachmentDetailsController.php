<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\AttachmentStudent;
use App\Models\Company;
use App\Models\IndustrialSupervisor;
use App\Models\Location;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class AttachmentDetailsController extends Controller
{
    /**
     * Show the form for editing student attachment details.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $attachmentStudentId = $request->session()->get('attachment_student_id');

        if (!$attachmentStudentId) {
            return redirect()->route('dashboard')->with('error', 'No active attachment session found.');
        }

        $attachmentStudent = AttachmentStudent::findOrFail($attachmentStudentId);

        // Ensure current user owns this attachment record
        $loggedUser = auth()->user();
        $myStudentDetails = Student::with('program')
            ->where('user_id', $loggedUser->id)
            ->first();

        if ($myStudentDetails && $attachmentStudent->student_id !== $myStudentDetails->id) {
            abort(403, 'Unauthorized access to attachment details.');
        }

        if ($attachmentStudent->company_id) {
            return view('student.form-submitted', compact('attachmentStudentId'));
        }

        $companies = Company::with('town')->get();
        $counties = Location::where('level', 1)->orderBy('name', 'asc')->get();
        $towns = Location::where('level', 3)->orderBy('name', 'asc')->get();

        return view('student.attachment-form', compact(
            'companies',
            'loggedUser',
            'myStudentDetails',
            'attachmentStudent',
            'attachmentStudentId',
            'counties',
            'towns'
        ));
    }

    /**
     * Update student attachment, company, and supervisor details.
     */
    public function update(Request $request): RedirectResponse
    {
        $attachmentId = $request->session()->get('attachment_id');
        $attachmentStudentId = $request->session()->get('attachment_student_id');

        if (!$attachmentStudentId || !$attachmentId) {
            return redirect()->back()->with('error', 'Session expired. Please refresh the page.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'student_phone' => ['required', 'string', 'max:20'],
            'supervisor_name' => ['nullable', 'string', 'max:255'],
            'supervisor_email' => ['required', 'email', 'max:255'],
            'supervisor_phone' => ['required', 'string', 'max:20'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'alias' => ['nullable', 'string', 'max:100'],
            'contact' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'county_id' => ['nullable', 'exists:locations,id'],
            'town_id' => ['nullable', 'exists:locations,id'],
            'street' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            /** @var User $currentUser */
            $currentUser = auth()->user();
            $currentUser->update([
                'phone_number' => $validated['student_phone'],
            ]);

            // Create or retrieve company user with secure random password fallback
            $companyUser = User::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'role' => 'company',
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            $company = Company::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'alias' => $validated['alias'] ?? null,
                    'contact' => $validated['contact'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'county_id' => $validated['county_id'] ?? null,
                    'town_id' => $validated['town_id'] ?? null,
                    'user_id' => $companyUser->id,
                    'street' => $validated['street'] ?? 'N/A',
                    'building' => $validated['building'] ?? 'N/A',
                ]
            );

            $supervisorUser = User::firstOrCreate(
                ['email' => $validated['supervisor_email']],
                [
                    'name' => $validated['supervisor_name'] ?? 'Industrial Supervisor',
                    'phone_number' => $validated['supervisor_phone'],
                    'role' => 'industrial_supervisor',
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            $supervisorProfile = IndustrialSupervisor::updateOrCreate(
                ['user_id' => $supervisorUser->id],
                [
                    'company_id' => $company->id,
                    'position_title' => 'Industrial Supervisor',
                ]
            );

            AttachmentStudent::where('id', $attachmentStudentId)
                ->update([
                    'company_id' => $company->id,
                    'industrial_supervisor_id' => $supervisorProfile->id,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                ]);

            DB::commit();

            return redirect()->back()->with([
                'notification' => [
                    'icon' => 'success',
                    'title' => 'Success',
                    'message' => 'Attachment details updated successfully.',
                ],
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update attachment details: ' . $e->getMessage(), ['exception' => $e]);

            return redirect()->back()->with('error', 'An error occurred while updating details.');
        }
    }

    /**
     * Display JSON details for a given AttachmentStudent ID.
     */
    public function show(int $id): JsonResponse
    {
        $data = AttachmentStudent::with([
            'student.user',
            'student.program',
            'company.town',
            'industrialSupervisor.user',
            'attachment',
            'attachmentLecturer.lecturer.user',
        ])->findOrFail($id);

        return response()->json($data);
    }

    /**
     * Import attachment student records via Excel/CSV.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls'],
        ]);

        try {
            $dataRows = Excel::toCollection(
                new class implements WithHeadingRow {
                    public function headingRow(): int
                    {
                        return 1;
                    }
                },
                $request->file('file')
            )->first();

            if (!$dataRows || $dataRows->isEmpty()) {
                return response()->json(['message' => 'Import file is empty.'], 422);
            }

            $firstRow = $dataRows->first();
            $attachmentSlug = trim((string) data_get($firstRow, 'attachment_slug'));

            if ($attachmentSlug !== '') {
                $attachment = Attachment::where('slug', $attachmentSlug)->first();

                if (!$attachment) {
                    return response()->json([
                        'message' => "Attachment with slug '{$attachmentSlug}' not found.",
                    ], 422);
                }
            } else {
                $attachment = Attachment::latest()->first();

                if (!$attachment) {
                    return response()->json([
                        'message' => 'No attachment period found. Please create an attachment period first.',
                    ], 422);
                }
            }

            $processedCount = 0;

            DB::transaction(function () use ($dataRows, $attachment, &$processedCount) {
                $defaultCounty = Location::where('level', 1)->where('name', 'Nairobi')->first()
                    ?? Location::where('level', 1)->first();
                $defaultCountyId = $defaultCounty ? $defaultCounty->id : 1;

                foreach ($dataRows as $row) {
                    $regNo = trim((string) data_get($row, 'reg_no'));
                    if ($regNo === '') {
                        continue;
                    }

                    $studentName = trim((string) (data_get($row, 'student_name')
                        ?? data_get($row, 'Student Name')
                        ?? ('Student ' . $regNo)));
                    $studentEmail = trim((string) data_get($row, 'student_email')) ?: (strtolower(str_replace('/', '', $regNo)) . '@student.com');
                    $studentPhone = trim((string) data_get($row, 'student_phone')) ?: '0700000000';

                    $studentUser = User::firstOrCreate(
                        ['email' => $studentEmail],
                        [
                            'name' => $studentName,
                            'phone_number' => $studentPhone,
                            'role' => 'student',
                            'password' => Hash::make(Str::random(12)),
                        ]
                    );

                    $programId = data_get($row, 'program_id') ?: 38;

                    $student = Student::updateOrCreate(
                        ['user_id' => $studentUser->id],
                        [
                            'phone_number' => $studentPhone,
                            'reg_no' => $regNo,
                            'program_id' => $programId,
                        ]
                    );

                    $companyName = trim((string) data_get($row, 'name_of_organization')) ?: 'Unknown Company';
                    $companyEmail = trim((string) data_get($row, 'company_email'));

                    if ($companyEmail === '') {
                        $companyEmail = strtolower((string) preg_replace('/[^A-Za-z0-9]/', '', $companyName)) . '@company.com';
                    }

                    $companyUser = User::firstOrCreate(
                        ['email' => $companyEmail],
                        [
                            'name' => $companyName,
                            'role' => 'company',
                            'password' => Hash::make(Str::random(12)),
                        ]
                    );

                    $townName = trim((string) data_get($row, 'town'));
                    $excelCounty = trim((string) data_get($row, 'county'));

                    $townId = null;
                    $countyId = null;

                    if ($townName !== '') {
                        $town = Location::where('level', 3)
                            ->whereRaw('LOWER(name) = ?', [strtolower($townName)])
                            ->first();

                        if ($town) {
                            $townId = $town->id;
                            $countyId = $town->parent_id;
                        }
                    }

                    if (!$countyId && $excelCounty !== '') {
                        $county = Location::where('level', 1)
                            ->whereRaw('LOWER(name) = ?', [strtolower($excelCounty)])
                            ->first();

                        if ($county) {
                            $countyId = $county->id;
                        }
                    }

                    $countyId = $countyId ?? $defaultCountyId;

                    $company = Company::updateOrCreate(
                        ['email' => $companyEmail],
                        [
                            'name' => $companyName,
                            'user_id' => $companyUser->id,
                            'alias' => strtoupper(substr($companyName, 0, 3)) . '-' . $companyUser->id,
                            'contact' => trim((string) data_get($row, 'contact')) ?: '0700000000',
                            'address' => data_get($row, 'address', $townName ? $townName . ' Road' : 'N/A'),
                            'county_id' => $countyId,
                            'town_id' => $townId,
                            'street' => data_get($row, 'street', 'N/A'),
                            'building' => data_get($row, 'building', 'N/A'),
                        ]
                    );

                    $supervisorEmail = trim((string) data_get($row, 'supervisor_email'));
                    $supervisorName = trim((string) data_get($row, 'name_of_indusrty_supervisor'));
                    $supervisorPhone = trim((string) data_get($row, 'telephone_number_of_supervisor'));

                    $industrialSupervisorId = null;

                    if ($supervisorEmail !== '') {
                        $supervisorUser = User::firstOrCreate(
                            ['email' => $supervisorEmail],
                            [
                                'name' => $supervisorName ?: 'Industrial Supervisor',
                                'phone_number' => $supervisorPhone,
                                'role' => 'industrial_supervisor',
                                'password' => Hash::make(Str::random(12)),
                            ]
                        );

                        $supervisorProfile = IndustrialSupervisor::updateOrCreate(
                            ['user_id' => $supervisorUser->id],
                            [
                                'company_id' => $company->id,
                                'position_title' => 'Industrial Supervisor',
                            ]
                        );

                        $industrialSupervisorId = $supervisorProfile->id;
                    }

                    AttachmentStudent::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'attachment_id' => $attachment->id,
                        ],
                        [
                            'company_id' => $company->id,
                            'industrial_supervisor_id' => $industrialSupervisorId,
                            'start_date' => $this->transformDate((string) data_get($row, 'date_started')),
                            'end_date' => $this->transformDate((string) data_get($row, 'expected_date_of_completion')),
                            'town_id' => $townId,
                            'county_id' => $countyId,
                        ]
                    );

                    $processedCount++;
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => "Successfully imported {$processedCount} student records.",
            ]);
        } catch (Throwable $e) {
            Log::error('Import Error: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the view for importing attachment records.
     */
    public function showImportPage(): View
    {
        return view('admin.attachmentimport');
    }

    /**
     * Parse raw Excel date string or integer into 'Y-m-d' format.
     */
    private function transformDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    ExcelDate::excelToDateTimeObject((float) $value)
                )->format('Y-m-d');
            } catch (Throwable) {
                return null;
            }
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            Log::warning("Unable to parse date: {$value}");

            return null;
        }
    }
}