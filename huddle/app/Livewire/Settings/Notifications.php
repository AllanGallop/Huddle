<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notifications extends Component
{
    public bool $digest_opt_out = false;

    public function mount(): void
    {
        $this->digest_opt_out = (bool) Auth::user()->digest_opt_out;
    }

    public function save(): void
    {
        $user = Auth::user();

        $user->forceFill([
            'digest_opt_out' => $this->digest_opt_out,
        ])->save();

        if (! $this->digest_opt_out) {
            session()->flash('status', __('Community digest emails are enabled.'));
        } else {
            session()->flash('status', __('You will not receive community digest emails.'));
        }

        $this->dispatch('notifications-saved');
    }

    public function render()
    {
        return view('livewire.settings.notifications');
    }
}
