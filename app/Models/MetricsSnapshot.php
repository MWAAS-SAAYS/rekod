<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $snapshot_date
 * @property string $metric_type
 * @property string|null $dimension
 * @property string|null $dimension_value
 * @property string $value
 * @property int|null $sample_size
 * @property string|null $anonymization_method
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder<static>|MetricsSnapshot forDate(\Illuminate\Support\Carbon $date)
 * @method static Builder<static>|MetricsSnapshot forDateRange(\Illuminate\Support\Carbon $start, \Illuminate\Support\Carbon $end)
 * @method static Builder<static>|MetricsSnapshot byMetricType(string $type)
 * @method static Builder<static>|MetricsSnapshot byDimension(string $dimension)
 * @method static Builder<static>|MetricsSnapshot byDimensionValue(string $value)
 * @method static Builder<static>|MetricsSnapshot latestSnapshot()
 */
final class MetricsSnapshot extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'metrics_snapshots';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'snapshot_date',
        'metric_type',
        'dimension',
        'dimension_value',
        'value',
        'sample_size',
        'anonymization_method',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'snapshot_date'        => 'date',
        'metric_type'          => 'string',
        'dimension'            => 'string',
        'dimension_value'      => 'string',
        'value'                => 'decimal:4',
        'sample_size'          => 'integer',
        'anonymization_method' => 'string',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    /**
     * Scope a query to filter snapshots by specific date.
     *
     * @param Builder<MetricsSnapshot> $query
     * @return Builder<MetricsSnapshot>
     */
    public function scopeForDate(Builder $query, \Illuminate\Support\Carbon $date): Builder
    {
        return $query->where('snapshot_date', $date);
    }

    /**
     * Scope a query to filter snapshots by date range.
     *
     * @param Builder<MetricsSnapshot> $query
     * @return Builder<MetricsSnapshot>
     */
    public function scopeForDateRange(Builder $query, \Illuminate\Support\Carbon $start, \Illuminate\Support\Carbon $end): Builder
    {
        return $query->whereBetween('snapshot_date', [$start, $end]);
    }

    /**
     * Scope a query to filter snapshots by metric type.
     *
     * @param Builder<MetricsSnapshot> $query
     * @return Builder<MetricsSnapshot>
     */
    public function scopeByMetricType(Builder $query, string $type): Builder
    {
        return $query->where('metric_type', $type);
    }

    /**
     * Scope a query to filter snapshots by dimension.
     *
     * @param Builder<MetricsSnapshot> $query
     * @return Builder<MetricsSnapshot>
     */
    public function scopeByDimension(Builder $query, string $dimension): Builder
    {
        return $query->where('dimension', $dimension);
    }

    /**
     * Scope a query to filter snapshots by dimension value.
     *
     * @param Builder<MetricsSnapshot> $query
     * @return Builder<MetricsSnapshot>
     */
    public function scopeByDimensionValue(Builder $query, string $value): Builder
    {
        return $query->where('dimension_value', $value);
    }

    /**
     * Scope a query to get the latest snapshot for each metric type/dimension combination.
     *
     * @param Builder<MetricsSnapshot> $query
     * @return Builder<MetricsSnapshot>
     */
    public function scopeLatestSnapshot(Builder $query): Builder
    {
        return $query->latest('snapshot_date');
    }

    /**
     * Get the metric value as a float.
     */
    public function getValueAsFloat(): float
    {
        return (float) $this->value;
    }

    /**
     * Check if this snapshot uses k-anonymity anonymization.
     */
    public function isKAnonymized(): bool
    {
        return str_starts_with($this->anonymization_method ?? '', 'k_anonymity');
    }

    /**
     * Check if this snapshot uses differential privacy anonymization.
     */
    public function isDifferentiallyPrivate(): bool
    {
        return str_contains($this->anonymization_method ?? '', 'dp_');
    }
}