<?php

namespace App\Livewire\Forms\Manage;

use App\Models\Form;
use App\Models\FormQuestion;
use App\Models\FormQuestionOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

class Edit extends Component
{
    public ?Form $form = null;

    public string $title = '';

    public string $description = '';

    public string $type = Form::TYPE_SURVEY;

    public bool $is_published = false;

    public ?int $pass_percentage = 70;

    /** @var array<int, array<string, mixed>> */
    public array $questionDrafts = [];

    public function mount(?Form $form = null): void
    {
        if ($form?->exists) {
            $this->authorize('manage', $form);
            $form->load(['questions.options']);
            $this->form = $form;
            $this->title = $form->title;
            $this->description = $form->description ?? '';
            $this->type = $form->type;
            $this->is_published = $form->is_published;
            $this->pass_percentage = $form->pass_percentage ?? 70;
            $this->questionDrafts = $form->questions->map(fn (FormQuestion $question) => [
                'key' => (string) Str::uuid(),
                'id' => $question->id,
                'type' => $question->type,
                'body' => $question->body,
                'points' => $question->points,
                'correct_yes_no' => $question->correct_yes_no ?? true,
                'options' => $question->options->map(fn (FormQuestionOption $option) => [
                    'key' => (string) Str::uuid(),
                    'id' => $option->id,
                    'label' => $option->label,
                    'is_correct' => $option->is_correct,
                ])->all(),
            ])->all();
        } else {
            $this->authorize('create', Form::class);
        }
    }

    public function title(): string
    {
        return $this->form ? __('Edit form') : __('Create form');
    }

    public function updatedType(): void
    {
        if ($this->type === Form::TYPE_SURVEY) {
            $this->pass_percentage = null;
        } elseif ($this->pass_percentage === null) {
            $this->pass_percentage = 70;
        }
    }

    public function addQuestion(string $type): void
    {
        $draft = [
            'key' => (string) Str::uuid(),
            'id' => null,
            'type' => $type,
            'body' => '',
            'points' => $this->type === Form::TYPE_EXAM ? 1 : 0,
            'correct_yes_no' => true,
            'options' => [],
        ];

        if ($type === FormQuestion::TYPE_MULTIPLE_CHOICE) {
            $draft['options'] = [
                ['key' => (string) Str::uuid(), 'id' => null, 'label' => '', 'is_correct' => true],
                ['key' => (string) Str::uuid(), 'id' => null, 'label' => '', 'is_correct' => false],
            ];
        }

        $this->questionDrafts[] = $draft;
    }

    public function removeQuestion(string $key): void
    {
        $this->questionDrafts = array_values(array_filter(
            $this->questionDrafts,
            fn (array $draft): bool => $draft['key'] !== $key,
        ));
    }

    public function addOption(string $questionKey): void
    {
        foreach ($this->questionDrafts as $index => $draft) {
            if ($draft['key'] !== $questionKey) {
                continue;
            }

            $draft['options'][] = [
                'key' => (string) Str::uuid(),
                'id' => null,
                'label' => '',
                'is_correct' => false,
            ];
            $this->questionDrafts[$index] = $draft;

            break;
        }
    }

    public function removeOption(string $questionKey, string $optionKey): void
    {
        foreach ($this->questionDrafts as $index => $draft) {
            if ($draft['key'] !== $questionKey) {
                continue;
            }

            $draft['options'] = array_values(array_filter(
                $draft['options'],
                fn (array $option): bool => $option['key'] !== $optionKey,
            ));
            $this->questionDrafts[$index] = $draft;

            break;
        }
    }

    public function setCorrectOption(string $questionKey, string $optionKey): void
    {
        foreach ($this->questionDrafts as $index => $draft) {
            if ($draft['key'] !== $questionKey) {
                continue;
            }

            foreach ($draft['options'] as $optIndex => $option) {
                $draft['options'][$optIndex]['is_correct'] = $option['key'] === $optionKey;
            }
            $this->questionDrafts[$index] = $draft;

            break;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::in([Form::TYPE_SURVEY, Form::TYPE_EXAM])],
            'is_published' => ['boolean'],
            'pass_percentage' => [
                Rule::requiredIf($this->type === Form::TYPE_EXAM),
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'questionDrafts' => ['required', 'array', 'min:1'],
            'questionDrafts.*.type' => ['required', Rule::in([FormQuestion::TYPE_YES_NO, FormQuestion::TYPE_MULTIPLE_CHOICE])],
            'questionDrafts.*.body' => ['required', 'string', 'max:2000'],
            'questionDrafts.*.points' => ['integer', 'min:0', 'max:1000'],
            'questionDrafts.*.correct_yes_no' => ['boolean'],
            'questionDrafts.*.options' => ['array'],
            'questionDrafts.*.options.*.label' => ['required_with:questionDrafts.*.options', 'string', 'max:500'],
            'questionDrafts.*.options.*.is_correct' => ['boolean'],
        ]);

        $this->validateQuestionDrafts();

        DB::transaction(function () use ($validated): void {
            $data = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'type' => $validated['type'],
                'is_published' => $validated['is_published'],
                'pass_percentage' => $validated['type'] === Form::TYPE_EXAM
                    ? $validated['pass_percentage']
                    : null,
            ];

            if ($this->form) {
                $this->form->update($data);
                $form = $this->form;
            } else {
                $form = Form::create([
                    ...$data,
                    'created_by' => Auth::id(),
                ]);
            }

            $this->syncQuestions($form);

            $this->form = $form->fresh(['questions.options']);
        });

        session()->flash('status', __('Form saved.'));
        $this->redirect(route('forms.manage.edit', $this->form), navigate: true);
    }

    protected function validateQuestionDrafts(): void
    {
        foreach ($this->questionDrafts as $index => $draft) {
            if ($draft['type'] === FormQuestion::TYPE_MULTIPLE_CHOICE) {
                if (count($draft['options'] ?? []) < 2) {
                    $this->addError("questionDrafts.{$index}.options", __('Add at least two options.'));

                    continue;
                }

                if ($this->type === Form::TYPE_EXAM) {
                    $hasCorrect = collect($draft['options'])->contains(fn (array $o): bool => (bool) ($o['is_correct'] ?? false));
                    if (! $hasCorrect) {
                        $this->addError("questionDrafts.{$index}.options", __('Mark one option as correct.'));
                    }
                }
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages($this->getErrorBag()->toArray());
        }
    }

    protected function syncQuestions(Form $form): void
    {
        $keptQuestionIds = [];

        foreach ($this->questionDrafts as $sortOrder => $draft) {
            $questionData = [
                'sort_order' => $sortOrder,
                'type' => $draft['type'],
                'body' => $draft['body'],
                'points' => $this->type === Form::TYPE_EXAM ? (int) $draft['points'] : 0,
                'correct_yes_no' => $draft['type'] === FormQuestion::TYPE_YES_NO && $this->type === Form::TYPE_EXAM
                    ? (bool) $draft['correct_yes_no']
                    : null,
            ];

            if (! empty($draft['id'])) {
                $question = FormQuestion::query()->where('form_id', $form->id)->findOrFail($draft['id']);
                $question->update($questionData);
            } else {
                $question = $form->questions()->create($questionData);
            }

            $keptQuestionIds[] = $question->id;

            if ($question->isMultipleChoice()) {
                $this->syncOptions($question, $draft['options'] ?? []);
            } else {
                $question->options()->delete();
            }
        }

        $form->questions()->whereNotIn('id', $keptQuestionIds)->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $optionDrafts
     */
    protected function syncOptions(FormQuestion $question, array $optionDrafts): void
    {
        $keptOptionIds = [];

        foreach ($optionDrafts as $sortOrder => $draft) {
            $optionData = [
                'sort_order' => $sortOrder,
                'label' => $draft['label'],
                'is_correct' => $this->type === Form::TYPE_EXAM && (bool) ($draft['is_correct'] ?? false),
            ];

            if (! empty($draft['id'])) {
                $option = FormQuestionOption::query()->where('form_question_id', $question->id)->findOrFail($draft['id']);
                $option->update($optionData);
            } else {
                $option = $question->options()->create($optionData);
            }

            $keptOptionIds[] = $option->id;
        }

        $question->options()->whereNotIn('id', $keptOptionIds)->delete();
    }

    public function render()
    {
        return view('livewire.forms.manage.edit');
    }
}
