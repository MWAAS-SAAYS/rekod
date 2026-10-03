<?php

declare(strict_types=1);

namespace App\Services;

final class RegNoParser
{
    /**
     * Map prefix codes to program names and disciplines.
     *
     * @var array<string, string>
     */
    private static array $programMap = [
        'E028' => 'Electrical and Electronic Engineering',
        'E027' => 'Mechanical Engineering',
        'E029' => 'Civil Engineering',
        'C001' => 'Computer Science',
        'B002' => 'Business Administration',
        'H001' => 'Nursing',
    ];

    /**
     * Discipline keyword mapping for automatic platform classification.
     *
     * @var array<string, array<int, string>>
     */
    private static array $keywordMap = [
        'E028' => ['electrical', 'electronic', 'circuit', 'power', 'solar', 'telecom', 'instrumentation', 'control', 'embedded', 'wiring', 'transformer', 'high voltage', 'automation', 'plc', 'dsp'],
        'E027' => ['mechanical', 'automotive', 'manufacturing', 'hvac', 'cad', 'maintenance', 'machining', 'hydraulics', 'pneumatics', 'plant engineering'],
        'E029' => ['civil', 'construction', 'structural', 'surveying', 'building', 'concrete', 'site engineer', 'geotechnical'],
        'C001' => ['software', 'developer', 'computer', 'it', 'web', 'python', 'php', 'laravel', 'networking', 'cyber', 'database', 'frontend', 'backend', 'fullstack', 'system admin'],
        'B002' => ['business', 'accounting', 'finance', 'marketing', 'human resource', 'hr', 'sales', 'management', 'procurement', 'logistics'],
        'H001' => ['nursing', 'clinical', 'health', 'hospital', 'medical', 'pharmacy', 'patient care'],
    ];

    /**
     * Parse raw registration number into structured data.
     *
     * @return array{prefix: ?string, program_name: string, entry_year: ?string}
     */
    public static function parse(?string $regNo): array
    {
        if ($regNo === null || trim($regNo) === '') {
            return [
                'prefix'       => null,
                'program_name' => 'General',
                'entry_year'   => null,
            ];
        }

        $trimmedRegNo = trim($regNo);

        // Matches formats like E028-01-1542/2021, E028/1542/2021, or C001-0100/2022
        if (preg_match('/^([A-Z0-9]+)[\/-].*[\/-](\d{4})$/i', $trimmedRegNo, $matches) === 1) {
            $prefix = strtoupper($matches[1]);
            $entryYear = $matches[2];
        } else {
            $parts = explode('-', str_replace('/', '-', $trimmedRegNo));
            $prefix = strtoupper($parts[0] !== '' ? $parts[0] : 'GENERAL');
            $entryYear = null;
        }

        return [
            'prefix'       => $prefix,
            'program_name' => self::$programMap[$prefix] ?? 'General Discipline',
            'entry_year'   => $entryYear,
        ];
    }

    /**
     * Platform Auto-Classifier: Scans title & description text to automatically determine target discipline.
     */
    public static function detectProgramCode(?string $title, ?string $description): ?string
    {
        $text = strtolower(trim(($title ?? '') . ' ' . ($description ?? '')));

        if ($text === '') {
            return null;
        }

        $scores = [];

        foreach (self::$keywordMap as $code => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($text, strtolower($keyword))) {
                    $score++;
                }
            }

            if ($score > 0) {
                $scores[$code] = $score;
            }
        }

        if (empty($scores)) {
            return null; // General / Open to all disciplines
        }

        // Return program code with the highest matching keyword count
        arsort($scores);

        return array_key_first($scores);
    }

    /**
     * Resolve full program name from a discipline code.
     */
    public static function getProgramName(string $code): string
    {
        return self::$programMap[strtoupper($code)] ?? 'General Discipline';
    }
}