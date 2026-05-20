<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'digest_opt_out',
        'last_digest_sent_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'digest_opt_out' => 'boolean',
            'last_digest_sent_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function flags(): BelongsToMany
    {
        return $this->belongsToMany(UserFlags::class, 'user_flag_assignments', 'user_id', 'user_flag_id')
            ->withTimestamps()
            ->orderBy('name');
    }

    public function isAdmin(): bool
    {
        return $this->role_id === 1 || $this->role?->name === 'admin';
    }

    public function ownsProject(Project $project): bool
    {
        return $this->id === $project->created_by;
    }

    public function canManageProject(Project $project): bool
    {
        return $this->isAdmin() || $this->ownsProject($project);
    }

    public function leadsProject(Project $project): bool
    {
        return $this->id === $project->leader_id;
    }

    public function canManageProjectFinancials(Project $project): bool
    {
        return $this->isAdmin() || $this->leadsProject($project);
    }

    public function ownsEvent(Event $event): bool
    {
        return $this->id === $event->created_by;
    }

    public function canManageEvent(Event $event): bool
    {
        return $this->isAdmin() || $this->ownsEvent($event);
    }

    public function canViewEvent(Event $event): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($event->event_status === 'draft') {
            return $this->ownsEvent($event);
        }

        return in_array($event->event_status, ['published', 'cancelled', 'archived'], true);
    }

    public function receivesDigest(): bool
    {
        return ! $this->digest_opt_out;
    }
}


