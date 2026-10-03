<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attachment extends Model
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
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    /**
     * Get the student records attached to this attachment session.
     *
     * @return HasMany<AttachmentStudent, $this>
     */
    public function attachmentStudents(): HasMany
    {
        return $this->hasMany(AttachmentStudent::class, 'attachment_id');
    }

    /**
     * Get the lecturer allocations for this attachment session.
     *
     * @return HasMany<AttachmentLecturer, $this>
     */
    public function attachmentLecturers(): HasMany
    {
        return $this->hasMany(AttachmentLecturer::class, 'attachment_id');
    }

    /**
     * Get the opportunities listed under this attachment session.
     *
     * @return HasMany<Opportunity, $this>
     */
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'attachment_id');
    }

    /**
     * Scope a query to filter attachments by slug.
     *
     * @param Builder<Attachment> $query
     * @return Builder<Attachment>
     */
    public function scopeSlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope a query to include only active attachments.
     *
     * @param Builder<Attachment> $query
     * @return Builder<Attachment>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}