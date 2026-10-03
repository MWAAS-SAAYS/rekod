<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class AttachmentStudent extends Model
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
        'attachment_id'            => 'integer',
        'student_id'               => 'integer',
        'company_id'               => 'integer',
        'lecturer_id'              => 'integer',
        'attachment_lecturer_id'   => 'integer',
        'industrial_supervisor_id' => 'integer',
    ];

    /**
     * Get the overarching attachment session.
     *
     * @return BelongsTo<Attachment, $this>
     */
    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }

    /**
     * Get the student profile linked to this placement.
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the user account of the attached student.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the host company for the industrial attachment.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the assigned visiting academic lecturer.
     *
     * @return BelongsTo<Lecturer, $this>
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }

    /**
     * Get the attachment lecturer allocation instance.
     *
     * @return BelongsTo<AttachmentLecturer, $this>
     */
    public function attachmentLecturer(): BelongsTo
    {
        return $this->belongsTo(AttachmentLecturer::class, 'attachment_lecturer_id');
    }

    /**
     * Get the designated industrial supervisor at the host company.
     *
     * @return BelongsTo<IndustrialSupervisor, $this>
     */
    public function industrialSupervisor(): BelongsTo
    {
        return $this->belongsTo(IndustrialSupervisor::class, 'industrial_supervisor_id');
    }

    /**
     * Get all daily logbook reports submitted by the student.
     *
     * @return HasMany<DailyReport, $this>
     */
    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'attachment_student_id');
    }

    /**
     * Get all weekly summary reports submitted by the student.
     *
     * @return HasMany<WeeklyReport, $this>
     */
    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class, 'attachment_student_id');
    }

    /**
     * Get the academic program (AdministrativeUnit) through the student.
     *
     * @return HasOneThrough<AdministrativeUnit, Student, $this>
     */
    public function program(): HasOneThrough
    {
        return $this->hasOneThrough(
            AdministrativeUnit::class,
            Student::class,
            'id',         // Foreign key on Student table
            'id',         // Foreign key on AdministrativeUnit table
            'student_id', // Local key on AttachmentStudent table
            'program_id'  // Local key on Student table
        )->with('parent');
    }

    /**
     * Accessor to retrieve the parent academic department via program hierarchy.
     */
    public function getDepartmentAttribute(): ?AdministrativeUnit
    {
        return $this->student?->program?->parent;
    }

    /**
     * Scope a query to filter placements by student ID.
     *
     * @param Builder<AttachmentStudent> $query
     * @return Builder<AttachmentStudent>
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to filter placements by attachment session ID.
     *
     * @param Builder<AttachmentStudent> $query
     * @return Builder<AttachmentStudent>
     */
    public function scopeForAttachment(Builder $query, int $attachmentId): Builder
    {
        return $query->where('attachment_id', $attachmentId);
    }

    /**
     * Scope a query to filter placements by assigned lecturer ID.
     *
     * @param Builder<AttachmentStudent> $query
     * @return Builder<AttachmentStudent>
     */
    public function scopeForLecturer(Builder $query, int $lecturerId): Builder
    {
        return $query->where('lecturer_id', $lecturerId);
    }
}