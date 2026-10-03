<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttachmentLecturer extends Model
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
        'attachment_id' => 'integer',
        'department_id' => 'integer',
        'job_grade_id'  => 'integer',
        'user_id'       => 'integer',
        'lecturer_id'   => 'integer',
    ];

    /**
     * Get the attachment session associated with this allocation.
     *
     * @return BelongsTo<Attachment, $this>
     */
    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }

    /**
     * Get the administrative department unit.
     *
     * @return BelongsTo<AdministrativeUnit, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(AdministrativeUnit::class, 'department_id');
    }

    /**
     * Get the job grade assigned to this lecturer allocation.
     *
     * @return BelongsTo<JobGrade, $this>
     */
    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class, 'job_grade_id');
    }

    /**
     * Get the budget entries associated with this lecturer allocation.
     *
     * @return HasMany<Budget, $this>
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'attachment_lecturer_id');
    }

    /**
     * Get the user account of the lecturer.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the lecturer profile associated with this allocation.
     *
     * @return BelongsTo<Lecturer, $this>
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }

    /**
     * Scope a query to filter allocations by lecturer ID.
     *
     * @param Builder<AttachmentLecturer> $query
     * @return Builder<AttachmentLecturer>
     */
    public function scopeForLecturer(Builder $query, int $lecturerId): Builder
    {
        return $query->where('lecturer_id', $lecturerId);
    }

    /**
     * Scope a query to filter allocations by attachment session ID.
     *
     * @param Builder<AttachmentLecturer> $query
     * @return Builder<AttachmentLecturer>
     */
    public function scopeForAttachment(Builder $query, int $attachmentId): Builder
    {
        return $query->where('attachment_id', $attachmentId);
    }
}