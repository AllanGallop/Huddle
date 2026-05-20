<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    public const STATUSES = [
        'draft',
        'published',
        'cancelled',
        'archived',
    ];

    public const TYPES = [
        'public',
        'private',
    ];

    protected $fillable = [
        'name',
        'description',
        'location',
        'start_time',
        'end_time',
        'created_by',
        'event_type',
        'event_status',
        'volunteer_required',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'volunteer_required' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(EventComment::class);
    }

    public function topLevelComments(): HasMany
    {
        return $this->hasMany(EventComment::class)->whereNull('parent_comment_id');
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(EventVolunteer::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->whereIn('event_status', ['published', 'cancelled', 'archived'])
                ->orWhere(function (Builder $draft) use ($user) {
                    $draft->where('event_status', 'draft')
                        ->where('created_by', $user->id);
                });
        });
    }

    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture();
    }

    public function isOngoing(): bool
    {
        $now = now();

        return $this->start_time->lte($now) && $this->end_time->gte($now);
    }

    public function isPast(): bool
    {
        return $this->end_time->isPast();
    }

    public function timingLabel(): string
    {
        if ($this->isOngoing()) {
            return __('Ongoing');
        }

        if ($this->isUpcoming()) {
            return __('Upcoming');
        }

        return __('Past');
    }
}
