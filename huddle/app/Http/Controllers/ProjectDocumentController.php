<?php

namespace App\Http\Controllers;

use App\Mail\ProjectInvoiceMail;
use App\Mail\ProjectQuoteMail;
use App\Models\OrganizationSetting;
use App\Models\Project;
use App\Services\ProjectDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ProjectDocumentController extends Controller
{
    public function __construct(
        protected ProjectDocumentService $documents,
    ) {}

    public function quote(Request $request, Project $project): View
    {
        $this->authorizeFinancials($request, $project);

        return view('projects.quote', [
            'project' => $this->documents->prepareProject($project),
            'forPdf' => false,
        ]);
    }

    public function invoice(Request $request, Project $project): View
    {
        $this->authorizeFinancials($request, $project);

        return view('projects.invoice', [
            'project' => $this->documents->prepareProject($project),
            'forPdf' => false,
            'bank' => OrganizationSetting::instance(),
        ]);
    }

    public function quotePdf(Request $request, Project $project): Response
    {
        $this->authorizeFinancials($request, $project);
        $this->ensureQuoteAmount($project);

        return $this->documents
            ->quotePdf($project)
            ->download($this->documents->quoteFilename($project));
    }

    public function invoicePdf(Request $request, Project $project): Response
    {
        $this->authorizeFinancials($request, $project);
        $this->ensureInvoiceAmount($project);

        return $this->documents
            ->invoicePdf($project)
            ->download($this->documents->invoiceFilename($project));
    }

    public function emailQuote(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeFinancials($request, $project);
        $this->ensureQuoteAmount($project);

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $pdf = $this->documents->quotePdf($project);

        Mail::to($validated['email'])->send(new ProjectQuoteMail(
            $project,
            $pdf->output(),
            $this->documents->quoteFilename($project),
        ));

        return back()->with('status', __('Quote PDF sent to :email.', ['email' => $validated['email']]));
    }

    public function emailInvoice(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeFinancials($request, $project);
        $this->ensureInvoiceAmount($project);

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $pdf = $this->documents->invoicePdf($project);

        Mail::to($validated['email'])->send(new ProjectInvoiceMail(
            $project,
            $pdf->output(),
            $this->documents->invoiceFilename($project),
        ));

        return back()->with('status', __('Invoice PDF sent to :email.', ['email' => $validated['email']]));
    }

    protected function authorizeFinancials(Request $request, Project $project): void
    {
        abort_unless(
            $request->user()?->canManageProjectFinancials($project),
            403,
        );
    }

    protected function ensureQuoteAmount(Project $project): void
    {
        abort_unless($project->quote_amount, 422, __('Set a quote amount before generating this document.'));
    }

    protected function ensureInvoiceAmount(Project $project): void
    {
        abort_unless($project->invoice_amount, 422, __('Set an invoice amount before generating this document.'));
    }
}
