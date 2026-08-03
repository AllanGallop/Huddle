<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Project;
use App\Models\ProjectCategory;
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

    public const UPDATES_LIMIT = 8;

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
            'updated' => (clone $mine)
                ->where('updated_at', '>=', now()->subDays(self::UPDATE_LOOKBACK_DAYS))
                ->count(),
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
     * Recently updated projects the user leads, created, or volunteers on.
     *
     * @return Collection<int, Project>
     */
    #[Computed]
    public function projectUpdates(): Collection
    {
        return $this->myProjectsQuery()
            ->with(['leader', 'categories'])
            ->where('updated_at', '>=', now()->subDays(self::UPDATE_LOOKBACK_DAYS))
            ->orderByDesc('updated_at')
            ->limit(self::UPDATES_LIMIT)
            ->get();
    }

    /**
     * Active projects for the user, grouped by category name.
     * Projects with multiple categories appear in each group.
     * Uncategorized projects are under a dedicated key.
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

    #[Computed]
    public function upcomingEvents()
    {
        return Event::query()
            ->visibleTo(Auth::user())
            ->with('creator')
            ->where('event_status', 'published')
            ->where('end_time', '>=', now())
            ->orderBy('start_time')
            ->limit(4)
            ->get();
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
