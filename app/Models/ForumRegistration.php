<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $forum_id
 * @property int $user_id
 * @property string $role
 * @property bool $attended
 * @property int|null $feedback_score
 * @property string|null $feedback_text
 * @property \Illuminate\Support\Carbon $registered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read EngagementForum $forum
 * @property-read User $user
 * @method static Builder<static>|ForumRegistration attended()
 * @method static Builder<static>|ForumRegistration notAttended()
 * @method static Builder<static>|ForumRegistration role(string $role)
 * @method static Builder<static>|ForumRegistration forForum(int $forumId)
 * @method static Builder<static>|ForumRegistration forUser(int $userId)
 */
final class ForumRegistration extends Model
{
    use HasFactory;

    protected $table = 'forum_registrations';

    protected $fillable = [
        'forum_id',
        'user_id',
        'role',
        'attended',
        'feedback_score',
        'feedback_text',
        'registered_at',
    ];

    protected $casts = [
        'attended' => 'boolean',
        'feedback_score' => 'integer',
        'registered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function forum(): BelongsTo
    {
        return $this->belongsTo(EngagementForum::class, 'forum_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAttended(Builder $query): Builder
    {
        return $query->where('attended', true);
    }

    public function scopeNotAttended(Builder $query): Builder
    {
        return $query->where('attended', false);
    }

    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    public function scopeForForum(Builder $query, int $forumId): Builder
    {
        return $query->where('forum_id', $forumId);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function markAttended(): void
    {
        $this->update(['attended' => true]);
    }

    public function submitFeedback(int $score, ?string $text = null): void
    {
        $this->update([
            'feedback_score' => $score,
            'feedback_text' => $text,
        ]);
    }

    public function hasFeedback(): bool
    {
        return $this->feedback_score !== null;
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isCompanyHr(): bool
    {
        return $this->role === 'company_hr';
    }

    public function isSpeaker(): bool
    {
        return $this->role === 'speaker';
    }
}