<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function projectStats(): array
    {
        return [
            'total' => Project::count(),
            'in_progress' => Project::where('project_status', 'in-progress')->count(),
            'completed' => Project::where('project_status', 'completed')->count(),
            'volunteers' => Project::where('volunteer_required', true)->count(),
        ];
    }

    #[Computed]
    public function eventStats(): array
    {
        $visible = Event::query()->visibleTo(Auth::user());

        return [
            'total' => (clone $visible)->count(),
            'upcoming' => (clone $visible)->where('start_time', '>', now())->where('event_status', 'published')->count(),
            'ongoing' => (clone $visible)->where('start_time', '<=', now())->where('end_time', '>=', now())->where('event_status', 'published')->count(),
            'volunteers' => (clone $visible)->where('volunteer_required', true)->where('event_status', 'published')->count(),
        ];
    }

    #[Computed]
    public function recentProjects()
    {
        return Project::query()
            ->with(['leader', 'creator'])
            ->latest()
            ->limit(4)
            ->get();
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

    public function render()
    {
        return view('livewire.dashboard');
    }
}
