<?php

namespace App\Services;

use App\Data\CommunityDigest;
use App\Models\Event;
use App\Models\EventVolunteer;
use App\Models\Project;
use App\Models\ProjectVolunteer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CommunityDigestService
{
    public const LOOKBACK_DAYS = 7;

    public function since(User $user): Carbon
    {
        return $user->last_digest_sent_at
            ?? now()->subDays(self::LOOKBACK_DAYS);
    }

    public function buildFor(User $user): CommunityDigest
    {
        $since = $this->since($user);

        return new CommunityDigest(
            newPublicEvents: $this->newPublicEvents($since),
            updatedVolunteerEvents: $this->updatedVolunteerEvents($user, $since),
            updatedVolunteerProjects: $this->updatedVolunteerProjects($user, $since),
            newVolunteerProjects: $this->newVolunteerProjects($since),
        );
    }

    public function recipients(): Collection
    {
        return User::query()
            ->where('digest_opt_out', false)
            ->orderBy('id')
            ->get();
    }

    protected function newPublicEvents(Carbon $since): Collection
    {
        return Event::query()
            ->with('creator')
            ->where('event_type', 'public')
            ->where('event_status', 'published')
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->get();
    }

    protected function updatedVolunteerEvents(User $user, Carbon $since): Collection
    {
        $eventIds = EventVolunteer::query()
            ->where('user_id', $user->id)
            ->pluck('event_id');

        if ($eventIds->isEmpty()) {
            return collect();
        }

        return Event::query()
            ->with('creator')
            ->whereIn('id', $eventIds)
            ->where('updated_at', '>=', $since)
            ->where('created_at', '<', $since)
            ->orderByDesc('updated_at')
            ->get();
    }

    protected function updatedVolunteerProjects(User $user, Carbon $since): Collection
    {
        $projectIds = ProjectVolunteer::query()
            ->where('user_id', $user->id)
            ->pluck('project_id');

        if ($projectIds->isEmpty()) {
            return collect();
        }

        return Project::query()
            ->with('leader')
            ->whereIn('id', $projectIds)
            ->where('updated_at', '>=', $since)
            ->where('created_at', '<', $since)
            ->orderByDesc('updated_at')
            ->get();
    }

    protected function newVolunteerProjects(Carbon $since): Collection
    {
        return Project::query()
            ->with('leader')
            ->where('volunteer_required', true)
            ->whereNotIn('project_status', ['draft', 'cancelled', 'archived'])
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->get();
    }
}
