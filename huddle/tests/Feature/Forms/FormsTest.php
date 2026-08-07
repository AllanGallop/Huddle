<?php

namespace Tests\Feature\Forms;

use App\Livewire\Forms\Manage\Edit;
use App\Livewire\Forms\Take;
use App\Models\Accreditation;
use App\Models\AccreditationAssignment;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_access_form_management(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('forms.manage.index'))
            ->assertForbidden();
    }

    public function test_mentor_can_create_exam_and_member_receives_pass_fail(): void
    {
        $mentor = User::factory()->withRole('Mentor')->create();
        $member = User::factory()->create(['name' => 'Test Member']);

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

        $member2 = User::factory()->create();
        Livewire::actingAs($member2)
            ->test(Take::class, ['form' => $form])
            ->set("answers.{$yesNo->id}", '1')
            ->set("answers.{$mc->id}", (string) $correctOption->id)
            ->call('submit');

        $passed = FormSubmission::query()->where('form_id', $form->id)->where('user_id', $member2->id)->first();
        $this->assertTrue($passed->passed);
        $this->assertSame(4, $passed->score);
    }

    public function test_passing_exam_auto_assigns_linked_accreditation(): void
    {
        $mentor = User::factory()->withRole('Mentor')->create();
        $member = User::factory()->create();
        $accreditation = Accreditation::query()->create([
            'name' => 'Safety Cert',
            'description' => 'Passed safety exam',
            'is_active' => true,
        ]);

        Livewire::actingAs($mentor)
            ->test(Edit::class)
            ->set('title', 'Linked exam')
            ->set('type', Form::TYPE_EXAM)
            ->set('pass_percentage', 50)
            ->set('is_published', true)
            ->set('accreditation_id', $accreditation->id)
            ->set('questionDrafts', [
                [
                    'key' => 'q1',
                    'id' => null,
                    'type' => 'yes_no',
                    'body' => 'Safe?',
                    'points' => 1,
                    'correct_yes_no' => true,
                    'options' => [],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $form = Form::query()->where('title', 'Linked exam')->firstOrFail();
        $question = $form->questions()->first();

        Livewire::actingAs($member)
            ->test(Take::class, ['form' => $form])
            ->set("answers.{$question->id}", '1')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('accreditation_assignments', [
            'user_id' => $member->id,
            'accreditation_id' => $accreditation->id,
            'is_active' => true,
        ]);
    }

    public function test_failing_exam_does_not_assign_accreditation(): void
    {
        $mentor = User::factory()->withRole('Mentor')->create();
        $member = User::factory()->create();
        $accreditation = Accreditation::query()->create([
            'name' => 'Safety Cert Fail',
            'description' => 'Should not assign',
            'is_active' => true,
        ]);

        Livewire::actingAs($mentor)
            ->test(Edit::class)
            ->set('title', 'Fail linked exam')
            ->set('type', Form::TYPE_EXAM)
            ->set('pass_percentage', 100)
            ->set('is_published', true)
            ->set('accreditation_id', $accreditation->id)
            ->set('questionDrafts', [
                [
                    'key' => 'q1',
                    'id' => null,
                    'type' => 'yes_no',
                    'body' => 'Safe?',
                    'points' => 1,
                    'correct_yes_no' => true,
                    'options' => [],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $form = Form::query()->where('title', 'Fail linked exam')->firstOrFail();
        $question = $form->questions()->first();

        Livewire::actingAs($member)
            ->test(Take::class, ['form' => $form])
            ->set("answers.{$question->id}", '0')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertFalse(
            AccreditationAssignment::query()
                ->where('user_id', $member->id)
                ->where('accreditation_id', $accreditation->id)
                ->exists()
        );
    }

    public function test_admin_can_manage_forms(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('forms.manage.create'))
            ->assertOk();
    }
}
