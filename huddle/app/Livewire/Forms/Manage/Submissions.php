<?php

namespace App\Livewire\Forms\Manage;

use App\Models\Form;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

class Submissions extends Component
{
    public Form $form;

    public function mount(Form $form): void
    {
        abort_unless($form->canManage(Auth::user()), 403);

        $this->form = $form;
    }

    public function title(): string
    {
        return $this->form->title.' — '.__('Responses');
    }

    #[Computed]
    public function submissions()
    {
        return $this->form->submissions()
            ->with('user')
            ->orderByDesc('submitted_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.forms.manage.submissions');
    }
}
