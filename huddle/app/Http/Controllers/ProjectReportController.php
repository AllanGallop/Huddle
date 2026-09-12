<?php

namespace App\Http\Controllers;

use App\Mail\ProjectStatusReportMail;
use App\Models\Report;
use App\Services\ProjectReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ProjectReportController extends Controller
{
    public function __construct(
        protected ProjectReportService $reports,
    ) {}

    public function projectsStatus(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $showFinancials = $this->showFinancials($request);
        $filterSummary = $this->reports->filterSummary($filters, $showFinancials);

        return view('reports.projects-status', [
            ...$this->reports->projectsStatusData($filters),
            'filterSummary' => $filterSummary,
            'showFinancials' => $showFinancials,
            'forPdf' => false,
        ]);
    }

    public function projectsStatusPdf(Request $request): Response
    {
        $filters = $this->validatedFilters($request);
        $showFinancials = $this->showFinancials($request);
        $filterSummary = $this->reports->filterSummary($filters, $showFinancials);

        return $this->reports
            ->projectsStatusPdf($filters, $filterSummary, $showFinancials)
            ->download($this->reports->projectsStatusFilename());
    }

    public function emailProjectsStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $filters = $this->validatedFilters($request);
        $showFinancials = $this->showFinancials($request);
        $filterSummary = $this->reports->filterSummary($filters, $showFinancials);

        $pdf = $this->reports->projectsStatusPdf($filters, $filterSummary, $showFinancials);

        Mail::to($validated['email'])->send(new ProjectStatusReportMail(
            $pdf->output(),
            $this->reports->projectsStatusFilename(),
        ));

        return back()->with('status', __('Projects report PDF sent to :email.', ['email' => $validated['email']]));
    }

    protected function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'statuses' => ['nullable', 'array'],
            'statuses.*' => ['string', 'in:outstanding,in-progress'],
            'leader_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_date_from' => ['nullable', 'date'],
            'due_date_to' => ['nullable', 'date', 'after_or_equal:due_date_from'],
            'volunteer_filter' => ['nullable', 'in:,required,not_required'],
            'financial_status' => ['nullable', 'in:,'.implode(',', \App\Models\Project::FINANCIAL_STATUSES)],
            'overdue_only' => ['nullable', 'boolean'],
            'include_comments' => ['nullable'],
        ]);

        $statuses = array_values(array_unique($validated['statuses'] ?? ['outstanding', 'in-progress']));

        return [
            'statuses' => $statuses !== [] ? $statuses : ['outstanding', 'in-progress'],
            'leader_id' => isset($validated['leader_id']) ? (int) $validated['leader_id'] : null,
            'due_date_from' => $validated['due_date_from'] ?? null,
            'due_date_to' => $validated['due_date_to'] ?? null,
            'volunteer_filter' => $validated['volunteer_filter'] ?? null,
            'financial_status' => $validated['financial_status'] ?? null,
            'overdue_only' => (bool) ($validated['overdue_only'] ?? false),
            'include_comments' => $request->has('include_comments')
                ? filter_var($request->input('include_comments'), FILTER_VALIDATE_BOOLEAN)
                : true,
        ];
    }

    protected function showFinancials(Request $request): bool
    {
        return (bool) $request->user()?->can('viewFinancials', Report::class);
    }
}
