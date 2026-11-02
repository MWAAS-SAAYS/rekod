<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $consent_type
 * @property bool $granted
 * @property string $version
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $granted_at
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @method static Builder<static>|ConsentRecord granted()
 * @method static Builder<static>|ConsentRecord revoked()
 * @method static Builder<static>|ConsentRecord type(string $type)
 * @method static Builder<static>|ConsentRecord version(string $version)
 * @method static Builder<static>|ConsentRecord forUser(int $userId)
 */
final class ConsentRecord extends Model
{
    use HasFactory;

    protected $table = 'consent_records';

    protected $fillable = [
        'user_id',
        'consent_type',
        'granted',
        'version',
        'ip_address',
        'user_agent',
        'granted_at',
        'revoked_at',
    ];

    protected $casts = [
        'granted' => 'boolean',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeGranted(Builder $query): Builder
    {
        return $query->where('granted', true);
    }

    public function scopeRevoked(Builder $query): Builder
    {
        return $query->where('granted', false);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('consent_type', $type);
    }

    public function scopeVersion(Builder $query, string $version): Builder
    {
        return $query->where('version', $version);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isGranted(): bool
    {
        return $this->granted === true;
    }

    public function isRevoked(): bool
    {
        return $this->granted === false;
    }

    public function grant(): void
    {
        $this->update([
            'granted' => true,
            'granted_at' => now(),
            'revoked_at' => null,
        ]);
    }

    public function revoke(): void
    {
        $this->update([
            'granted' => false,
            'revoked_at' => now(),
        ]);
    }

    /**
     * Get the latest consent record for a user and type.
     */
    public static function getLatestForUser(int $userId, string $consentType): ?self
    {
        return static::forUser($userId)
            ->type($consentType)
            ->latest('created_at')
            ->first();
    }

    /**
     * Check if user has granted consent for a type.
     */
    public static function hasConsent(int $userId, string $consentType): bool
    {
        $record = static::getLatestForUser($userId, $consentType);
        return $record?->isGranted() ?? false;
    }
}