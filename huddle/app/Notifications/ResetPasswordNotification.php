<?php

namespace App\Notifications;

use App\Notifications\Concerns\BuildsPasswordResetUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use BuildsPasswordResetUrl, Queueable;

    public function __construct(#[\SensitiveParameter] public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Reset your :app password', ['app' => config('app.name')]))
            ->markdown('mail.reset-password', [
                'user' => $notifiable,
                'url' => $this->resetUrl($notifiable),
                'expireMinutes' => $this->expireMinutes(),
            ]);
    }
}
