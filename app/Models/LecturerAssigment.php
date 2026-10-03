<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LecturerAssigment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lecturer_assignments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attachment_lecturer_id',
        'attachment_student_id',
        'company_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attachment_lecturer_id' => 'integer',
        'attachment_student_id'  => 'integer',
        'company_id'             => 'integer',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    /**
     * Get the attachment lecturer assigned to this record.
     *
     * @return BelongsTo<AttachmentLecturer, $this>
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(AttachmentLecturer::class, 'attachment_lecturer_id');
    }

    /**
     * Get the attachment student assigned to the lecturer.
     *
     * @return BelongsTo<AttachmentStudent, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(AttachmentStudent::class, 'attachment_student_id');
    }

    /**
     * Get the company location where this assignment takes place.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Scope a query to filter assignments by attachment lecturer ID.
     *
     * @param Builder<LecturerAssigment> $query
     * @return Builder<LecturerAssigment>
     */
    public function scopeForLecturer(Builder $query, int $lecturerId): Builder
    {
        return $query->where('attachment_lecturer_id', $lecturerId);
    }

    /**
     * Scope a query to filter assignments by attachment student ID.
     *
     * @param Builder<LecturerAssigment> $query
     * @return Builder<LecturerAssigment>
     */
    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('attachment_student_id', $studentId);
    }

    /**
     * Scope a query to filter assignments by company ID.
     *
     * @param Builder<LecturerAssigment> $query
     * @return Builder<LecturerAssigment>
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}