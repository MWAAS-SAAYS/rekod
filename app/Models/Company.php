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
 * @property string $name
 * @property string|null $registration_no
 * @property string|null $kra_pin
 * @property bool $kra_verified
 * @property \Illuminate\Support\Carbon|null $kra_verified_at
 * @property string|null $sector
 * @property array|null $sub_sectors
 * @property array|null $physical_address
 * @property string|null $address
 * @property string|null $contact_person
 * @property string|null $contact_phone
 * @property string|null $email
 * @property string|null $website
 * @property string|null $description
 * @property string|null $logo_url
 * @property string $vetting_status
 * @property string|null $vetting_notes
 * @property int|null $vetted_by
 * @property \Illuminate\Support\Carbon|null $vetted_at
 * @property string $plan_tier
 * @property \Illuminate\Support\Carbon|null $plan_expires_at
 * @property int|null $county_id
 * @property int|null $sub_county_id
 * @property int|null $town_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Location|null $county
 * @property-read Location|null $subcounty
 * @property-read Location|null $town
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Opportunity> $opportunities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AttachmentStudent> $attachmentStudents
 * @property-read User|null $vetter
 * @method static Builder<static>|Company vetted()
 * @method static Builder<static>|Company pendingVetting()
 * @method static Builder<static>|Company planTier(string $tier)
 * @method static Builder<static>|Company sector(string $sector)
 */
class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'registration_no',
        'kra_pin',
        'kra_verified',
        'kra_verified_at',
        'sector',
        'sub_sectors',
        'physical_address',
        'address',
        'contact_person',
        'contact_phone',
        'email',
        'website',
        'description',
        'logo_url',
        'vetting_status',
        'vetting_notes',
        'vetted_by',
        'vetted_at',
        'plan_tier',
        'plan_expires_at',
        'county_id',
        'sub_county_id',
        'town_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'county_id'        => 'integer',
        'sub_county_id'    => 'integer',
        'town_id'          => 'integer',
        'kra_verified'     => 'boolean',
        'kra_verified_at'  => 'datetime',
        'sub_sectors'      => 'array',
        'physical_address' => 'array',
        'vetted_at'        => 'datetime',
        'plan_expires_at'  => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
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
     * Get the user who vetted this company.
     */
    public function vetter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vetted_by');
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
     * Get company documents.
     *
     * @return HasMany<CompanyDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class, 'company_id');
    }

    /**
     * Scope a query to filter companies by county ID.
     */
    public function scopeInCounty(Builder $query, int $countyId): Builder
    {
        return $query->where('county_id', $countyId);
    }

    /**
     * Scope a query to filter companies by sub-county ID.
     */
    public function scopeInSubCounty(Builder $query, int $subCountyId): Builder
    {
        return $query->where('sub_county_id', $subCountyId);
    }

    /**
     * Scope a query to filter companies by town ID.
     */
    public function scopeInTown(Builder $query, int $townId): Builder
    {
        return $query->where('town_id', $townId);
    }

    /**
     * Scope a query to filter vetted companies.
     */
    public function scopeVetted(Builder $query): Builder
    {
        return $query->where('vetting_status', 'approved');
    }

    /**
     * Scope a query to filter companies pending vetting.
     */
    public function scopePendingVetting(Builder $query): Builder
    {
        return $query->where('vetting_status', 'pending');
    }

    /**
     * Scope a query to filter companies by plan tier.
     */
    public function scopePlanTier(Builder $query, string $tier): Builder
    {
        return $query->where('plan_tier', $tier);
    }

    /**
     * Scope a query to filter companies by sector.
     */
    public function scopeSector(Builder $query, string $sector): Builder
    {
        return $query->where('sector', $sector);
    }

    /**
     * Check if company has active paid plan.
     */
    public function hasActivePlan(): bool
    {
        if ($this->plan_tier === 'free') {
            return false;
        }
        if ($this->plan_expires_at && $this->plan_expires_at->isPast()) {
            return false;
        }
        return true;
    }
}