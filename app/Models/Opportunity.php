<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Opportunity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'opportunities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'link',
        'location',
        'location_type',
        'expiry_date',
        'program_code',
        'is_paid',
        'stipend_min',
        'stipend_max',
        'duration_months',
        'slots_total',
        'slots_filled',
        'field_tags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'company_id'    => 'integer',
        'expiry_date'   => 'date',
        'is_paid'       => 'boolean',
        'stipend_min'   => 'decimal:2',
        'stipend_max'   => 'decimal:2',
        'duration_months' => 'integer',
        'slots_total'   => 'integer',
        'slots_filled'  => 'integer',
        'slots_count'   => 'integer',
        'field_tags'    => 'array',
        'location_type' => 'string',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Get the company user owner of the opportunity.
     *
     * @return BelongsTo<User, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    /**
     * Get applicants for the opportunity.
     *
     * @return BelongsToMany<User, $this>
     */
    public function applicants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'opportunity_applications', 'opportunity_id', 'user_id')
            ->withPivot(['cover_letter', 'cv_path', 'applied_at'])
            ->withTimestamps();
    }

    /**
     * Get application records for the opportunity.
     *
     * @return HasMany<Application, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'opportunity_id', 'id');
    }

    /**
     * Scope a query to fetch active non-expired opportunities.
     *
     * @param Builder<Opportunity> $query
     * @return Builder<Opportunity>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('expiry_date', '>=', Carbon::today());
    }

    /**
     * Scope a query to filter opportunities by program code.
     *
     * @param Builder<Opportunity> $query
     * @return Builder<Opportunity>
     */
    public function scopeForProgram(Builder $query, string $programCode): Builder
    {
        return $query->where('program_code', $programCode);
    }

    /**
     * Scope a query to filter paid opportunities.
     *
     * @param Builder<Opportunity> $query
     * @return Builder<Opportunity>
     */
    public function scopePaid(Builder $query, bool $isPaid = true): Builder
    {
        return $query->where('is_paid', $isPaid);
    }

    /**
     * Scope a query to filter opportunities by location type.
     *
     * @param Builder<Opportunity> $query
     * @return Builder<Opportunity>
     */
    public function scopeByLocationType(Builder $query, string $type): Builder
    {
        return $query->where('location_type', $type);
    }

    /**
     * Scope a query to filter opportunities with stipend range.
     *
     * @param Builder<Opportunity> $query
     * @return Builder<Opportunity>
     */
    public function scopeWithStipendRange(Builder $query, float $min = 0, float $max = PHP_FLOAT_MAX): Builder
    {
        return $query->where(function (Builder $q) use ($min, $max) {
            $q->where('stipend_min', '>=', $min)
                ->orWhere('stipend_max', '<=', $max);
        });
    }

    /**
     * Scope a query to filter opportunities by field tags.
     *
     * @param Builder<Opportunity> $query
     * @return Builder<Opportunity>
     */
    public function scopeWithFieldTags(Builder $query, array $tags): Builder
    {
        return $query->whereJsonContains('field_tags', $tags);
    }

    /**
     * Scope a query to filter opportunities with available slots.
     *
     * @param Builder<Opportunity> $query
     * @return Builder<Opportunity>
     */
    public function scopeWithAvailableSlots(Builder $query): Builder
    {
        return $query->whereRaw('slots_filled < slots_total');
    }

    /**
     * Check if the opportunity deadline has passed.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date ? $this->expiry_date->isPast() : false;
    }

    /**
     * Get the number of available slots.
     */
    public function getAvailableSlotsAttribute(): int
    {
        return max(0, ($this->slots_total ?? $this->slots_count ?? 0) - ($this->slots_filled ?? 0));
    }

    /**
     * Check if the opportunity has available slots.
     */
    public function getHasAvailableSlotsAttribute(): bool
    {
        return $this->available_slots > 0;
    }

    /**
     * Get the stipend range as a formatted string.
     */
    public function getStipendRangeAttribute(): ?string
    {
        if (!$this->stipend_min && !$this->stipend_max) {
            return null;
        }
        if ($this->stipend_min && $this->stipend_max && $this->stipend_min == $this->stipend_max) {
            return 'KES ' . number_format($this->stipend_min, 2);
        }
        $min = $this->stipend_min ? 'KES ' . number_format($this->stipend_min, 2) : 'Negotiable';
        $max = $this->stipend_max ? 'KES ' . number_format($this->stipend_max, 2) : 'Negotiable';
        return "{$min} - {$max}";
    }

    /**
     * Increment the filled slots count.
     */
    public function incrementFilledSlots(): void
    {
        $this->increment('slots_filled');
    }

    /**
     * Decrement the filled slots count.
     */
    public function decrementFilledSlots(): void
    {
        $this->decrement('slots_filled');
    }
}