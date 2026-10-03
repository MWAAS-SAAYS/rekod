<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
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
        'county_id'     => 'integer',
        'sub_county_id' => 'integer',
        'town_id'       => 'integer',
    ];

    /**
     * Get the county location for the company.
     *
     * @return BelongsTo<Location, $this>
     */
    public function county(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'county_id');
    }

    /**
     * Get the sub-county location for the company.
     *
     * @return BelongsTo<Location, $this>
     */
    public function subcounty(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'sub_county_id');
    }

    /**
     * Get the town location for the company.
     *
     * @return BelongsTo<Location, $this>
     */
    public function town(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'town_id');
    }

    /**
     * Alias for town location.
     *
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->town();
    }

    /**
     * Get the opportunities listed by this company.
     *
     * @return HasMany<Opportunity, $this>
     */
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'company_id');
    }

    /**
     * Get all student attachment placements hosted by this company.
     *
     * @return HasMany<AttachmentStudent, $this>
     */
    public function attachmentStudents(): HasMany
    {
        return $this->hasMany(AttachmentStudent::class, 'company_id');
    }

    /**
     * Scope a query to filter companies by county ID.
     *
     * @param Builder<Company> $query
     * @return Builder<Company>
     */
    public function scopeInCounty(Builder $query, int $countyId): Builder
    {
        return $query->where('county_id', $countyId);
    }

    /**
     * Scope a query to filter companies by sub-county ID.
     *
     * @param Builder<Company> $query
     * @return Builder<Company>
     */
    public function scopeInSubCounty(Builder $query, int $subCountyId): Builder
    {
        return $query->where('sub_county_id', $subCountyId);
    }

    /**
     * Scope a query to filter companies by town ID.
     *
     * @param Builder<Company> $query
     * @return Builder<Company>
     */
    public function scopeInTown(Builder $query, int $townId): Builder
    {
        return $query->where('town_id', $townId);
    }
}