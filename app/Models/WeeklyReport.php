<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WeeklyReport extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'weekly_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'attachment_student_id',
        'week_id',
        'week_start_date',
        'week_end_date',
        'weekly_report',
        'industrial_supervisor_comment',
        'lecturer_comment',
        'is_approved',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'student_id'            => 'integer',
        'attachment_student_id' => 'integer',
        'week_id'               => 'integer',
        'is_approved'           => 'boolean',
        'week_start_date'       => 'date',
        'week_end_date'         => 'date',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'status',
    ];

    /**
     * Get the student associated with this weekly report.
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the attachment student instance linked to this report.
     *
     * @return BelongsTo<AttachmentStudent, $this>
     */
    public function attachmentStudent(): BelongsTo
    {
        return $this->belongsTo(AttachmentStudent::class, 'attachment_student_id');
    }

    /**
     * Get the daily log entries associated with this weekly report.
     *
     * @return HasMany<DailyReport, $this>
     */
    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'weekly_report_id');
    }

    /**
     * Calculate and return the report review status workflow state.
     */
    public function getStatusAttribute(): string
    {
        if (empty($this->weekly_report)) {
            return 'pending_student';
        }

        if (! $this->is_approved) {
            return 'pending_industrial';
        }

        if (empty($this->lecturer_comment)) {
            return 'pending_lecturer';
        }

        return 'completed';
    }

    /**
     * Scope a query to filter approved reports.
     *
     * @param Builder<WeeklyReport> $query
     * @return Builder<WeeklyReport>
     */
    public function scopeApproved(Builder $query, bool $approved = true): Builder
    {
        return $query->where('is_approved', $approved);
    }

    /**
     * Scope a query to filter reports by week number or ID.
     *
     * @param Builder<WeeklyReport> $query
     * @return Builder<WeeklyReport>
     */
    public function scopeForWeek(Builder $query, int $weekId): Builder
    {
        return $query->where('week_id', $weekId);
    }

    /**
     * Scope a query to filter reports by attachment student ID.
     *
     * @param Builder<WeeklyReport> $query
     * @return Builder<WeeklyReport>
     */
    public function scopeForAttachmentStudent(Builder $query, int $attachmentStudentId): Builder
    {
        return $query->where('attachment_student_id', $attachmentStudentId);
    }
}