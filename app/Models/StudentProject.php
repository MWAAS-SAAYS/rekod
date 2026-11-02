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
 * @property string $title
 * @property string $problem_statement
 * @property string $brief_solution
 * @property string|null $full_solution_path
 * @property array $field_tags
 * @property bool $interdisciplinary
 * @property string $ip_ownership
 * @property string|null $ip_notes
 * @property string $hod_approval_status
 * @property int|null $hod_approved_by
 * @property \Illuminate\Support\Carbon|null $hod_approved_at
 * @property string|null $hod_comments
 * @property string $visibility
 * @property int $view_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $student
 * @property-read User|null $hodApprover
 * @method static Builder<static>|StudentProject pendingHod()
 * @method static Builder<static>|StudentProject approved()
 * @method static Builder<static>|StudentProject visibleTo(string $role)
 * @method static Builder<static>|StudentProject interdisciplinary()
 */
final class StudentProject extends Model
{
    use HasFactory;

    protected $table = 'student_projects';

    protected $fillable = [
        'student_id',
        'title',
        'problem_statement',
        'brief_solution',
        'full_solution_path',
        'field_tags',
        'interdisciplinary',
        'ip_ownership',
        'ip_notes',
        'hod_approval_status',
        'hod_approved_by',
        'hod_approved_at',
        'hod_comments',
        'visibility',
        'view_count',
    ];

    protected $casts = [
        'field_tags' => 'array',
        'interdisciplinary' => 'boolean',
        'hod_approved_at' => 'datetime',
        'view_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function hodApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_approved_by');
    }

    public function scopePendingHod(Builder $query): Builder
    {
        return $query->where('hod_approval_status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('hod_approval_status', 'approved');
    }

    public function scopeInterdisciplinary(Builder $query): Builder
    {
        return $query->where('interdisciplinary', true);
    }

    public function scopeVisibleTo(Builder $query, string $role): Builder
    {
        return match ($role) {
            'student' => $query->where('visibility', 'public'),
            'hod' => $query->whereIn('visibility', ['hod_only', 'vetted_companies', 'public']),
            'industry' => $query->whereIn('visibility', ['vetted_companies', 'public']),
            'admin' => $query,
            default => $query->where('visibility', 'public'),
        };
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}