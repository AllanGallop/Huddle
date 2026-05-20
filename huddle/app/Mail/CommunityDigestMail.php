<?php

namespace App\Mail;

use App\Data\CommunityDigest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CommunityDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $unsubscribeUrl;

    public function __construct(
        public User $user,
        public CommunityDigest $digest,
    ) {
        $this->unsubscribeUrl = URL::signedRoute('digest.unsubscribe', ['user' => $user->id]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your :app community digest', ['app' => config('app.name')]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.community-digest',
        );
    }
}
