<?php

namespace App\Livewire\Forms;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Services\FormSubmissionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

class Take extends Component
{
    public Form $form;

    /** @var array<int, mixed> */
    public array $answers = [];

    public ?FormSubmission $submission = null;

    public bool $submitted = false;

    public function mount(Form $form): void
    {
        abort_unless($form->canTake(Auth::user()), 404);

        $form->load(['questions.options']);

        if ($form->questions->isEmpty()) {
            abort(404);
        }

        $this->form = $form;

        $existing = FormSubmission::query()
            ->where('form_id', $form->id)
            ->where('user_id', Auth::id())
            ->with('answers')
            ->first();

        if ($existing) {
            $this->submission = $existing;
            $this->submitted = true;

            foreach ($existing->answers as $answer) {
                if (array_key_exists('yes', $answer->value)) {
                    $this->answers[$answer->form_question_id] = $answer->value['yes'] ? '1' : '0';
                } else {
                    $this->answers[$answer->form_question_id] = (string) ($answer->value['option_id'] ?? '');
                }
            }
        }
    }

    public function title(): string
    {
        return $this->form->title;
    }

    public function submit(FormSubmissionService $service): void
    {
        if ($this->submitted) {
            return;
        }

        $this->submission = $service->submit($this->form, Auth::user(), $this->answers);
        $this->submitted = true;

        session()->flash('status', $this->resultMessage());
    }

    protected function resultMessage(): string
    {
        if (! $this->form->isExam() || $this->submission->passed === null) {
            return __('Thank you — your response has been recorded.');
        }

        return $this->submission->passed
            ? __('Exam passed! You scored :score of :max (:percent%).', [
                'score' => $this->submission->score,
                'max' => $this->submission->max_score,
                'percent' => $this->submission->scorePercentage(),
            ])
            : __('Exam not passed. You scored :score of :max (:percent%).', [
                'score' => $this->submission->score,
                'max' => $this->submission->max_score,
                'percent' => $this->submission->scorePercentage(),
            ]);
    }

    public function render()
    {
        return view('livewire.forms.take');
    }
}
