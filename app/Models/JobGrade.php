<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class JobGrade extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_grades';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'public_service_group',
        'dekut_grade',
        'designation',
        'daily_allowance',
        'applies_to',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'daily_allowance' => 'float',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * Get the attachment lecturers associated with this job grade.
     *
     * @return HasMany<AttachmentLecturer, $this>
     */
    public function attachmentLecturers(): HasMany
    {
        return $this->hasMany(AttachmentLecturer::class, 'job_grade_id');
    }

    /**
     * Scope a query to filter job grades by DeKUT grade code.
     *
     * @param Builder<JobGrade> $query
     * @return Builder<JobGrade>
     */
    public function scopeForGrade(Builder $query, string $grade): Builder
    {
        return $query->where('dekut_grade', $grade);
    }

    /**
     * Scope a query to filter job grades by target domain/role.
     *
     * @param Builder<JobGrade> $query
     * @return Builder<JobGrade>
     */
    public function scopeAppliesTo(Builder $query, string $target): Builder
    {
        return $query->where('applies_to', $target);
    }
}