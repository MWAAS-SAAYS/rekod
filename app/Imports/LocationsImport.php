<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

final class LocationsImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
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
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:100'],
            'parent_code' => ['nullable', 'string', 'max:100'],
            'level'       => ['required', 'integer'],
        ];
    }

    /**
     * Map an Excel row to a Location model instance.
     *
     * @param array<string, mixed> $row
     */
    public function model(array $row): ?Location
    {
        try {
            $code = Str::lower(trim((string) ($row['code'] ?? '')));
            $name = trim((string) ($row['name'] ?? ''));

            $parentCode = isset($row['parent_code']) && trim((string) $row['parent_code']) !== ''
                ? Str::lower(trim((string) $row['parent_code']))
                : null;

            $level = (int) ($row['level'] ?? 0);

            if ($code === '' || $name === '') {
                return null;
            }

            $location = DB::transaction(function () use ($code, $name, $parentCode, $level) {
                return Location::updateOrCreate(
                    ['code' => $code],
                    [
                        'name'        => $name,
                        'parent_code' => $parentCode,
                        'level'       => $level,
                    ]
                );
            });

            $this->successCount++;

            return $location;
        } catch (Throwable $e) {
            $this->failedRecords[] = [
                'row'    => $row,
                'reason' => $e->getMessage(),
            ];

            return null;
        }
    }
}