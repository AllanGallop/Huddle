<?php

namespace Tests\Feature\Mentors;

use App\Models\Accreditation;
use App\Models\User;
use App\Models\UserFlags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MentorsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_without_mentor_tag_cannot_access_mentors(): void
    {
        $user = User::factory()->create(['role_id' => 2]);

        $this->actingAs($user)
            ->get(route('mentors.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_mentors(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);

        $this->actingAs($admin)
            ->get(route('mentors.index'))
            ->assertOk();
    }

    public function test_mentor_tag_user_can_access_mentors(): void
    {
        $mentorFlag = UserFlags::firstOrCreate(
            ['name' => 'Mentor'],
            ['description' => 'Accreditation Mentor'],
        );

        $user = User::factory()->create(['role_id' => 2]);
        $user->flags()->attach($mentorFlag);

        $this->actingAs($user)
            ->get(route('mentors.index'))
            ->assertOk();
    }

    public function test_mentor_can_create_accreditation_and_assign_to_user(): void
    {
        $mentorFlag = UserFlags::firstOrCreate(
            ['name' => 'Mentor'],
            ['description' => 'Accreditation Mentor'],
        );

        $mentor = User::factory()->create(['role_id' => 2]);
        $mentor->flags()->attach($mentorFlag);

        $member = User::factory()->create(['role_id' => 2]);

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
}
