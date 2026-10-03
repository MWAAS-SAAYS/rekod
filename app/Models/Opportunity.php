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
        'expiry_date',
        'program_code',
        'is_paid',
        'slots_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'company_id'  => 'integer',
        'expiry_date' => 'date',
        'is_paid'     => 'boolean',
        'slots_count' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
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
     * Check if the opportunity deadline has passed.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date ? $this->expiry_date->isPast() : false;
    }
}