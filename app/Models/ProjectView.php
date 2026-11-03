<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $project_id
 * @property int $company_id
 * @property \Illuminate\Support\Carbon $viewed_at
 * @property string $plan_tier_at_view
 * @property bool $nda_accepted
 * @property \Illuminate\Support\Carbon|null $nda_accepted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read StudentProject $project
 * @property-read User $company
 * @method static Builder<static>|ProjectView forProject(int $projectId)
 * @method static Builder<static>|ProjectView forCompany(int $companyId)
 * @method static Builder<static>|ProjectView withNdaAccepted(bool $accepted = true)
 * @method static Builder<static>|ProjectView viewedBetween(\Illuminate\Support\Carbon $start, \Illuminate\Support\Carbon $end)
 */
final class ProjectView extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'project_views';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'company_id',
        'plan_tier_at_view',
        'nda_accepted',
        'nda_accepted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'project_id'         => 'integer',
        'company_id'         => 'integer',
        'plan_tier_at_view'  => 'string',
        'nda_accepted'       => 'boolean',
        'nda_accepted_at'    => 'datetime',
        'viewed_at'          => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    /**
     * Get the student project that was viewed.
     *
     * @return BelongsTo<StudentProject, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(StudentProject::class, 'project_id');
    }

    /**
     * Get the company (user) that viewed the project.
     *
     * @return BelongsTo<User, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    /**
     * Scope a query to filter views by project ID.
     *
     * @param Builder<ProjectView> $query
     * @return Builder<ProjectView>
     */
    public function scopeForProject(Builder $query, int $projectId): Builder
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope a query to filter views by company ID.
     *
     * @param Builder<ProjectView> $query
     * @return Builder<ProjectView>
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to filter views by NDA acceptance status.
     *
     * @param Builder<ProjectView> $query
     * @return Builder<ProjectView>
     */
    public function scopeWithNdaAccepted(Builder $query, bool $accepted = true): Builder
    {
        return $query->where('nda_accepted', $accepted);
    }

    /**
     * Scope a query to filter views by date range.
     *
     * @param Builder<ProjectView> $query
     * @return Builder<ProjectView>
     */
    public function scopeViewedBetween(Builder $query, \Illuminate\Support\Carbon $start, \Illuminate\Support\Carbon $end): Builder
    {
        return $query->whereBetween('viewed_at', [$start, $end]);
    }

    /**
     * Mark NDA as accepted for this view.
     */
    public function acceptNda(): void
    {
        $this->update([
            'nda_accepted'    => true,
            'nda_accepted_at' => now(),
        ]);
    }
}