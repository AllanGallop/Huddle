<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Projects')]
class Index extends Component
{
    public string $search = '';

    public string $statusFilter = '';

    public string $leaderFilter = '';

    public string $volunteersFilter = '';

    public string $financialStatusFilter = '';

    public bool $mineOnly = false;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public bool $showCreateModal = false;

    public string $name = '';

    public string $description = '';

    public string $project_status = 'draft';

    public bool $volunteer_required = false;

    public ?int $leader_id = null;

    public ?string $due_date = null;

    public function mount(): void
    {
        $this->leader_id = Auth::id();
    }

    public function sort(string $column): void
    {
        $allowed = ['name', 'leader', 'status', 'due_date', 'created_at', 'updated_at', 'volunteers', 'financial'];

        if (! in_array($column, $allowed, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = in_array($column, ['created_at', 'updated_at', 'due_date', 'volunteers'], true) ? 'desc' : 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'leaderFilter', 'volunteersFilter', 'financialStatusFilter', 'mineOnly']);
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->statusFilter !== ''
            || $this->leaderFilter !== ''
            || $this->volunteersFilter !== ''
            || $this->financialStatusFilter !== ''
            || $this->mineOnly;
    }

    #[Computed]
    public function projects()
    {
        $query = Project::query()
            ->with(['leader', 'creator'])
            ->withCount(['comments', 'volunteers', 'images']);

        $this->applyFilters($query);
        $this->applySorting($query);

        return $query->get();
    }

    #[Computed]
    public function users()
    {
        return User::query()->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function canFilterFinancials(): bool
    {
        $user = Auth::user();

        return $user->isAdmin()
            || Project::query()->where('leader_id', $user->id)->exists();
    }

    #[Computed]
    public function leaders()
    {
        return User::query()
            ->whereIn('id', Project::query()->distinct()->pluck('leader_id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function viewProject(int $projectId)
    {
        return $this->redirect(route('projects.show', $projectId), navigate: true);
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

    public function createProject(): void
    {
        $this->authorize('create', Project::class);

        $user = Auth::user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'project_status' => ['required', 'in:'.implode(',', Project::STATUSES)],
            'volunteer_required' => ['boolean'],
            'due_date' => ['nullable', 'date'],
        ];

        if ($user->can('assignLeader', Project::class)) {
            $rules['leader_id'] = ['required', 'exists:users,id'];
        }

        $validated = $this->validate($rules);

        if (! $user->can('assignLeader', Project::class)) {
            $validated['leader_id'] = $user->id;
        }

        $project = Project::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        $this->redirect(route('projects.show', $project), navigate: true);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhereHas('leader', fn (Builder $leader) => $leader->where('name', 'like', $term));
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('project_status', $this->statusFilter);
        }

        if ($this->leaderFilter !== '') {
            $query->where('leader_id', $this->leaderFilter);
        }

        if ($this->volunteersFilter === 'required') {
            $query->where('volunteer_required', true);
        } elseif ($this->volunteersFilter === 'not_required') {
            $query->where('volunteer_required', false);
        }

        if ($this->mineOnly) {
            $query->where('created_by', Auth::id());
        }

        if ($this->financialStatusFilter !== '') {
            $query->where('financial_status', $this->financialStatusFilter);
        }
    }

    protected function applySorting(Builder $query): void
    {
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        match ($this->sortBy) {
            'name' => $query->orderBy('name', $direction),
            'status' => $query->orderBy('project_status', $direction),
            'leader' => $query
                ->join('users as leader_sort', 'projects.leader_id', '=', 'leader_sort.id')
                ->orderBy('leader_sort.name', $direction)
                ->select('projects.*'),
            'updated_at' => $query->orderBy('updated_at', $direction),
            'due_date' => $query->orderByRaw('due_date is null, due_date '.$direction),
            'financial' => $query->orderByRaw('financial_status is null, financial_status '.$direction),
            'volunteers' => $query->orderBy('volunteers_count', $direction),
            default => $query->orderBy('created_at', $direction),
        };
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'description', 'project_status', 'volunteer_required', 'due_date']);
        $this->project_status = 'draft';
        $this->volunteer_required = false;
        $this->leader_id = Auth::id();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.projects.index');
    }
}
