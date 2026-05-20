<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectQuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Project $project,
        public string $pdfBinary,
        public string $filename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Quote: :project', ['project' => $this->project->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.project-document',
            with: [
                'documentLabel' => __('quote'),
                'project' => $this->project,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfBinary, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
