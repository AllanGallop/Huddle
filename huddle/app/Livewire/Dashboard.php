<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectComment;
use App\Models\ProjectImage;
use App\Models\ProjectVolunteer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public const UPDATE_LOOKBACK_DAYS = 14;

    public const UPDATES_LIMIT = 10;

    public const EVENTS_LIMIT = 5;

    #[Computed]
    public function firstName(): string
    {
        return str(Auth::user()->name)->before(' ')->toString() ?: Auth::user()->name;
    }

    #[Computed]
    public function projectStats(): array
    {
        $mine = $this->myProjectsQuery();

        return [
            'active' => (clone $mine)->whereNotIn('project_status', ['cancelled', 'archived', 'completed'])->count(),
            'leading' => (clone $mine)->where('leader_id', Auth::id())->whereNotIn('project_status', ['cancelled', 'archived'])->count(),
            'volunteering' => Project::query()
                ->whereHas('volunteers', fn (Builder $q) => $q->where('user_id', Auth::id()))
                ->whereNotIn('project_status', ['cancelled', 'archived'])
                ->count(),
            'updated' => $this->projectUpdates->count(),
        ];
    }

    #[Computed]
    public function eventStats(): array
    {
        $visible = Event::query()->visibleTo(Auth::user());

        return [
            'upcoming' => (clone $visible)->where('start_time', '>', now())->where('event_status', 'published')->count(),
            'volunteering' => Event::query()
                ->visibleTo(Auth::user())
                ->where('event_status', 'published')
                ->where('end_time', '>=', now())
                ->whereHas('volunteers', fn (Builder $q) => $q->where('user_id', Auth::id()))
                ->count(),
        ];
    }

    /**
     * Activity feed for projects the user leads, created, or volunteers on.
     *
     * @return Collection<int, array{project: Project, type: string, summary: string, at: \Illuminate\Support\Carbon}>
     */
    #[Computed]
    public function projectUpdates(): Collection
    {
        $since = now()->subDays(self::UPDATE_LOOKBACK_DAYS);
        $projectIds = $this->myProjectsQuery()->pluck('id');

        if ($projectIds->isEmpty()) {
            return collect();
        }

        $projects = Project::query()
            ->with(['leader', 'categories'])
            ->whereIn('id', $projectIds)
            ->get()
            ->keyBy('id');

        $activities = collect();

        $comments = ProjectComment::query()
            ->with('user')
            ->whereIn('project_id', $projectIds)
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(40)
            ->get();

        foreach ($comments as $comment) {
            $project = $projects->get($comment->project_id);
            if (! $project) {
                continue;
            }

            $actor = $comment->user?->name ?? __('Someone');
            $activities->push([
                'project' => $project,
                'type' => 'comment',
                'summary' => __(':name commented', ['name' => $actor]),
                'at' => $comment->created_at,
            ]);
        }

        $volunteers = ProjectVolunteer::query()
            ->with('user')
            ->whereIn('project_id', $projectIds)
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(40)
            ->get();

        foreach ($volunteers as $volunteer) {
            $project = $projects->get($volunteer->project_id);
            if (! $project) {
                continue;
            }

            $actor = $volunteer->user?->name ?? __('Someone');
            $summary = (int) $volunteer->user_id === Auth::id()
                ? __('You signed up as a volunteer')
                : __(':name signed up as a volunteer', ['name' => $actor]);

            $activities->push([
                'project' => $project,
                'type' => 'volunteer',
                'summary' => $summary,
                'at' => $volunteer->created_at,
            ]);
        }

        $images = ProjectImage::query()
            ->whereIn('project_id', $projectIds)
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(40)
            ->get();

        foreach ($images as $image) {
            $project = $projects->get($image->project_id);
            if (! $project) {
                continue;
            }

            $activities->push([
                'project' => $project,
                'type' => 'image',
                'summary' => __('New photo added'),
                'at' => $image->created_at,
            ]);
        }

        return $activities
            ->sortByDesc(fn (array $item) => $item['at']->timestamp)
            ->values()
            ->take(self::UPDATES_LIMIT);
    }

    /**
     * @return Collection<int, Project>
     */
    #[Computed]
    public function volunteerNeededProjects(): Collection
    {
        if ($this->projectsByCategory->isNotEmpty() || $this->projectUpdates->isNotEmpty()) {
            return collect();
        }

        return Project::query()
            ->with(['leader', 'categories'])
            ->where('volunteer_required', true)
            ->whereNotIn('project_status', ['draft', 'cancelled', 'archived', 'completed'])
            ->whereDoesntHave('volunteers', fn (Builder $q) => $q->where('user_id', Auth::id()))
            ->latest()
            ->limit(4)
            ->get();
    }

    /**
     * Active projects for the user, grouped by category name.
     *
     * @return Collection<string, Collection<int, Project>>
     */
    #[Computed]
    public function projectsByCategory(): Collection
    {
        $projects = $this->myProjectsQuery()
            ->with(['leader', 'categories'])
            ->whereNotIn('project_status', ['cancelled', 'archived'])
            ->orderBy('name')
            ->get();

        $grouped = collect();

        $categoryNames = ProjectCategory::query()
            ->whereHas('projects', function (Builder $query): void {
                $this->constrainToMyProjects($query);
                $query->whereNotIn('project_status', ['cancelled', 'archived']);
            })
            ->orderBy('name')
            ->pluck('name');

        foreach ($categoryNames as $name) {
            $items = $projects->filter(
                fn (Project $project) => $project->categories->contains('name', $name)
            )->values();

            if ($items->isNotEmpty()) {
                $grouped[$name] = $items;
            }
        }

        $uncategorized = $projects->filter(
            fn (Project $project) => $project->categories->isEmpty()
        )->values();

        if ($uncategorized->isNotEmpty()) {
            $grouped[__('Uncategorized')] = $uncategorized;
        }

        return $grouped;
    }

    /**
     * Upcoming events with ones the user volunteers on listed first.
     *
     * @return Collection<int, Event>
     */
    #[Computed]
    public function upcomingEvents(): Collection
    {
        $userId = Auth::id();

        $events = Event::query()
            ->visibleTo(Auth::user())
            ->with(['creator', 'volunteers'])
            ->where('event_status', 'published')
            ->where('end_time', '>=', now())
            ->orderBy('start_time')
            ->limit(20)
            ->get();

        return $events
            ->sortBy([
                fn (Event $event) => $event->volunteers->contains('user_id', $userId) ? 0 : 1,
                fn (Event $event) => $event->start_time->timestamp,
            ])
            ->values()
            ->take(self::EVENTS_LIMIT);
    }

    public function involvementLabel(Project $project): string
    {
        $userId = Auth::id();

        if ((int) $project->leader_id === $userId) {
            return __('Leading');
        }

        if ((int) $project->created_by === $userId) {
            return __('Created');
        }

        return __('Volunteering');
    }

    public function isVolunteeringOnEvent(Event $event): bool
    {
        return $event->volunteers->contains('user_id', Auth::id());
    }

    protected function myProjectsQuery(): Builder
    {
        return Project::query()->where(function (Builder $query): void {
            $this->constrainToMyProjects($query);
        });
    }

    protected function constrainToMyProjects(Builder $query): void
    {
        $userId = Auth::id();

        $query->where(function (Builder $inner) use ($userId): void {
            $inner->where('leader_id', $userId)
                ->orWhere('created_by', $userId)
                ->orWhereHas('volunteers', fn (Builder $volunteers) => $volunteers->where('user_id', $userId));
        });
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
