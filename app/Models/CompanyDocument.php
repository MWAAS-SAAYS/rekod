<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property string $doc_type
 * @property string $file_path
 * @property string $original_name
 * @property string $mime_type
 * @property int $uploaded_by
 * @property bool $verified
 * @property int|null $verified_by
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property string|null $verification_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Company $company
 * @property-read User $uploader
 * @property-read User|null $verifier
 * @method static Builder<static>|CompanyDocument verified()
 * @method static Builder<static>|CompanyDocument unverified()
 * @method static Builder<static>|CompanyDocument type(string $type)
 */
final class CompanyDocument extends Model
{
    use HasFactory;

    protected $table = 'company_documents';

    protected $fillable = [
        'company_id',
        'doc_type',
        'file_path',
        'original_name',
        'mime_type',
        'uploaded_by',
        'verified',
        'verified_by',
        'verified_at',
        'verification_notes',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verified', true);
    }

    public function scopeUnverified(Builder $query): Builder
    {
        return $query->where('verified', false);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('doc_type', $type);
    }
}