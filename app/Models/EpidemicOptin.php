<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $professional_type
 * @property array|null $skills
 * @property array|null $counties
 * @property \Illuminate\Support\Carbon $optin_at
 * @property \Illuminate\Support\Carbon|null $optout_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @method static Builder<static>|EpidemicOptin active()
 * @method static Builder<static>|EpidemicOptin inactive()
 * @method static Builder<static>|EpidemicOptin professionalType(string $type)
 * @method static Builder<static>|EpidemicOptin withSkill(string $skill)
 * @method static Builder<static>|EpidemicOptin inCounty(string $county)
 * @method static Builder<static>|EpidemicOptin forUser(int $userId)
 */
final class EpidemicOptin extends Model
{
    use HasFactory;

    protected $table = 'epidemic_optins';

    protected $fillable = [
        'user_id',
        'professional_type',
        'skills',
        'counties',
        'optin_at',
        'optout_at',
        'is_active',
    ];

    protected $casts = [
        'skills' => 'array',
        'counties' => 'array',
        'optin_at' => 'datetime',
        'optout_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeProfessionalType(Builder $query, string $type): Builder
    {
        return $query->where('professional_type', $type);
    }

    public function scopeWithSkill(Builder $query, string $skill): Builder
    {
        return $query->whereJsonContains('skills', $skill);
    }

    public function scopeInCounty(Builder $query, string $county): Builder
    {
        return $query->whereJsonContains('counties', $county);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function optOut(): void
    {
        $this->update([
            'is_active' => false,
            'optout_at' => now(),
        ]);
    }

    public function hasSkill(string $skill): bool
    {
        return in_array($skill, $this->skills ?? []);
    }

    public function hasCounty(string $county): bool
    {
        return in_array($county, $this->counties ?? []);
    }

    public function matchesCriteria(array $targetSkills, array $targetCounties): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $skillMatch = empty($targetSkills) || array_intersect($targetSkills, $this->skills ?? []);
        $countyMatch = empty($targetCounties) || array_intersect($targetCounties, $this->counties ?? []);

        return $skillMatch && $countyMatch;
    }

    /**
     * Get all active opt-ins matching skills and counties.
     */
    public static function getMatching(array $skills, array $counties): Builder
    {
        return static::active()
            ->when($skills, fn(Builder $q) => $q->where(function ($q) use ($skills) {
                foreach ($skills as $skill) {
                    $q->orWhereJsonContains('skills', $skill);
                }
            }))
            ->when($counties, fn(Builder $q) => $q->where(function ($q) use ($counties) {
                foreach ($counties as $county) {
                    $q->orWhereJsonContains('counties', $county);
                }
            }));
    }
}