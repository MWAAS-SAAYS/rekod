<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attachment_lecturer_id',
        'user_id',
        'students_count',
        'days',
        'transport',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attachment_lecturer_id' => 'integer',
        'user_id'                => 'integer',
        'students_count'         => 'integer',
        'days'                   => 'integer',
        'transport'              => 'float',
    ];

    /**
     * Get the lecturer attachment allocation associated with this budget.
     *
     * @return BelongsTo<AttachmentLecturer, $this>
     */
    public function attachmentLecturer(): BelongsTo
    {
        return $this->belongsTo(AttachmentLecturer::class, 'attachment_lecturer_id');
    }

    /**
     * Get the user account associated with this budget entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the student attachment records under the same lecturer allocation.
     *
     * @return HasMany<AttachmentStudent, $this>
     */
    public function attachmentStudents(): HasMany
    {
        return $this->hasMany(AttachmentStudent::class, 'attachment_lecturer_id', 'attachment_lecturer_id');
    }

    /**
     * Scope a query to filter budgets by lecturer attachment allocation ID.
     *
     * @param Builder<Budget> $query
     * @return Builder<Budget>
     */
    public function scopeForAttachmentLecturer(Builder $query, int $attachmentLecturerId): Builder
    {
        return $query->where('attachment_lecturer_id', $attachmentLecturerId);
    }

    /**
     * Scope a query to filter budgets by user ID.
     *
     * @param Builder<Budget> $query
     * @return Builder<Budget>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}