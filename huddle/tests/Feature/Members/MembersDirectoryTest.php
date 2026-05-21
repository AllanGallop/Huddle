<?php

namespace Tests\Feature\Members;

use App\Livewire\Members\Index;
use App\Models\MembershipRenewal;
use App\Models\MembershipRenewalAssignment;
use App\Models\User;
use App\Models\UserFlags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MembersDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_members_directory(): void
    {
        $this->get(route('members.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_browse_and_filter_members(): void
    {
        $viewer = User::factory()->create(['role_id' => 2]);

        $activeMember = User::factory()->create([
            'role_id' => 2,
            'name' => 'Active Alex',
        ]);

        $expiredMember = User::factory()->create([
            'role_id' => 1,
            'name' => 'Expired Erin',
        ]);

        $tag = UserFlags::create(['name' => 'Mentor', 'description' => 'Mentor tag']);
        $activeMember->flags()->attach($tag);

        $currentRenewal = MembershipRenewal::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $pastRenewal = MembershipRenewal::create([
            'name' => '2020',
            'start_date' => '2020-01-01',
            'end_date' => '2020-12-31',
        ]);

        MembershipRenewalAssignment::create([
            'user_id' => $activeMember->id,
            'membership_renewal_id' => $currentRenewal->id,
        ]);

        MembershipRenewalAssignment::create([
            'user_id' => $expiredMember->id,
            'membership_renewal_id' => $pastRenewal->id,
        ]);

        $this->actingAs($viewer)
            ->get(route('members.index'))
            ->assertOk()
            ->assertSee('Members')
            ->assertSee('Active Alex')
            ->assertSee('Expired Erin')
            ->assertSee('Member')
            ->assertSee('Admin')
            ->assertSee('Mentor');

        Livewire::actingAs($viewer)
            ->test(Index::class)
            ->set('membershipFilter', 'active')
            ->assertSee('Active Alex')
            ->assertDontSee('Expired Erin');

        Livewire::actingAs($viewer)
            ->test(Index::class)
            ->set('search', 'Mentor')
            ->assertSee('Active Alex')
            ->assertDontSee('Expired Erin');

        Livewire::actingAs($viewer)
            ->test(Index::class)
            ->set('tagFilter', (string) $tag->id)
            ->assertSee('Active Alex')
            ->assertDontSee('Expired Erin');
    }
}
