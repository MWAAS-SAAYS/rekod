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
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon $entry_date
 * @property float $hours_worked
 * @property array $field_values
 * @property string|null $supervisor_name
 * @property string|null $supervisor_signature_path
 * @property \Illuminate\Support\Carbon|null $supervisor_signed_at
 * @property string $status
 * @property int|null $hod_approved_by
 * @property \Illuminate\Support\Carbon|null $hod_approved_at
 * @property string|null $offline_id
 * @property \Illuminate\Support\Carbon|null $synced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $student
 * @property-read Company|null $company
 * @property-read User|null $hodApprover
 * @method static Builder<static>|LogbookEntry draft()
 * @method static Builder<static>|LogbookEntry submitted()
 * @method static Builder<static>|LogbookEntry supervisorSigned()
 * @method static Builder<static>|LogbookEntry hodApproved()
 * @method static Builder<static>|LogbookEntry forStudent(int $studentId)
 * @method static Builder<static>|LogbookEntry forCompany(int $companyId)
 * @method static Builder<static>|LogbookEntry dateRange(\Illuminate\Support\Carbon $start, \Illuminate\Support\Carbon $end)
 * @method static Builder<static>|LogbookEntry unsynced()
 * @method static Builder<static>|LogbookEntry offlineId(string $offlineId)
 */
final class LogbookEntry extends Model
{
    use HasFactory;

    protected $table = 'logbook_entries';

    protected $fillable = [
        'student_id',
        'company_id',
        'entry_date',
        'hours_worked',
        'field_values',
        'supervisor_name',
        'supervisor_signature_path',
        'supervisor_signed_at',
        'status',
        'hod_approved_by',
        'hod_approved_at',
        'offline_id',
        'synced_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'hours_worked' => 'decimal:2',
        'field_values' => 'array',
        'supervisor_signed_at' => 'datetime',
        'hod_approved_at' => 'datetime',
        'synced_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function hodApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_approved_by');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function scopeSupervisorSigned(Builder $query): Builder
    {
        return $query->where('status', 'supervisor_signed');
    }

    public function scopeHodApproved(Builder $query): Builder
    {
        return $query->where('status', 'hod_approved');
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeDateRange(Builder $query, \Illuminate\Support\Carbon $start, \Illuminate\Support\Carbon $end): Builder
    {
        return $query->whereBetween('entry_date', [$start, $end]);
    }

    public function scopeUnsynced(Builder $query): Builder
    {
        return $query->whereNull('synced_at');
    }

    public function scopeOfflineId(Builder $query, string $offlineId): Builder
    {
        return $query->where('offline_id', $offlineId);
    }

    public function submit(): void
    {
        $this->update(['status' => 'submitted']);
    }

    public function markSupervisorSigned(string $supervisorName, ?string $signaturePath = null): void
    {
        $this->update([
            'status' => 'supervisor_signed',
            'supervisor_name' => $supervisorName,
            'supervisor_signature_path' => $signaturePath,
            'supervisor_signed_at' => now(),
        ]);
    }

    public function markHodApproved(int $userId): void
    {
        $this->update([
            'status' => 'hod_approved',
            'hod_approved_by' => $userId,
            'hod_approved_at' => now(),
        ]);
    }

    public function markSynced(): void
    {
        $this->update(['synced_at' => now()]);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isSupervisorSigned(): bool
    {
        return $this->status === 'supervisor_signed';
    }

    public function isHodApproved(): bool
    {
        return $this->status === 'hod_approved';
    }

    public function getFieldValue(string $fieldName): mixed
    {
        return $this->field_values[$fieldName] ?? null;
    }

    public function setFieldValue(string $fieldName, mixed $value): void
    {
        $this->field_values[$fieldName] = $value;
        $this->save();
    }
}