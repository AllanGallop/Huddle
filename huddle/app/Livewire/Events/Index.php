<?php

namespace App\Livewire\Events;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Events')]
class Index extends Component
{
    public string $search = '';

    public string $statusFilter = '';

    public string $typeFilter = '';

    public string $timingFilter = '';

    public string $volunteersFilter = '';

    public bool $mineOnly = false;

    public string $sortBy = 'start_time';

    public string $sortDirection = 'asc';

    public bool $showCreateModal = false;

    public string $name = '';

    public string $description = '';

    public string $location = '';

    public string $start_time = '';

    public string $end_time = '';

    public string $event_type = 'public';

    public string $event_status = 'draft';

    public bool $volunteer_required = false;

    public function sort(string $column): void
    {
        $allowed = ['name', 'status', 'type', 'start_time', 'volunteers'];

        if (! in_array($column, $allowed, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = in_array($column, ['start_time'], true) ? 'asc' : 'desc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'typeFilter', 'timingFilter', 'volunteersFilter', 'mineOnly']);
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->statusFilter !== ''
            || $this->typeFilter !== ''
            || $this->timingFilter !== ''
            || $this->volunteersFilter !== ''
            || $this->mineOnly;
    }

    #[Computed]
    public function events()
    {
        $query = Event::query()
            ->visibleTo(Auth::user())
            ->with('creator')
            ->withCount(['comments', 'volunteers']);

        $this->applyFilters($query);
        $this->applySorting($query);

        return $query->get();
    }

    public function viewEvent(int $eventId)
    {
        return $this->redirect(route('events.show', $eventId), navigate: true);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function createEvent(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:500'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'event_type' => ['required', 'in:'.implode(',', Event::TYPES)],
            'event_status' => ['required', 'in:'.implode(',', Event::STATUSES)],
            'volunteer_required' => ['boolean'],
        ]);

        $event = Event::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        $this->redirect(route('events.show', $event), navigate: true);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('location', 'like', $term)
                    ->orWhereHas('creator', fn (Builder $creator) => $creator->where('name', 'like', $term));
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('event_status', $this->statusFilter);
        }

        if ($this->typeFilter !== '') {
            $query->where('event_type', $this->typeFilter);
        }

        if ($this->timingFilter === 'upcoming') {
            $query->where('start_time', '>', now());
        } elseif ($this->timingFilter === 'ongoing') {
            $query->where('start_time', '<=', now())->where('end_time', '>=', now());
        } elseif ($this->timingFilter === 'past') {
            $query->where('end_time', '<', now());
        }

        if ($this->volunteersFilter === 'required') {
            $query->where('volunteer_required', true);
        } elseif ($this->volunteersFilter === 'not_required') {
            $query->where('volunteer_required', false);
        }

        if ($this->mineOnly) {
            $query->where('created_by', Auth::id());
        }
    }

    protected function applySorting(Builder $query): void
    {
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        match ($this->sortBy) {
            'name' => $query->orderBy('name', $direction),
            'status' => $query->orderBy('event_status', $direction),
            'type' => $query->orderBy('event_type', $direction),
            'volunteers' => $query->orderBy('volunteers_count', $direction),
            default => $query->orderBy('start_time', $direction),
        };
    }

    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'description',
            'location',
            'start_time',
            'end_time',
            'event_type',
            'event_status',
            'volunteer_required',
        ]);
        $this->event_type = 'public';
        $this->event_status = 'draft';
        $this->volunteer_required = false;

        $defaultStart = now()->addWeek()->startOfHour();
        $this->start_time = $defaultStart->format('Y-m-d\TH:i');
        $this->end_time = $defaultStart->copy()->addHours(2)->format('Y-m-d\TH:i');

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.events.index');
    }
}
