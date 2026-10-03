<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndustrialSupervisor extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'industrial_supervisors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id'    => 'integer',
        'company_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user account associated with this industrial supervisor.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the company associated with this industrial supervisor.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the student attachment placements assigned to this industrial supervisor.
     *
     * @return HasMany<AttachmentStudent, $this>
     */
    public function attachmentStudents(): HasMany
    {
        return $this->hasMany(AttachmentStudent::class, 'industrial_supervisor_id');
    }

    /**
     * Scope a query to filter industrial supervisors by user ID.
     *
     * @param Builder<IndustrialSupervisor> $query
     * @return Builder<IndustrialSupervisor>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter industrial supervisors by company ID.
     *
     * @param Builder<IndustrialSupervisor> $query
     * @return Builder<IndustrialSupervisor>
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}