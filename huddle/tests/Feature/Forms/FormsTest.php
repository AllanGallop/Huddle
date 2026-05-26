<?php

namespace Tests\Feature\Forms;

use App\Livewire\Forms\Manage\Edit;
use App\Livewire\Forms\Take;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use App\Models\UserFlags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_access_form_management(): void
    {
        $member = User::factory()->create(['role_id' => 2]);

        $this->actingAs($member)
            ->get(route('forms.manage.index'))
            ->assertForbidden();
    }

    public function test_mentor_can_create_exam_and_member_receives_pass_fail(): void
    {
        $mentor = User::factory()->create(['role_id' => 2]);
        $mentorTag = UserFlags::create(['name' => 'Mentor', 'description' => 'Mentor']);
        $mentor->flags()->attach($mentorTag);

        $member = User::factory()->create(['role_id' => 2, 'name' => 'Test Member']);

        Livewire::actingAs($mentor)
            ->test(Edit::class)
            ->set('title', 'Safety exam')
            ->set('description', 'Annual safety check')
            ->set('type', Form::TYPE_EXAM)
            ->set('pass_percentage', 50)
            ->set('is_published', true)
            ->set('questionDrafts', [
                [
                    'key' => 'q1',
                    'id' => null,
                    'type' => 'yes_no',
                    'body' => 'Is PPE required?',
                    'points' => 2,
                    'correct_yes_no' => true,
                    'options' => [],
                ],
                [
                    'key' => 'q2',
                    'id' => null,
                    'type' => 'multiple_choice',
                    'body' => 'Pick the safe colour',
                    'points' => 2,
                    'correct_yes_no' => true,
                    'options' => [
                        ['key' => 'o1', 'id' => null, 'label' => 'Green', 'is_correct' => true],
                        ['key' => 'o2', 'id' => null, 'label' => 'Red', 'is_correct' => false],
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $form = Form::query()->where('title', 'Safety exam')->first();
        $this->assertNotNull($form);
        $this->assertTrue($form->is_published);

        $form->load('questions.options');
        $yesNo = $form->questions->firstWhere('type', 'yes_no');
        $mc = $form->questions->firstWhere('type', 'multiple_choice');
        $wrongOption = $mc->options->firstWhere('is_correct', false);

        Livewire::actingAs($member)
            ->test(Take::class, ['form' => $form])
            ->set("answers.{$yesNo->id}", '0')
            ->set("answers.{$mc->id}", (string) $wrongOption->id)
            ->call('submit')
            ->assertHasNoErrors();

        $submission = FormSubmission::query()->where('form_id', $form->id)->where('user_id', $member->id)->first();
        $this->assertNotNull($submission);
        $this->assertSame(0, $submission->score);
        $this->assertSame(4, $submission->max_score);
        $this->assertFalse($submission->passed);

        Livewire::actingAs($member)
            ->test(Take::class, ['form' => $form])
            ->call('submit')
            ->assertHasErrors();

        $correctOption = $mc->options->firstWhere('is_correct', true);

        $member2 = User::factory()->create(['role_id' => 2]);
        Livewire::actingAs($member2)
            ->test(Take::class, ['form' => $form])
            ->set("answers.{$yesNo->id}", '1')
            ->set("answers.{$mc->id}", (string) $correctOption->id)
            ->call('submit');

        $passed = FormSubmission::query()->where('form_id', $form->id)->where('user_id', $member2->id)->first();
        $this->assertTrue($passed->passed);
        $this->assertSame(4, $passed->score);
    }

    public function test_admin_can_manage_forms(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);

        $this->actingAs($admin)
            ->get(route('forms.manage.create'))
            ->assertOk();
    }
}
