<?php

namespace App\Livewire\Forms\Manage;

use App\Models\Form;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manage forms')]
class Index extends Component
{
    #[Computed]
    public function forms()
    {
        return Form::query()
            ->with('creator')
            ->withCount(['questions', 'submissions'])
            ->orderByDesc('updated_at')
            ->get();
    }

    public function deleteForm(int $formId): void
    {
        $form = Form::query()->findOrFail($formId);
        abort_unless($form->canManage(Auth::user()), 403);

        $form->delete();

        unset($this->forms);
        session()->flash('status', __('Form deleted.'));
    }

    public function render()
    {
        return view('livewire.forms.manage.index');
    }
}
