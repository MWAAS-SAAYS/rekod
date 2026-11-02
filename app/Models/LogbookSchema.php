<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $department_id
 * @property string $field_name
 * @property string $field_label
 * @property string $field_type
 * @property array|null $field_options
 * @property bool $is_required
 * @property int $display_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read AdministrativeUnit $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LogbookEntry> $entries
 * @method static Builder<static>|LogbookSchema active()
 * @method static Builder<static>|LogbookSchema forDepartment(int $departmentId)
 * @method static Builder<static>|LogbookSchema fieldType(string $type)
 * @method static Builder<static>|LogbookSchema required()
 * @method static Builder<static>|LogbookSchema ordered()
 */
final class LogbookSchema extends Model
{
    use HasFactory;

    protected $table = 'logbook_schema';

    protected $fillable = [
        'department_id',
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'is_required',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_required' => 'boolean',
        'display_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(AdministrativeUnit::class, 'department_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LogbookEntry::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeFieldType(Builder $query, string $type): Builder
    {
        return $query->where('field_type', $type);
    }

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order');
    }

    public function isSelectType(): bool
    {
        return $this->field_type === 'select';
    }

    public function isSignatureType(): bool
    {
        return $this->field_type === 'signature';
    }

    public function isFileType(): bool
    {
        return $this->field_type === 'file';
    }

    public function getOptions(): array
    {
        return $this->field_options ?? [];
    }
}