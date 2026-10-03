<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\AdministrativeUnit;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

final class AdministrativeUnitsImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
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
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:100'],
            'parent_code' => ['nullable', 'string', 'max:100'],
            'level'       => ['required', 'integer'],
        ];
    }

    /**
     * Map an Excel row to an AdministrativeUnit model instance.
     *
     * @param array<string, mixed> $row
     */
    public function model(array $row): ?AdministrativeUnit
    {
        $code = Str::lower(trim((string) ($row['code'] ?? '')));

        if ($code === '') {
            return null;
        }

        $parentCode = isset($row['parent_code']) && trim((string) $row['parent_code']) !== ''
            ? Str::lower(trim((string) $row['parent_code']))
            : null;

        return AdministrativeUnit::updateOrCreate(
            ['code' => $code],
            [
                'name'        => trim((string) ($row['name'] ?? '')),
                'parent_code' => $parentCode,
                'level'       => (int) ($row['level'] ?? 0),
            ]
        );
    }
}