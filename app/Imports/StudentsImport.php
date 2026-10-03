<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\AdministrativeUnit;
use App\Models\Student;
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

final class StudentsImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
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
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number'  => ['nullable', 'string', 'max:50'],
            'reg_no'        => ['required', 'string', 'max:100', 'unique:students,reg_no'],
            'year_of_study' => ['nullable', 'string', 'max:20'],
            'program_code'  => ['required', 'string', 'max:100'],
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
            $regNo = Str::upper(trim((string) ($row['reg_no'] ?? '')));
            $email = Str::lower(trim((string) ($row['email'] ?? '')));
            $programCode = Str::lower(trim((string) ($row['program_code'] ?? '')));
            $name = trim((string) ($row['name'] ?? ''));

            $phoneNumber = isset($row['phone_number']) && trim((string) $row['phone_number']) !== ''
                ? trim((string) $row['phone_number'])
                : null;

            $yearOfStudy = isset($row['year_of_study']) && trim((string) $row['year_of_study']) !== ''
                ? trim((string) $row['year_of_study'])
                : null;

            $programme = AdministrativeUnit::query()
                ->select(['id', 'code', 'level'])
                ->where('code', $programCode)
                ->where('level', 3)
                ->first();

            if (! $programme) {
                $this->failedRecords[] = [
                    'row'    => $row,
                    'reason' => "Programme with code '{$programCode}' not found (level 3)",
                ];

                return null;
            }

            return DB::transaction(function () use (
                $regNo,
                $email,
                $name,
                $phoneNumber,
                $yearOfStudy,
                $programme
            ) {
                $user = User::create([
                    'name'         => $name,
                    'email'        => $email,
                    'phone_number' => $phoneNumber,
                    'password'     => Hash::make($regNo),
                    'role'         => 'student',
                ]);

                Student::create([
                    'user_id'       => $user->id,
                    'reg_no'        => $regNo,
                    'program_id'    => $programme->id,
                    'year_of_study' => $yearOfStudy,
                    'phone_number'  => $phoneNumber,
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