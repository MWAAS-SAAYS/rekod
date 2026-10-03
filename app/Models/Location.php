<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Location extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'parent_code',
        'level',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'level'      => 'integer',
        'latitude'   => 'float',
        'longitude'  => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent location record.
     *
     * @return BelongsTo<Location, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_code', 'code');
    }

    /**
     * Get the child locations nested under this location.
     *
     * @return HasMany<Location, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_code', 'code');
    }

    /**
     * Get companies associated with this location.
     *
     * @return HasMany<Company, $this>
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'location_code', 'code');
    }

    /**
     * Scope a query to filter locations by administrative or geographic level.
     *
     * @param Builder<Location> $query
     * @return Builder<Location>
     */
    public function scopeLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to filter locations by location code.
     *
     * @param Builder<Location> $query
     * @return Builder<Location>
     */
    public function scopeCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    /**
     * Scope a query to filter child locations by parent code.
     *
     * @param Builder<Location> $query
     * @return Builder<Location>
     */
    public function scopeByParent(Builder $query, string $parentCode): Builder
    {
        return $query->where('parent_code', $parentCode);
    }
}