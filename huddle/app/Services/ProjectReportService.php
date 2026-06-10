<?php

namespace App\Services;

use App\Models\Project;
use Barryvdh\DomPDF\PDF as DomPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProjectReportService
{
    /**
     * @return Collection<int, Project>
     */
    public function activeProjects(array $filters = []): Collection
    {
        $statuses = $filters['statuses'] ?? ['outstanding', 'in-progress'];

        return Project::query()
            ->whereIn('project_status', $statuses)
            ->with([
                'leader',
                'creator',
                'volunteers.user',
                'topLevelComments' => fn ($query) => $query
                    ->with(['user', 'replies.user'])
                    ->oldest(),
                'topLevelComments.replies' => fn ($query) => $query->oldest(),
            ])
            ->withCount(['comments', 'volunteers'])
            ->when($filters['leader_id'] ?? null, fn (Builder $query, int $leaderId) => $query->where('leader_id', $leaderId))
            ->when($filters['due_date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('due_date', '>=', $date))
            ->when($filters['due_date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('due_date', '<=', $date))
            ->when(
                ($filters['volunteer_filter'] ?? null) === 'required',
                fn (Builder $query) => $query->where('volunteer_required', true),
            )
            ->when(
                ($filters['volunteer_filter'] ?? null) === 'not_required',
                fn (Builder $query) => $query->where('volunteer_required', false),
            )
            ->when($filters['financial_status'] ?? null, fn (Builder $query, string $status) => $query->where('financial_status', $status))
            ->when(
                $filters['overdue_only'] ?? false,
                fn (Builder $query) => $query
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', now()->toDateString()),
            )
            ->orderByRaw("
                CASE project_status
                    WHEN 'outstanding' THEN 0
                    WHEN 'in-progress' THEN 1
                    ELSE 2
                END
            ")
            ->orderByRaw('due_date is null, due_date asc')
            ->orderBy('name')
            ->get();
    }

    public function projectsStatusData(array $filters = []): array
    {
        $projects = $this->activeProjects($filters);

        return [
            'projects' => $projects,
            'generatedAt' => now(),
            'filters' => $filters,
        ];
    }

    public function projectsStatusPdf(array $filters = [], array $filterSummary = [], bool $showFinancials = true): DomPdf
    {
        return Pdf::loadView('reports.projects-status', [
            ...$this->projectsStatusData($filters),
            'filterSummary' => $filterSummary,
            'showFinancials' => $showFinancials,
            'forPdf' => true,
        ])->setPaper('a4', 'portrait');
    }

    public function projectsStatusFilename(): string
    {
        return 'projects-status-report-'.now()->format('Y-m-d').'.pdf';
    }
}
