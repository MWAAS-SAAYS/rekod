<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Student extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'students';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'program_id',
        'supervisor_id',
        'reg_no',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id'       => 'integer',
        'program_id'    => 'integer',
        'supervisor_id' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Get the user account associated with this student.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the academic supervisor associated with this student.
     *
     * @return BelongsTo<User, $this>
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get the academic program unit associated with this student.
     *
     * @return BelongsTo<AdministrativeUnit, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(AdministrativeUnit::class, 'program_id');
    }

    /**
     * Get the primary attachment session linked to this student.
     *
     * @return HasOne<AttachmentStudent, $this>
     */
    public function attachmentStudent(): HasOne
    {
        return $this->hasOne(AttachmentStudent::class, 'student_id');
    }

    /**
     * Get all attachment sessions linked to this student.
     *
     * @return HasMany<AttachmentStudent, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AttachmentStudent::class, 'student_id');
    }

    /**
     * Get weekly logbook reports submitted by this student.
     *
     * @return HasMany<WeeklyReport, $this>
     */
    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class, 'student_id');
    }

    /**
     * Scope a query to filter students by user account ID.
     *
     * @param Builder<Student> $query
     * @return Builder<Student>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter students by academic program unit ID.
     *
     * @param Builder<Student> $query
     * @return Builder<Student>
     */
    public function scopeForProgram(Builder $query, int $programId): Builder
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Scope a query to filter students by registration number.
     *
     * @param Builder<Student> $query
     * @return Builder<Student>
     */
    public function scopeRegistrationNumber(Builder $query, string $regNo): Builder
    {
        return $query->where('reg_no', $regNo);
    }
}