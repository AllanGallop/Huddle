<?php

namespace App\Livewire\Projects;

use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectCategory;
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

    public string $statusFilter = 'active';

    public string $leaderFilter = '';

    public string $categoryFilter = '';

    public string $customerFilter = '';

    public string $volunteersFilter = '';

    public string $financialStatusFilter = '';

    public bool $mineOnly = false;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public bool $showCreateModal = false;

    public int $createStep = 1;

    public string $name = '';

    public string $description = '';

    public string $project_status = 'draft';

    public bool $volunteer_required = false;

    public ?int $leader_id = null;

    public ?string $due_date = null;

    public ?int $customer_id = null;

    public string $new_customer_name = '';

    public string $new_customer_address = '';

    public string $new_customer_email = '';

    public string $new_customer_telephone = '';

    public string $new_customer_type = 'domestic';

    /** @var array<int> */
    public array $assignedCategoryIds = [];

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
        $this->reset(['search', 'leaderFilter', 'categoryFilter', 'customerFilter', 'volunteersFilter', 'financialStatusFilter', 'mineOnly']);
        $this->statusFilter = 'active';
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || ($this->statusFilter !== '' && $this->statusFilter !== 'active')
            || $this->leaderFilter !== ''
            || $this->categoryFilter !== ''
            || $this->customerFilter !== ''
            || $this->volunteersFilter !== ''
            || $this->financialStatusFilter !== ''
            || $this->mineOnly;
    }

    #[Computed]
    public function newestProjects()
    {
        $query = Project::query()
            ->with([
                'leader',
                'categories',
                'customer:id,name,type',
            ])
            ->withCount(['comments', 'volunteers', 'images']);

        $this->applyFilters($query);

        return $query->orderByDesc('created_at')->limit(4)->get();
    }

    #[Computed]
    public function projects()
    {
        $query = Project::query()
            ->with([
                'leader',
                'creator',
                'categories',
                'customer:id,name,type',
            ])
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
    public function categories()
    {
        return ProjectCategory::query()->orderBy('name')->get();
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'type']);
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

    public function goToNewCustomerStep(): void
    {
        $this->authorize('create', Project::class);

        $this->validateProjectDetails();
        $this->customer_id = null;
        $this->createStep = 2;
        $this->resetValidation();
    }

    public function backToProjectStep(): void
    {
        $this->createStep = 1;
        $this->resetValidation();
    }

    public function createProject(): void
    {
        $this->authorize('create', Project::class);

        $this->validateProjectDetails();

        if ($this->createStep === 2) {
            $customer = $this->createCustomerFromForm();
            $this->customer_id = $customer->id;
            unset($this->customers);
        }

        $user = Auth::user();
        $categoryIds = $this->assignedCategoryIds;

        $project = Project::create([
            'name' => $this->name,
            'description' => $this->description,
            'project_status' => $this->project_status,
            'volunteer_required' => $this->volunteer_required,
            'due_date' => $this->due_date ?: null,
            'customer_id' => $this->customer_id ?: null,
            'leader_id' => $user->can('assignLeader', Project::class)
                ? $this->leader_id
                : $user->id,
            'created_by' => Auth::id(),
        ]);

        $project->categories()->sync($categoryIds);

        $this->redirect(route('projects.show', $project), navigate: true);
    }

    protected function validateProjectDetails(): void
    {
        $user = Auth::user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'project_status' => ['required', 'in:'.implode(',', Project::STATUSES)],
            'volunteer_required' => ['boolean'],
            'due_date' => ['nullable', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'assignedCategoryIds' => ['array'],
            'assignedCategoryIds.*' => ['integer', 'exists:project_categories,id'],
        ];

        if ($user->can('assignLeader', Project::class)) {
            $rules['leader_id'] = ['required', 'exists:users,id'];
        }

        $this->validate($rules);
    }

    protected function createCustomerFromForm(): Customer
    {
        $validated = $this->validate([
            'new_customer_name' => ['required', 'string', 'max:255'],
            'new_customer_address' => ['nullable', 'string', 'max:2000'],
            'new_customer_email' => ['nullable', 'email', 'max:255'],
            'new_customer_telephone' => ['nullable', 'string', 'max:50'],
            'new_customer_type' => ['required', 'in:'.implode(',', Customer::TYPES)],
        ]);

        return Customer::create([
            'name' => $validated['new_customer_name'],
            'address' => $validated['new_customer_address'] ?: null,
            'email' => $validated['new_customer_email'] ?: null,
            'telephone' => $validated['new_customer_telephone'] ?: null,
            'type' => $validated['new_customer_type'],
        ]);
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhereHas('leader', fn (Builder $leader) => $leader->where('name', 'like', $term))
                    ->orWhereHas('categories', fn (Builder $category) => $category->where('name', 'like', $term))
                    ->orWhereHas('customer', function (Builder $customer) use ($term) {
                        $customer->where('name', 'like', $term);
                    });
            });
        }

        if ($this->statusFilter === 'active') {
            $query->whereNotIn('project_status', ['completed', 'cancelled']);
        } elseif ($this->statusFilter !== '') {
            $query->where('project_status', $this->statusFilter);
        }

        if ($this->leaderFilter !== '') {
            $query->where('leader_id', $this->leaderFilter);
        }

        if ($this->categoryFilter !== '') {
            $query->whereHas(
                'categories',
                fn (Builder $category) => $category->where('project_categories.id', $this->categoryFilter)
            );
        }

        if ($this->customerFilter !== '') {
            $query->where('customer_id', $this->customerFilter);
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
            'volunteers' => $query->orderBy('volunteer_required', $direction),
            default => $query->orderBy('created_at', $direction),
        };
    }

    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'description',
            'project_status',
            'volunteer_required',
            'due_date',
            'customer_id',
            'assignedCategoryIds',
            'createStep',
            'new_customer_name',
            'new_customer_address',
            'new_customer_email',
            'new_customer_telephone',
        ]);
        $this->createStep = 1;
        $this->project_status = 'draft';
        $this->volunteer_required = false;
        $this->leader_id = Auth::id();
        $this->customer_id = null;
        $this->new_customer_type = 'domestic';
        $this->assignedCategoryIds = [];
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.projects.index');
    }
}
