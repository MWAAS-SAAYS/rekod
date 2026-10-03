<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdministrativeUnit extends Model
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
        'level' => 'integer',
    ];

    /**
     * Get the parent administrative unit.
     *
     * @return BelongsTo<AdministrativeUnit, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_code', 'code');
    }

    /**
     * Get the child administrative units.
     *
     * @return HasMany<AdministrativeUnit, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_code', 'code');
    }

    /**
     * Scope a query to filter units by hierarchical level.
     *
     * @param Builder<AdministrativeUnit> $query
     * @return Builder<AdministrativeUnit>
     */
    public function scopeLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to filter Level 2 (Department) units.
     *
     * @param Builder<AdministrativeUnit> $query
     * @return Builder<AdministrativeUnit>
     */
    public function scopeDepartments(Builder $query): Builder
    {
        return $query->where('level', 2);
    }

    /**
     * Scope a query to filter Level 3 (Programme) units.
     *
     * @param Builder<AdministrativeUnit> $query
     * @return Builder<AdministrativeUnit>
     */
    public function scopeProgrammes(Builder $query): Builder
    {
        return $query->where('level', 3);
    }
}