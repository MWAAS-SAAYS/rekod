<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Attachment;
use App\Models\AttachmentStudent;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

final class AttachmentStudentsImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
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
            'reg_no'          => ['required', 'string'],
            'attachment_slug' => ['required', 'string'],
        ];
    }

    /**
     * Map an Excel row to an AttachmentStudent model instance.
     *
     * @param array<string, mixed> $row
     */
    public function model(array $row): ?AttachmentStudent
    {
        try {
            $regNo = Str::upper(trim((string) ($row['reg_no'] ?? '')));
            $slug = Str::lower(trim((string) ($row['attachment_slug'] ?? '')));

            $student = Student::query()
                ->select(['id', 'reg_no'])
                ->where('reg_no', $regNo)
                ->first();

            $attachment = Attachment::query()
                ->select(['id', 'slug'])
                ->where('slug', $slug)
                ->first();

            $errors = [];

            if (! $student) {
                $errors[] = "Student with reg_no '{$regNo}' not found";
            }

            if (! $attachment) {
                $errors[] = "Attachment with slug '{$slug}' not found";
            }

            if ($student && $attachment) {
                $exists = AttachmentStudent::query()
                    ->where('student_id', $student->id)
                    ->where('attachment_id', $attachment->id)
                    ->exists();

                if ($exists) {
                    $errors[] = "Student with reg_no '{$regNo}' for attachment '{$slug}' had already been uploaded";
                }
            }

            if (! empty($errors)) {
                $this->failedRecords[] = [
                    'row'    => $row,
                    'reason' => implode(' | ', $errors),
                ];

                return null;
            }

            $startDate = $this->parseDate($row['start_date'] ?? null);
            $endDate = $this->parseDate($row['end_date'] ?? null);

            $companyName = isset($row['company_name']) && trim((string) $row['company_name']) !== ''
                ? trim((string) $row['company_name'])
                : null;

            $town = isset($row['town']) && trim((string) $row['town']) !== ''
                ? trim((string) $row['town'])
                : null;

            $street = isset($row['street']) && trim((string) $row['street']) !== ''
                ? trim((string) $row['street'])
                : null;

            $building = isset($row['building']) && trim((string) $row['building']) !== ''
                ? trim((string) $row['building'])
                : null;

            $industrialSupervisor = isset($row['industrial_supervisor']) && trim((string) $row['industrial_supervisor']) !== ''
                ? trim((string) $row['industrial_supervisor'])
                : null;

            $program = match (true) {
                isset($row['programme']) && trim((string) $row['programme']) !== '' => trim((string) $row['programme']),
                isset($row['program']) && trim((string) $row['program']) !== ''     => trim((string) $row['program']),
                default                                                            => null,
            };

            $attachmentStudent = AttachmentStudent::create([
                'student_id'            => $student->id,
                'attachment_id'         => $attachment->id,
                'company_name'          => $companyName,
                'start_date'            => $startDate?->format('Y-m-d'),
                'end_date'              => $endDate?->format('Y-m-d'),
                'town'                  => $town,
                'street'                => $street,
                'building'              => $building,
                'industrial_supervisor' => $industrialSupervisor,
                'program'               => $program,
            ]);

            $this->successCount++;

            return $attachmentStudent;
        } catch (Throwable $e) {
            $this->failedRecords[] = [
                'row'    => $row,
                'reason' => $e->getMessage(),
            ];

            return null;
        }
    }

    /**
     * Safely parse Excel numeric timestamps or standard date strings into Carbon instances.
     */
    private function parseDate(mixed $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (Throwable) {
                return null;
            }
        }

        $stringValue = trim((string) $value);

        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $stringValue)->startOfDay();
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($stringValue)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }
}