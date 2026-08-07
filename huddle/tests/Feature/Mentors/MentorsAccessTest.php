<?php

namespace Tests\Feature\Mentors;

use App\Models\Accreditation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MentorsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_without_permission_cannot_access_mentors(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('mentors.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_mentors(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('mentors.index'))
            ->assertOk();
    }

    public function test_mentor_role_user_can_access_mentors(): void
    {
        $user = User::factory()->withRole('Mentor')->create();

        $this->actingAs($user)
            ->get(route('mentors.index'))
            ->assertOk();
    }

    public function test_mentor_can_create_accreditation_and_assign_to_user(): void
    {
        $mentor = User::factory()->withRole('Mentor')->create();
        $member = User::factory()->create();

        $this->actingAs($mentor);

        Livewire::test(\App\Livewire\Mentors\Index::class)
            ->call('openCreateAccreditationModal')
            ->set('accreditation_name', 'First Aid')
            ->set('accreditation_description', 'Basic first aid training')
            ->call('saveAccreditation')
            ->assertHasNoErrors();

        $accreditation = Accreditation::query()->where('name', 'First Aid')->first();
        $this->assertNotNull($accreditation);

        Livewire::test(\App\Livewire\Mentors\Index::class)
            ->call('setTab', 'assignments')
            ->call('openCreateAssignmentModal')
            ->set('assignment_user_id', $member->id)
            ->set('assignment_accreditation_id', $accreditation->id)
            ->call('saveAssignment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('accreditation_assignments', [
            'user_id' => $member->id,
            'accreditation_id' => $accreditation->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_assign_accreditation_mentors(): void
    {
        $admin = User::factory()->admin()->create();
        $mentorContact = User::factory()->create();
        $accreditation = Accreditation::query()->create([
            'name' => 'Food Hygiene',
            'description' => 'Kitchen safety',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Mentors\Index::class)
            ->call('setTab', 'mentors')
            ->call('openEditMentorsModal', $accreditation->id)
            ->set('assignedMentorIds', [$mentorContact->id])
            ->call('saveMentors')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('accreditation_mentors', [
            'accreditation_id' => $accreditation->id,
            'user_id' => $mentorContact->id,
        ]);
    }
}
