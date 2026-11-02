<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $department_id
 * @property string $trigger
 * @property array $escalate_to_roles
 * @property array $notify_channels
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read AdministrativeUnit|null $department
 * @method static Builder<static>|DistressEscalationRule active()
 * @method static Builder<static>|DistressEscalationRule forDepartment(int|null $departmentId)
 * @method static Builder<static>|DistressEscalationRule trigger(string $trigger)
 */
final class DistressEscalationRule extends Model
{
    use HasFactory;

    protected $table = 'distress_escalation_rules';

    protected $fillable = [
        'department_id',
        'trigger',
        'escalate_to_roles',
        'notify_channels',
        'is_active',
    ];

    protected $casts = [
        'escalate_to_roles' => 'array',
        'notify_channels' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(AdministrativeUnit::class, 'department_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDepartment(Builder $query, ?int $departmentId): Builder
    {
        if ($departmentId === null) {
            return $query->whereNull('department_id');
        }
        return $query->where('department_id', $departmentId);
    }

    public function scopeTrigger(Builder $query, string $trigger): Builder
    {
        return $query->where('trigger', $trigger);
    }

    /**
     * Get all rules that should apply for a given department.
     * Includes university-wide rules (department_id = null).
     */
    public static function getApplicableRules(?int $departmentId): Builder
    {
        return static::active()
            ->where(function ($query) use ($departmentId) {
                $query->whereNull('department_id')
                      ->orWhere('department_id', $departmentId);
            })
            ->orderByRaw('department_id IS NULL ASC'); // Department-specific rules first
    }

    /**
     * Check if a role should be escalated to.
     */
    public function shouldEscalateTo(string $role): bool
    {
        return in_array($role, $this->escalate_to_roles ?? []);
    }

    /**
     * Check if a channel should be notified.
     */
    public function shouldNotifyVia(string $channel): bool
    {
        return in_array($channel, $this->notify_channels ?? []);
    }
}