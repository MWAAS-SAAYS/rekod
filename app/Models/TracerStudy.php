<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $alumni_id
 * @property string $survey_round
 * @property string|null $employment_status
 * @property string|null $current_role
 * @property string|null $current_company
 * @property string|null $sector
 * @property string|null $salary_band
 * @property int|null $satisfaction_score
 * @property int|null $relevance_score
 * @property string|null $feedback
 * @property bool $consent_research
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $alumni
 * @method static Builder<static>|TracerStudy round(string $round)
 * @method static Builder<static>|TracerStudy employmentStatus(string $status)
 * @method static Builder<static>|TracerStudy sector(string $sector)
 * @method static Builder<static>|TracerStudy withConsent()
 * @method static Builder<static>|TracerStudy forAlumni(int $alumniId)
 */
final class TracerStudy extends Model
{
    use HasFactory;

    protected $table = 'tracer_studies';

    protected $fillable = [
        'alumni_id',
        'survey_round',
        'employment_status',
        'current_role',
        'current_company',
        'sector',
        'salary_band',
        'satisfaction_score',
        'relevance_score',
        'feedback',
        'consent_research',
        'submitted_at',
    ];

    protected $casts = [
        'satisfaction_score' => 'integer',
        'relevance_score' => 'integer',
        'consent_research' => 'boolean',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alumni_id');
    }

    public function scopeRound(Builder $query, string $round): Builder
    {
        return $query->where('survey_round', $round);
    }

    public function scopeEmploymentStatus(Builder $query, string $status): Builder
    {
        return $query->where('employment_status', $status);
    }

    public function scopeSector(Builder $query, string $sector): Builder
    {
        return $query->where('sector', $sector);
    }

    public function scopeWithConsent(Builder $query): Builder
    {
        return $query->where('consent_research', true);
    }

    public function scopeForAlumni(Builder $query, int $alumniId): Builder
    {
        return $query->where('alumni_id', $alumniId);
    }

    public function isEmployed(): bool
    {
        return in_array($this->employment_status, ['employed', 'self_employed']);
    }

    public function isStudying(): bool
    {
        return $this->employment_status === 'studying';
    }

    public function isUnemployed(): bool
    {
        return $this->employment_status === 'unemployed';
    }

    public function hasPositiveSatisfaction(): bool
    {
        return $this->satisfaction_score !== null && $this->satisfaction_score >= 4;
    }

    public function hasPositiveRelevance(): bool
    {
        return $this->relevance_score !== null && $this->relevance_score >= 4;
    }

    public function getSalaryBand(): ?string
    {
        return $this->salary_band;
    }

    public function getAverageScore(): ?float
    {
        if ($this->satisfaction_score === null && $this->relevance_score === null) {
            return null;
        }
        $scores = array_filter([$this->satisfaction_score, $this->relevance_score]);
        return array_sum($scores) / count($scores);
    }

    /**
     * Check if alumni has completed a specific survey round.
     */
    public static function hasCompletedRound(int $alumniId, string $round): bool
    {
        return static::forAlumni($alumniId)
            ->round($round)
            ->exists();
    }
}