<?php

namespace App\Notifications\Concerns;

trait BuildsPasswordResetUrl
{
    protected function resetUrl(object $notifiable): string
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    protected function expireMinutes(): int
    {
        return (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
    }
}
