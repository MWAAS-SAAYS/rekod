<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\AdministrativeUnit;
use App\Models\Attachment;
use App\Models\AttachmentLecturer;
use App\Models\Lecturer;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

final class AttachmentLecturersImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
{
    use Importable;
    use SkipsFailures;

    public int $successCount = 0;

    /** @var array<int, array{row: array<string, mixed>, reason: string}> */
    public array $failedRecords = [];

    /**
     * Get the validation rules that apply to the import row.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'staff_number'    => ['required', 'string'],
            'job_grade'       => ['required', 'string', 'max:50'],
            'attachment_slug' => ['required', 'string'],
            'department_code' => ['nullable', 'string'],
        ];
    }

    /**
     * Map an Excel row to an AttachmentLecturer model instance.
     *
     * @param array<string, mixed> $row
     */
    public function model(array $row): ?AttachmentLecturer
    {
        try {
            $staffNo = Str::upper(trim((string) ($row['staff_number'] ?? '')));
            $jobGrade = trim((string) ($row['job_grade'] ?? ''));
            $slug = Str::lower(trim((string) ($row['attachment_slug'] ?? '')));
            $departmentCode = Str::lower(trim((string) ($row['department_code'] ?? '')));

            $lecturer = Lecturer::query()
                ->select(['id', 'department_id', 'job_grade', 'staff_number'])
                ->where('staff_number', $staffNo)
                ->first();

            $attachment = Attachment::query()
                ->select(['id', 'slug'])
                ->where('slug', $slug)
                ->first();

            $department = null;
            $errors = [];

            if (! $lecturer) {
                $errors[] = "Lecturer with staff_no '{$staffNo}' not found";
            }

            if (! $attachment) {
                $errors[] = "Attachment with slug '{$slug}' not found";
            }

            if ($departmentCode !== '') {
                $department = AdministrativeUnit::query()
                    ->select(['id', 'code', 'level'])
                    ->where('code', $departmentCode)
                    ->where('level', 2)
                    ->first();

                if (! $department) {
                    $errors[] = "Department with code '{$departmentCode}' not found";
                }
            }

            $departmentId = $department->id ?? $lecturer?->department_id;

            if ($lecturer && $attachment && $departmentId) {
                $exists = AttachmentLecturer::query()
                    ->where('lecturer_id', $lecturer->id)
                    ->where('attachment_id', $attachment->id)
                    ->where('department_id', $departmentId)
                    ->exists();

                if ($exists) {
                    $errors[] = "Lecturer with staff_no '{$staffNo}' for attachment '{$slug}' had already been uploaded";
                }
            }

            if (! empty($errors)) {
                $this->failedRecords[] = [
                    'row'    => $row,
                    'reason' => implode(' | ', $errors),
                ];

                return null;
            }

            if ($jobGrade !== '' && $lecturer->job_grade !== $jobGrade) {
                $lecturer->update(['job_grade' => $jobGrade]);
            }

            $attachmentLecturer = AttachmentLecturer::create([
                'lecturer_id'   => $lecturer->id,
                'attachment_id' => $attachment->id,
                'department_id' => $departmentId,
                'job_grade'     => $jobGrade ?: $lecturer->job_grade,
            ]);

            $this->successCount++;

            return $attachmentLecturer;
        } catch (Throwable $e) {
            $this->failedRecords[] = [
                'row'    => $row,
                'reason' => $e->getMessage(),
            ];

            return null;
        }
    }
}