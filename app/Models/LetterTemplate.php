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
 * @property string $name
 * @property int|null $department_id
 * @property string $template_body
 * @property array $variables_schema
 * @property int $version
 * @property bool $is_active
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read AdministrativeUnit|null $department
 * @property-read User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LetterRequest> $letterRequests
 * @method static Builder<static>|LetterTemplate active()
 * @method static Builder<static>|LetterTemplate forDepartment(int|null $departmentId)
 * @method static Builder<static>|LetterTemplate name(string $name)
 */
final class LetterTemplate extends Model
{
    use HasFactory;

    protected $table = 'letter_templates';

    protected $fillable = [
        'name',
        'department_id',
        'template_body',
        'variables_schema',
        'version',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'variables_schema' => 'array',
        'version' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(AdministrativeUnit::class, 'department_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class, 'template_id');
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

    public function scopeName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }

    public function incrementVersion(): static
    {
        $this->increment('version');
        return $this->fresh();
    }
}