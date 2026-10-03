<?php

declare(strict_types=1);

namespace App;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;

final class GenerateWeekNumber
{
    /**
     * Generate an ISO year-week ID string (e.g. "202638") from a date or timestamp.
     *
     * @param DateTimeInterface|string|null $date
     */
    public function weekId(DateTimeInterface|string|null $date = null): string
    {
        if ($date === null) {
            $carbonDate = Carbon::now();
        } elseif ($date instanceof DateTimeInterface) {
            $carbonDate = Carbon::instance($date);
        } else {
            $carbonDate = Carbon::parse($date);
        }

        $isoYear = $carbonDate->isoWeekYear();
        $isoWeek = $carbonDate->isoWeek();

        return $isoYear . str_pad((string) $isoWeek, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Resolve the Monday-to-Sunday date range from a 6-digit ISO week ID string (e.g. "202638").
     *
     * @return array{start: string, end: string}
     *
     * @throws InvalidArgumentException
     */
    public function weekRangeFromId(string $weekId): array
    {
        $trimmedId = trim($weekId);

        if (preg_match('/^\d{6}$/', $trimmedId) !== 1) {
            throw new InvalidArgumentException("Invalid week ID format '{$weekId}'. Expected 6-digit 'YYYYWW' ISO string.");
        }

        $isoYear = (int) substr($trimmedId, 0, 4);
        $isoWeek = (int) substr($trimmedId, 4, 2);

        $startOfWeek = Carbon::now()
            ->setISODate($isoYear, $isoWeek)
            ->startOfWeek(Carbon::MONDAY);

        $endOfWeek = $startOfWeek->clone()->endOfWeek(Carbon::SUNDAY);

        return [
            'start' => $startOfWeek->toDateString(),
            'end'   => $endOfWeek->toDateString(),
        ];
    }
}