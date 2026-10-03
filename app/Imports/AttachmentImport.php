<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Attachment;
use App\Models\Student;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

final class AttachmentImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
{
    use Importable;
    use SkipsFailures;

    /**
     * Get the validation rules that apply to the import row.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'reg_no'               => ['required', 'string'],
            'name_of_organization' => ['required', 'string', 'max:255'],
            'date_started'          => ['required'],
            'expected_date_finish' => ['required'],
            'town'                 => ['required', 'string', 'max:255'],
            'street'               => ['nullable', 'string', 'max:255'],
            'building'             => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Map an Excel row to an Attachment model instance.
     *
     * @param array<string, mixed> $row
     */
    public function model(array $row): ?Attachment
    {
        $regNo = trim((string) ($row['reg_no'] ?? ''));
        if ($regNo === '') {
            return null;
        }

        $student = Student::query()
            ->select(['id', 'reg_no'])
            ->where('reg_no', $regNo)
            ->first();

        if (! $student) {
            return null;
        }

        $startDate = $this->parseDate($row['date_started'] ?? null);
        $endDate = $this->parseDate($row['expected_date_finish'] ?? null);

        if (! $startDate || ! $endDate) {
            return null;
        }

        $street = isset($row['street']) && trim((string) $row['street']) !== ''
            ? trim((string) $row['street'])
            : null;

        $building = isset($row['building']) && trim((string) $row['building']) !== ''
            ? trim((string) $row['building'])
            : null;

        return Attachment::updateOrCreate(
            ['student_id' => $student->id],
            [
                'name'       => trim((string) ($row['name_of_organization'] ?? '')),
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'town'       => trim((string) ($row['town'] ?? '')),
                'street'     => $street,
                'building'   => $building,
            ]
        );
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