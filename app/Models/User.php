<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Check if the user has the student role.
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Check if the user has the industry role.
     */
    public function isIndustry(): bool
    {
        return $this->role === 'industry';
    }

    /**
     * Check if the user has the university role.
     */
    public function isUniversity(): bool
    {
        return $this->role === 'university';
    }

    /**
     * Check if the user has the admin role.
     */
    public function isAdmin(): bool
    {
        return strtolower((string) $this->role) === 'admin';
    }

    /**
     * Get the weekly reports submitted by this user.
     *
     * @return HasMany<WeeklyReport, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class, 'user_id');
    }

    /**
     * Get company placements managed by this user.
     *
     * @return HasMany<Placement, $this>
     */
    public function placements(): HasMany
    {
        return $this->hasMany(Placement::class, 'company_id');
    }

    /**
     * Get students assigned to this user as a supervisor.
     *
     * @return HasMany<User, $this>
     */
    public function assignedStudents(): HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id')
            ->where('role', 'student');
    }

    /**
     * Get the student profile linked to this user.
     *
     * @return HasOne<Student, $this>
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    /**
     * Get the industrial supervisor profile linked to this user.
     *
     * @return HasOne<IndustrialSupervisor, $this>
     */
    public function industrialSupervisor(): HasOne
    {
        return $this->hasOne(IndustrialSupervisor::class, 'user_id');
    }

    /**
     * Get the academic lecturer profile linked to this user.
     *
     * @return HasOne<Lecturer, $this>
     */
    public function lecturer(): HasOne
    {
        return $this->hasOne(Lecturer::class, 'user_id');
    }

    /**
     * Scope a query to filter users by role.
     *
     * @param Builder<User> $query
     * @return Builder<User>
     */
    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to filter users by email address.
     *
     * @param Builder<User> $query
     * @return Builder<User>
     */
    public function scopeForEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', $email);
    }
}