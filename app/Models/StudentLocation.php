<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $student_id
 * @property string $latitude
 * @property string $longitude
 * @property int|null $accuracy_meters
 * @property string|null $location_name
 * @property bool $share_with_hod
 * @property \Illuminate\Support\Carbon $recorded_at
 * @property string $source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $student
 * @method static Builder<static>|StudentLocation sharedWithHod()
 * @method static Builder<static>|StudentLocation forStudent(int $studentId)
 * @method static Builder<static>|StudentLocation dateRange(\Illuminate\Support\Carbon $start, \Illuminate\Support\Carbon $end)
 * @method static Builder<static>|StudentLocation source(string $source)
 */
final class StudentLocation extends Model
{
    use HasFactory;

    protected $table = 'student_locations';

    protected $fillable = [
        'student_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'location_name',
        'share_with_hod',
        'recorded_at',
        'source',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy_meters' => 'integer',
        'share_with_hod' => 'boolean',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function scopeSharedWithHod(Builder $query): Builder
    {
        return $query->where('share_with_hod', true);
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeDateRange(Builder $query, \Illuminate\Support\Carbon $start, \Illuminate\Support\Carbon $end): Builder
    {
        return $query->whereBetween('recorded_at', [$start, $end]);
    }

    public function scopeSource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    public function getCoordinates(): array
    {
        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
        ];
    }

    public function isMobileSource(): bool
    {
        return $this->source === 'mobile';
    }

    public function isManualSource(): bool
    {
        return $this->source === 'manual';
    }

    public function isCompanyCheckinSource(): bool
    {
        return $this->source === 'company_checkin';
    }
}