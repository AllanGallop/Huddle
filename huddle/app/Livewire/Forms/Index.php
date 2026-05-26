<?php

namespace App\Livewire\Forms;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Forms')]
class Index extends Component
{
    #[Computed]
    public function publishedForms()
    {
        return Form::query()
            ->where('is_published', true)
            ->withCount('questions')
            ->orderByDesc('updated_at')
            ->get();
    }

    #[Computed]
    public function mySubmissions()
    {
        return FormSubmission::query()
            ->where('user_id', Auth::id())
            ->with('form')
            ->orderByDesc('submitted_at')
            ->get()
            ->keyBy('form_id');
    }

    #[Computed]
    public function canManage(): bool
    {
        return Auth::user()->canManageForms();
    }

    public function render()
    {
        return view('livewire.forms.index');
    }
}
