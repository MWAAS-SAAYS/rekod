<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalReport extends Model
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
        'attachment_student_id' => 'integer',
        'is_submitted'          => 'boolean',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
    ];

    /**
     * Get the student attachment placement associated with this final report.
     *
     * @return BelongsTo<AttachmentStudent, $this>
     */
    public function attachmentStudent(): BelongsTo
    {
        return $this->belongsTo(AttachmentStudent::class, 'attachment_student_id');
    }

    /**
     * Scope a query to filter final reports by attachment student ID.
     *
     * @param Builder<FinalReport> $query
     * @return Builder<FinalReport>
     */
    public function scopeForStudent(Builder $query, int $attachmentStudentId): Builder
    {
        return $query->where('attachment_student_id', $attachmentStudentId);
    }

    /**
     * Scope a query to include only submitted final reports.
     *
     * @param Builder<FinalReport> $query
     * @return Builder<FinalReport>
     */
    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('is_submitted', true);
    }

    /**
     * Scope a query to include only draft (unsubmitted) final reports.
     *
     * @param Builder<FinalReport> $query
     * @return Builder<FinalReport>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('is_submitted', false);
    }
}