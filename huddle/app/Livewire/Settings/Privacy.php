<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Privacy extends Component
{
    public bool $accept_policy = false;

    public function mount(): void
    {
        $this->accept_policy = Auth::user()->hasAcceptedPrivacyPolicy();
    }

    public function acceptPrivacyPolicy(): void
    {
        $this->validate([
            'accept_policy' => ['accepted'],
        ]);

        Auth::user()->acceptPrivacyPolicy();

        session()->flash('status', __('Privacy policy accepted.'));

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.privacy');
    }
}
