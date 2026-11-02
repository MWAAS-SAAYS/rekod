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
 * @property string $code
 * @property string $name
 * @property int|null $parent_id
 * @property int $level
 * @property string|null $description
 * @property bool $is_active
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read FieldTaxonomy|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FieldTaxonomy> $children
 * @method static Builder<static>|FieldTaxonomy active()
 * @method static Builder<static>|FieldTaxonomy level(int $level)
 * @method static Builder<static>|FieldTaxonomy root()
 */
final class FieldTaxonomy extends Model
{
    use HasFactory;

    protected $table = 'field_taxonomy';

    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'level',
        'description',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FieldTaxonomy::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(FieldTaxonomy::class, 'parent_id')->orderBy('display_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
}