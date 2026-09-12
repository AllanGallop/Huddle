<?php

namespace App\Livewire\Reports;

use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use App\Services\ProjectReportService;
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

    public bool $include_comments = true;

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
        $this->include_comments = filter_var($query['include_comments'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    public function updatedStatuses(): void
    {
        $this->statuses = array_values(array_intersect($this->statuses, ['outstanding', 'in-progress']));

        if ($this->statuses === []) {
            $this->statuses = ['outstanding', 'in-progress'];
        }
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

    #[Computed]
    public function filterQuery(): array
    {
        $query = [
            'statuses' => $this->statuses,
            'include_comments' => $this->include_comments ? '1' : '0',
        ];

        if ($this->leader_id !== '') {
            $query['leader_id'] = $this->leader_id;
        }

        if ($this->due_date_from) {
            $query['due_date_from'] = $this->due_date_from;
        }

        if ($this->due_date_to) {
            $query['due_date_to'] = $this->due_date_to;
        }

        if ($this->volunteer_filter !== '') {
            $query['volunteer_filter'] = $this->volunteer_filter;
        }

        if ($this->financial_status !== '') {
            $query['financial_status'] = $this->financial_status;
        }

        if ($this->overdue_only) {
            $query['overdue_only'] = '1';
        }

        return $query;
    }

    #[Computed]
    public function preview()
    {
        $filters = $this->serviceFilters();

        return app(ProjectReportService::class)->projectsStatusData($filters);
    }

    #[Computed]
    public function filterSummary(): array
    {
        return app(ProjectReportService::class)->filterSummary(
            $this->serviceFilters(),
            $this->canViewFinancials,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function serviceFilters(): array
    {
        return [
            'statuses' => $this->statuses !== [] ? $this->statuses : ['outstanding', 'in-progress'],
            'leader_id' => $this->leader_id !== '' ? (int) $this->leader_id : null,
            'due_date_from' => $this->due_date_from,
            'due_date_to' => $this->due_date_to,
            'volunteer_filter' => $this->volunteer_filter !== '' ? $this->volunteer_filter : null,
            'financial_status' => $this->financial_status !== '' ? $this->financial_status : null,
            'overdue_only' => $this->overdue_only,
            'include_comments' => $this->include_comments,
        ];
    }

    public function render()
    {
        return view('livewire.reports.index');
    }
}
