<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\AdministrativeUnit;
use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

final class LecturersImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
{
    use Importable;
    use SkipsFailures;

    public int $successCount = 0;

    /** @var array<int, array{row?: array<string, mixed>, reason: string}> */
    public array $failedRecords = [];

    /**
     * Get the validation rules that apply to the import row.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number'    => ['nullable', 'string', 'max:50', 'unique:users,phone_number'],
            'staff_number'    => ['required', 'string', 'max:100'],
            'job_grade'       => ['required', 'string', 'max:50'],
            'department_code' => ['required', 'string', 'max:100'],
            'office_location' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Map an Excel row to a User model instance.
     *
     * @param array<string, mixed> $row
     */
    public function model(array $row): ?User
    {
        try {
            $staffNo = Str::upper(trim((string) ($row['staff_number'] ?? '')));
            $email = Str::lower(trim((string) ($row['email'] ?? '')));
            $departmentCode = Str::lower(trim((string) ($row['department_code'] ?? '')));
            $name = trim((string) ($row['name'] ?? ''));

            $phoneNumber = isset($row['phone_number']) && trim((string) $row['phone_number']) !== ''
                ? trim((string) $row['phone_number'])
                : null;

            $jobGrade = trim((string) ($row['job_grade'] ?? ''));

            $officeLocation = isset($row['office_location']) && trim((string) $row['office_location']) !== ''
                ? trim((string) $row['office_location'])
                : null;

            $department = AdministrativeUnit::query()
                ->select(['id', 'code', 'level'])
                ->where('code', $departmentCode)
                ->where('level', 2)
                ->first();

            if (! $department) {
                $this->failedRecords[] = [
                    'row'    => $row,
                    'reason' => "Department with code '{$departmentCode}' not found (level 2)",
                ];

                return null;
            }

            return DB::transaction(function () use (
                $staffNo,
                $email,
                $name,
                $phoneNumber,
                $jobGrade,
                $officeLocation,
                $department
            ) {
                $user = User::create([
                    'name'         => $name,
                    'email'        => $email,
                    'phone_number' => $phoneNumber,
                    'password'     => Hash::make($staffNo),
                    'role'         => 'lecturer',
                ]);

                Lecturer::create([
                    'user_id'         => $user->id,
                    'staff_number'    => $staffNo,
                    'job_grade'       => $jobGrade,
                    'department_id'   => $department->id,
                    'office_location' => $officeLocation,
                    'office_phone'    => $phoneNumber,
                ]);

                $this->successCount++;

                return $user;
            });
        } catch (Throwable $e) {
            $this->failedRecords[] = [
                'row'    => $row,
                'reason' => $e->getMessage(),
            ];

            return null;
        }
    }
}