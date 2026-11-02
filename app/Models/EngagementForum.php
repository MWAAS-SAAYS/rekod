<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $event_type
 * @property \Illuminate\Support\Carbon $event_date
 * @property int $duration_minutes
 * @property string|null $location
 * @property int $organizer_id
 * @property array|null $target_fields
 * @property int|null $max_attendees
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $organizer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ForumRegistration> $registrations
 * @method static Builder<static>|EngagementForum scheduled()
 * @method static Builder<static>|EngagementForum live()
 * @method static Builder<static>|EngagementForum completed()
 * @method static Builder<static>|EngagementForum cancelled()
 * @method static Builder<static>|EngagementForum upcoming()
 * @method static Builder<static>|EngagementForum past()
 * @method static Builder<static>|EngagementForum eventType(string $type)
 * @method static Builder<static>|EngagementForum organizedBy(int $userId)
 */
final class EngagementForum extends Model
{
    use HasFactory;

    protected $table = 'engagement_forums';

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'event_date',
        'duration_minutes',
        'location',
        'organizer_id',
        'target_fields',
        'max_attendees',
        'status',
    ];

    protected $casts = [
        'target_fields' => 'array',
        'duration_minutes' => 'integer',
        'max_attendees' => 'integer',
        'event_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(ForumRegistration::class, 'forum_id');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', 'live');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('event_date', '>=', now())
            ->whereIn('status', ['scheduled', 'live'])
            ->orderBy('event_date');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('event_date', '<', now())
            ->orWhere('status', 'completed')
            ->orderByDesc('event_date');
    }

    public function scopeEventType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }

    public function scopeOrganizedBy(Builder $query, int $userId): Builder
    {
        return $query->where('organizer_id', $userId);
    }

    public function isUpcoming(): bool
    {
        return $this->event_date->isFuture() && in_array($this->status, ['scheduled', 'live']);
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isPast(): bool
    {
        return $this->event_date->isPast() || $this->status === 'completed';
    }

    public function getAttendeeCount(): int
    {
        return $this->registrations()->count();
    }

    public function getAttendedCount(): int
    {
        return $this->registrations()->where('attended', true)->count();
    }

    public function hasCapacity(): bool
    {
        if ($this->max_attendees === null) {
            return true;
        }
        return $this->getAttendeeCount() < $this->max_attendees;
    }

    public function getTargetFieldIds(): array
    {
        return $this->target_fields ?? [];
    }
}