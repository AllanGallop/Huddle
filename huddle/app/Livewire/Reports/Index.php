<?php

namespace App\Livewire\Reports;

use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Reports')]
class Index extends Component
{
    public array $statuses = ['outstanding', 'in-progress'];

    public string $leader_id = '';

    public ?string $due_date_from = null;

    public ?string $due_date_to = null;

    public string $volunteer_filter = '';

    public string $financial_status = '';

    public bool $overdue_only = false;

    public function mount(): void
    {
        $query = request()->query();

        $statuses = array_values(array_intersect(
            (array) ($query['statuses'] ?? $this->statuses),
            ['outstanding', 'in-progress'],
        ));

        $this->statuses = $statuses !== [] ? $statuses : ['outstanding', 'in-progress'];
        $this->leader_id = (string) ($query['leader_id'] ?? '');
        $this->due_date_from = $query['due_date_from'] ?? null;
        $this->due_date_to = $query['due_date_to'] ?? null;
        $this->volunteer_filter = (string) ($query['volunteer_filter'] ?? '');
        $this->financial_status = (string) ($query['financial_status'] ?? '');
        $this->overdue_only = filter_var($query['overdue_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    #[Computed]
    public function canViewFinancials(): bool
    {
        return Auth::user()->can('viewFinancials', Report::class);
    }

    #[Computed]
    public function leaders()
    {
        return User::query()
            ->whereIn('id', Project::query()->distinct()->pluck('leader_id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.reports.index');
    }
}
