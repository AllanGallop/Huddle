<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormQuestion;
use App\Models\FormSubmission;
use App\Models\FormSubmissionAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FormSubmissionService
{
    /**
     * @param  array<int, mixed>  $answers  question_id => answer value
     */
    public function submit(Form $form, User $user, array $answers): FormSubmission
    {
        if ($form->submissions()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'form' => __('You have already submitted this form.'),
            ]);
        }

        $form->load(['questions.options']);

        $this->validateAnswers($form, $answers);

        return DB::transaction(function () use ($form, $user, $answers): FormSubmission {
            [$score, $maxScore, $passed] = $this->scoreSubmission($form, $answers);

            $submission = FormSubmission::create([
                'form_id' => $form->id,
                'user_id' => $user->id,
                'submitted_at' => now(),
                'score' => $score,
                'max_score' => $maxScore,
                'passed' => $passed,
            ]);

            foreach ($form->questions as $question) {
                FormSubmissionAnswer::create([
                    'form_submission_id' => $submission->id,
                    'form_question_id' => $question->id,
                    'value' => $this->normalizeAnswer($question, $answers[$question->id]),
                ]);
            }

            return $submission->load('answers.question');
        });
    }

    /**
     * @param  array<int, mixed>  $answers
     */
    protected function validateAnswers(Form $form, array $answers): void
    {
        $errors = [];

        foreach ($form->questions as $question) {
            if (! array_key_exists($question->id, $answers)) {
                $errors["answers.{$question->id}"] = __('This question is required.');

                continue;
            }

            $value = $answers[$question->id];

            if ($question->isYesNo()) {
                if (! in_array($value, [true, false, '1', '0', 1, 0, 'true', 'false'], true)) {
                    $errors["answers.{$question->id}"] = __('Please select yes or no.');
                }
            } elseif ($question->isMultipleChoice()) {
                $optionId = (int) $value;
                if (! $question->options->contains('id', $optionId)) {
                    $errors["answers.{$question->id}"] = __('Please select a valid option.');
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @return array{0: ?int, 1: ?int, 2: ?bool}
     */
    protected function scoreSubmission(Form $form, array $answers): array
    {
        if (! $form->isExam()) {
            return [null, null, null];
        }

        $score = 0;
        $maxScore = 0;

        foreach ($form->questions as $question) {
            $maxScore += $question->points;

            if ($this->isAnswerCorrect($question, $answers[$question->id])) {
                $score += $question->points;
            }
        }

        $passed = null;
        if ($form->pass_percentage !== null && $maxScore > 0) {
            $percentage = ($score / $maxScore) * 100;
            $passed = $percentage >= $form->pass_percentage;
        }

        return [$score, $maxScore, $passed];
    }

    public function isAnswerCorrect(FormQuestion $question, mixed $rawAnswer): bool
    {
        if ($question->isYesNo()) {
            $answer = filter_var($rawAnswer, FILTER_VALIDATE_BOOLEAN);

            return $question->correct_yes_no !== null && $answer === (bool) $question->correct_yes_no;
        }

        $optionId = (int) $rawAnswer;

        return $question->options->firstWhere('id', $optionId)?->is_correct === true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeAnswer(FormQuestion $question, mixed $rawAnswer): array
    {
        if ($question->isYesNo()) {
            return ['yes' => filter_var($rawAnswer, FILTER_VALIDATE_BOOLEAN)];
        }

        return ['option_id' => (int) $rawAnswer];
    }
}
