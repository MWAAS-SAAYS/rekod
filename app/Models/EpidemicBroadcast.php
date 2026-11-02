<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $title
 * @property string $message
 * @property array|null $target_skills
 * @property array|null $target_counties
 * @property int $sent_count
 * @property int $delivered_count
 * @property int $sent_by
 * @property \Illuminate\Support\Carbon $sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $sender
 * @method static Builder<static>|EpidemicBroadcast recent()
 * @method static Builder<static>|EpidemicBroadcast sentBy(int $userId)
 * @method static Builder<static>|EpidemicBroadcast targetingSkills(array $skills)
 * @method static Builder<static>|EpidemicBroadcast targetingCounties(array $counties)
 */
final class EpidemicBroadcast extends Model
{
    use HasFactory;

    protected $table = 'epidemic_broadcasts';

    protected $fillable = [
        'title',
        'message',
        'target_skills',
        'target_counties',
        'sent_count',
        'delivered_count',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'target_skills' => 'array',
        'target_counties' => 'array',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('sent_at', 'desc');
    }

    public function scopeSentBy(Builder $query, int $userId): Builder
    {
        return $query->where('sent_by', $userId);
    }

    public function scopeTargetingSkills(Builder $query, array $skills): Builder
    {
        return $query->where(function ($q) use ($skills) {
            foreach ($skills as $skill) {
                $q->orWhereJsonContains('target_skills', $skill);
            }
        });
    }

    public function scopeTargetingCounties(Builder $query, array $counties): Builder
    {
        return $query->where(function ($q) use ($counties) {
            foreach ($counties as $county) {
                $q->orWhereJsonContains('target_counties', $county);
            }
        });
    }

    public function getDeliveryRate(): float
    {
        if ($this->sent_count === 0) {
            return 0.0;
        }
        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    public function incrementSentCount(int $count = 1): void
    {
        $this->increment('sent_count', $count);
    }

    public function incrementDeliveredCount(int $count = 1): void
    {
        $this->increment('delivered_count', $count);
    }

    public function getTargetSkills(): array
    {
        return $this->target_skills ?? [];
    }

    public function getTargetCounties(): array
    {
        return $this->target_counties ?? [];
    }

    public function hasTargeting(): bool
    {
        return !empty($this->target_skills) || !empty($this->target_counties);
    }
}