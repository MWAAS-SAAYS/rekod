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
 * @property int|null $hod_id
 * @property string $message
 * @property string $severity
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $acknowledged_at
 * @property int|null $acknowledged_by
 * @property \Illuminate\Support\Carbon|null $resolved_at
 * @property int|null $resolved_by
 * @property \Illuminate\Support\Carbon|null $escalated_at
 * @property array|null $escalated_to
 * @property string|null $escalation_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $student
 * @property-read User|null $hod
 * @property-read User|null $acknowledger
 * @property-read User|null $resolver
 * @method static Builder<static>|DistressMessage unread()
 * @method static Builder<static>|DistressMessage acknowledged()
 * @method static Builder<static>|DistressMessage inProgress()
 * @method static Builder<static>|DistressMessage resolved()
 * @method static Builder<static>|DistressMessage escalated()
 * @method static Builder<static>|DistressMessage severity(string $severity)
 * @method static Builder<static>|DistressMessage forHod(int $hodId)
 * @method static Builder<static>|DistressMessage forStudent(int $studentId)
 */
final class DistressMessage extends Model
{
    use HasFactory;

    protected $table = 'distress_messages';

    protected $fillable = [
        'student_id',
        'hod_id',
        'message',
        'severity',
        'status',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'resolved_by',
        'escalated_at',
        'escalated_to',
        'escalation_reason',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'escalated_at' => 'datetime',
        'escalated_to' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function hod(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_id');
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', 'unread');
    }

    public function scopeAcknowledged(Builder $query): Builder
    {
        return $query->where('status', 'acknowledged');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    public function scopeEscalated(Builder $query): Builder
    {
        return $query->where('status', 'escalated');
    }

    public function scopeSeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }

    public function scopeForHod(Builder $query, int $hodId): Builder
    {
        return $query->where('hod_id', $hodId);
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function markAcknowledged(int $userId): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
        ]);
    }

    public function markInProgress(int $userId): void
    {
        $this->update([
            'status' => 'in_progress',
            'acknowledged_at' => $this->acknowledged_at ?? now(),
            'acknowledged_by' => $this->acknowledged_by ?? $userId,
        ]);
    }

    public function markResolved(int $userId): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);
    }

    public function markEscalated(array $escalatedTo, string $reason): void
    {
        $this->update([
            'status' => 'escalated',
            'escalated_at' => now(),
            'escalated_to' => $escalatedTo,
            'escalation_reason' => $reason,
        ]);
    }

    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    public function isHighSeverity(): bool
    {
        return in_array($this->severity, ['high', 'critical']);
    }
}