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
 * @property int $template_id
 * @property array $template_data
 * @property string|null $generated_pdf_path
 * @property string $status
 * @property int|null $hod_reviewed_by
 * @property \Illuminate\Support\Carbon|null $hod_reviewed_at
 * @property string|null $hod_comments
 * @property int|null $cod_signed_by
 * @property \Illuminate\Support\Carbon|null $cod_signed_at
 * @property string|null $cod_signature_path
 * @property \Illuminate\Support\Carbon|null $dispatched_at
 * @property string|null $dispatched_via
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $student
 * @property-read Company|null $company
 * @property-read LetterTemplate $template
 * @property-read User|null $hodReviewer
 * @property-read User|null $codSigner
 * @method static Builder<static>|LetterRequest draft()
 * @method static Builder<static>|LetterRequest hodReview()
 * @method static Builder<static>|LetterRequest codReview()
 * @method static Builder<static>|LetterRequest signed()
 * @method static Builder<static>|LetterRequest dispatched()
 * @method static Builder<static>|LetterRequest forStudent(int $studentId)
 * @method static Builder<static>|LetterRequest forHodReviewer(int $hodId)
 * @method static Builder<static>|LetterRequest forCodSigner(int $codId)
 */
final class LetterRequest extends Model
{
    use HasFactory;

    protected $table = 'letter_requests';

    protected $fillable = [
        'student_id',
        'company_id',
        'template_id',
        'template_data',
        'generated_pdf_path',
        'status',
        'hod_reviewed_by',
        'hod_reviewed_at',
        'hod_comments',
        'cod_signed_by',
        'cod_signed_at',
        'cod_signature_path',
        'dispatched_at',
        'dispatched_via',
    ];

    protected $casts = [
        'template_data' => 'array',
        'hod_reviewed_at' => 'datetime',
        'cod_signed_at' => 'datetime',
        'dispatched_at' => 'datetime',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterTemplate::class, 'template_id');
    }

    public function hodReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_reviewed_by');
    }

    public function codSigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_signed_by');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeHodReview(Builder $query): Builder
    {
        return $query->where('status', 'hod_review');
    }

    public function scopeCodReview(Builder $query): Builder
    {
        return $query->where('status', 'cod_review');
    }

    public function scopeSigned(Builder $query): Builder
    {
        return $query->where('status', 'signed');
    }

    public function scopeDispatched(Builder $query): Builder
    {
        return $query->where('status', 'dispatched');
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForHodReviewer(Builder $query, int $hodId): Builder
    {
        return $query->where('hod_reviewed_by', $hodId);
    }

    public function scopeForCodSigner(Builder $query, int $codId): Builder
    {
        return $query->where('cod_signed_by', $codId);
    }

    public function submitForHodReview(): void
    {
        $this->update(['status' => 'hod_review']);
    }

    public function markHodReviewed(int $userId, ?string $comments = null): void
    {
        $this->update([
            'status' => 'cod_review',
            'hod_reviewed_by' => $userId,
            'hod_reviewed_at' => now(),
            'hod_comments' => $comments,
        ]);
    }

    public function markCodSigned(int $userId, ?string $signaturePath = null): void
    {
        $this->update([
            'status' => 'signed',
            'cod_signed_by' => $userId,
            'cod_signed_at' => now(),
            'cod_signature_path' => $signaturePath,
        ]);
    }

    public function markDispatched(string $via): void
    {
        $this->update([
            'status' => 'dispatched',
            'dispatched_at' => now(),
            'dispatched_via' => $via,
        ]);
    }

    public function isPendingHodReview(): bool
    {
        return $this->status === 'hod_review';
    }

    public function isPendingCodReview(): bool
    {
        return $this->status === 'cod_review';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['signed', 'dispatched']);
    }
}