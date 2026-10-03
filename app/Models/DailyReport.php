<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weekly_report_id' => 'integer',
        'report_date'      => 'date',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Get the student (user) who authored this daily logbook entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registration_number', 'registration_number');
    }

    /**
     * Get the parent weekly report that synthesizes this daily report.
     *
     * @return BelongsTo<WeeklyReport, $this>
     */
    public function weeklyReport(): BelongsTo
    {
        return $this->belongsTo(WeeklyReport::class, 'weekly_report_id');
    }

    /**
     * Scope a query to filter daily reports by student registration number.
     *
     * @param Builder<DailyReport> $query
     * @return Builder<DailyReport>
     */
    public function scopeForStudent(Builder $query, string $registrationNumber): Builder
    {
        return $query->where('registration_number', $registrationNumber);
    }

    /**
     * Scope a query to filter daily reports by parent weekly report ID.
     *
     * @param Builder<DailyReport> $query
     * @return Builder<DailyReport>
     */
    public function scopeForWeeklyReport(Builder $query, int $weeklyReportId): Builder
    {
        return $query->where('weekly_report_id', $weeklyReportId);
    }

    /**
     * Scope a query to filter daily reports on a specific date.
     *
     * @param Builder<DailyReport> $query
     * @return Builder<DailyReport>
     */
    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('report_date', $date);
    }
}