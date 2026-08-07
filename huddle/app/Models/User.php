<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'digest_opt_out',
        'last_digest_sent_at',
        'privacy_policy_accepted_at',
        'privacy_policy_version',
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
            'privacy_policy_accepted_at' => 'datetime',
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps()
            ->orderBy('name');
    }

    public function flags(): BelongsToMany
    {
        return $this->belongsToMany(UserFlags::class, 'user_flag_assignments', 'user_id', 'user_flag_id')
            ->withTimestamps()
            ->orderBy('name');
    }

    public function accreditationAssignments(): HasMany
    {
        return $this->hasMany(AccreditationAssignment::class);
    }

    public function mentoredAccreditations(): BelongsToMany
    {
        return $this->belongsToMany(Accreditation::class, 'accreditation_mentors')
            ->withTimestamps()
            ->orderBy('name');
    }

    public function membershipRenewalAssignments(): HasMany
    {
        return $this->hasMany(MembershipRenewalAssignment::class);
    }

    public function projectComments(): HasMany
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function eventComments(): HasMany
    {
        return $this->hasMany(EventComment::class);
    }

    public function ledProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'leader_id');
    }

    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function projectVolunteers(): HasMany
    {
        return $this->hasMany(ProjectVolunteer::class);
    }

    public function eventVolunteers(): HasMany
    {
        return $this->hasMany(EventVolunteer::class);
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function wikiPageVersions(): HasMany
    {
        return $this->hasMany(WikiPageVersion::class, 'created_by');
    }

    public function hasAcceptedPrivacyPolicy(): bool
    {
        return $this->privacy_policy_accepted_at !== null
            && $this->privacy_policy_version === config('gdpr.policy_version');
    }

    public function acceptPrivacyPolicy(): void
    {
        $this->forceFill([
            'privacy_policy_accepted_at' => now(),
            'privacy_policy_version' => config('gdpr.policy_version'),
        ])->save();
    }

    public function latestMembershipRenewalAssignment(): ?MembershipRenewalAssignment
    {
        $assignments = $this->relationLoaded('membershipRenewalAssignments')
            ? $this->membershipRenewalAssignments
            : $this->membershipRenewalAssignments()->with('membershipRenewal')->get();

        return $assignments
            ->sortByDesc(fn (MembershipRenewalAssignment $assignment): string => $assignment->membershipRenewal->name)
            ->first();
    }

    /**
     * @return 'active'|'expired'|'none'
     */
    public function membershipStatus(): string
    {
        $latest = $this->latestMembershipRenewalAssignment();

        if ($latest === null) {
            return 'none';
        }

        return $latest->membershipRenewal->isCurrent() ? 'active' : 'expired';
    }

    public function scopeWithLatestMembershipPeriod(Builder $query): Builder
    {
        return $query->whereExists(function ($sub): void {
            $sub->selectRaw('1')
                ->from('membership_renewal_assignments as mra')
                ->join('membership_renewals as mr', 'mr.id', '=', 'mra.membership_renewal_id')
                ->whereColumn('mra.user_id', 'users.id')
                ->whereRaw('mr.name = (
                    select max(mr2.name)
                    from membership_renewal_assignments as mra2
                    join membership_renewals as mr2 on mr2.id = mra2.membership_renewal_id
                    where mra2.user_id = users.id
                )');
        });
    }

    public function scopeMembershipActive(Builder $query): Builder
    {
        return $query
            ->withLatestMembershipPeriod()
            ->whereExists(function ($sub): void {
                $sub->selectRaw('1')
                    ->from('membership_renewal_assignments as mra')
                    ->join('membership_renewals as mr', 'mr.id', '=', 'mra.membership_renewal_id')
                    ->whereColumn('mra.user_id', 'users.id')
                    ->where('mr.end_date', '>=', now()->toDateString())
                    ->whereRaw('mr.name = (
                        select max(mr2.name)
                        from membership_renewal_assignments as mra2
                        join membership_renewals as mr2 on mr2.id = mra2.membership_renewal_id
                        where mra2.user_id = users.id
                    )');
            });
    }

    public function scopeMembershipExpired(Builder $query): Builder
    {
        return $query
            ->withLatestMembershipPeriod()
            ->whereExists(function ($sub): void {
                $sub->selectRaw('1')
                    ->from('membership_renewal_assignments as mra')
                    ->join('membership_renewals as mr', 'mr.id', '=', 'mra.membership_renewal_id')
                    ->whereColumn('mra.user_id', 'users.id')
                    ->where('mr.end_date', '<', now()->toDateString())
                    ->whereRaw('mr.name = (
                        select max(mr2.name)
                        from membership_renewal_assignments as mra2
                        join membership_renewals as mr2 on mr2.id = mra2.membership_renewal_id
                        where mra2.user_id = users.id
                    )');
            });
    }

    public function scopeWithoutMembership(Builder $query): Builder
    {
        return $query->whereDoesntHave('membershipRenewalAssignments');
    }

    public function hasFlag(string $name): bool
    {
        if ($this->relationLoaded('flags')) {
            return $this->flags->contains(
                fn (UserFlags $flag): bool => strcasecmp($flag->name, $name) === 0,
            );
        }

        return $this->flags()->whereRaw('LOWER(name) = ?', [strtolower($name)])->exists();
    }

    public function hasRole(string $name): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(
                fn (Role $role): bool => strcasecmp($role->name, $name) === 0,
            );
        }

        return $this->roles()->whereRaw('LOWER(name) = ?', [strtolower($name)])->exists();
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->relationLoaded('roles')) {
            $this->roles->loadMissing('permissions');

            return $this->roles->contains(
                fn (Role $role): bool => $role->hasPermission($slug),
            );
        }

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('slug', $slug))
            ->exists();
    }

    public function isMentor(): bool
    {
        return $this->hasRole('Mentor') || $this->hasFlag('Mentor');
    }

    public function canAccessMentors(): bool
    {
        return $this->isAdmin() || $this->hasPermission(Permission::ACCREDITATIONS_ASSIGN_VIA_EXAM);
    }

    public function canManageForms(): bool
    {
        return $this->hasPermission(Permission::FORMS_MANAGE);
    }

    public function canManageWiki(): bool
    {
        return $this->hasPermission(Permission::WIKI_EDIT);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function canViewFinancialReports(): bool
    {
        return $this->isAdmin() || $this->hasFlag('Committee');
    }

    public function ownsProject(Project $project): bool
    {
        return $this->id === $project->created_by;
    }

    public function canManageProject(Project $project): bool
    {
        return $this->ownsProject($project) || $this->hasPermission(Permission::PROJECTS_EDIT_ANY);
    }

    public function canDeleteProject(Project $project): bool
    {
        return $this->ownsProject($project) || $this->hasPermission(Permission::PROJECTS_DELETE_ANY);
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
        return $this->ownsEvent($event) || $this->hasPermission(Permission::EVENTS_EDIT_ANY);
    }

    public function canDeleteEvent(Event $event): bool
    {
        return $this->ownsEvent($event) || $this->hasPermission(Permission::EVENTS_DELETE_ANY);
    }

    public function canViewEvent(Event $event): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($event->event_status === 'draft') {
            return $this->ownsEvent($event) || $this->hasPermission(Permission::EVENTS_EDIT_ANY);
        }

        return in_array($event->event_status, ['published', 'cancelled', 'archived'], true);
    }

    public function receivesDigest(): bool
    {
        return ! $this->digest_opt_out;
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
