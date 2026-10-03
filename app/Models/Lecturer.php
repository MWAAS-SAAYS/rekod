<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Lecturer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lecturers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'department_id',
        'staff_number',
        'job_grade',
        'phone_alt',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id'       => 'integer',
        'department_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Get the user account associated with this lecturer.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the administrative department associated with this lecturer.
     *
     * @return BelongsTo<AdministrativeUnit, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(AdministrativeUnit::class, 'department_id');
    }

    /**
     * Get the job grade associated with this lecturer.
     *
     * @return BelongsTo<JobGrade, $this>
     */
    public function jobGrade(): BelongsTo
    {
        return $this->belongsTo(JobGrade::class, 'job_grade', 'dekut_grade');
    }

    /**
     * Get the budget claims associated with this lecturer.
     *
     * @return HasMany<Budget, $this>
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get student attachment assignments linked to this lecturer.
     *
     * @return HasMany<AttachmentStudent, $this>
     */
    public function attachmentStudents(): HasMany
    {
        return $this->hasMany(AttachmentStudent::class, 'attachment_lecturer_id');
    }

    /**
     * Get attachment session allocations assigned to this lecturer.
     *
     * @return HasMany<AttachmentLecturer, $this>
     */
    public function attachmentLecturers(): HasMany
    {
        return $this->hasMany(AttachmentLecturer::class, 'lecturer_id');
    }

    /**
     * Scope a query to filter lecturers by user ID.
     *
     * @param Builder<Lecturer> $query
     * @return Builder<Lecturer>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter lecturers by department ID.
     *
     * @param Builder<Lecturer> $query
     * @return Builder<Lecturer>
     */
    public function scopeForDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }
}