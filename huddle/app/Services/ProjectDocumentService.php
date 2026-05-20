<?php

namespace App\Services;

use App\Models\OrganizationSetting;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;

class ProjectDocumentService
{
    public function prepareProject(Project $project): Project
    {
        return $project->load(['leader', 'creator']);
    }

    public function quotePdf(Project $project): \Barryvdh\DomPDF\PDF
    {
        $project = $this->prepareProject($project);

        return Pdf::loadView('projects.quote', [
            'project' => $project,
            'forPdf' => true,
        ])->setPaper('a4');
    }

    public function invoicePdf(Project $project): \Barryvdh\DomPDF\PDF
    {
        $project = $this->prepareProject($project);

        return Pdf::loadView('projects.invoice', [
            'project' => $project,
            'forPdf' => true,
            'bank' => OrganizationSetting::instance(),
        ])->setPaper('a4');
    }

    public function quoteFilename(Project $project): string
    {
        return 'quote-'.str($project->name)->slug().'-'.$project->id.'.pdf';
    }

    public function invoiceFilename(Project $project): string
    {
        return 'invoice-'.str($project->name)->slug().'-'.$project->id.'.pdf';
    }
}
