<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Index;
use App\Models\MembershipRenewal;
use App\Models\MembershipRenewalAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MembershipRenewalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_membership_period_and_assign_to_user(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $member = User::factory()->create(['role_id' => 2]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('setTab', 'membership')
            ->call('openCreateRenewalModal')
            ->set('renewal_name', '2026')
            ->set('renewal_start_date', '2026-01-01')
            ->set('renewal_end_date', '2026-12-31')
            ->call('saveRenewal')
            ->assertHasNoErrors();

        $renewal = MembershipRenewal::query()->where('name', '2026')->first();
        $this->assertNotNull($renewal);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('setTab', 'membership')
            ->call('setMembershipTab', 'assignments')
            ->call('openCreateMembershipAssignmentModal')
            ->set('membership_assignment_user_id', $member->id)
            ->set('membership_assignment_renewal_id', $renewal->id)
            ->call('saveMembershipAssignment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('membership_renewal_assignments', [
            'user_id' => $member->id,
            'membership_renewal_id' => $renewal->id,
        ]);
    }

    public function test_membership_status_reflects_latest_period(): void
    {
        $member = User::factory()->create(['role_id' => 2]);

        $past = MembershipRenewal::create([
            'name' => '2024',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $current = MembershipRenewal::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        MembershipRenewalAssignment::create([
            'user_id' => $member->id,
            'membership_renewal_id' => $past->id,
        ]);

        MembershipRenewalAssignment::create([
            'user_id' => $member->id,
            'membership_renewal_id' => $current->id,
        ]);

        $member->load('membershipRenewalAssignments.membershipRenewal');

        $this->assertSame('active', $member->membershipStatus());

        $expired = MembershipRenewal::create([
            'name' => '2020',
            'start_date' => '2020-01-01',
            'end_date' => '2020-12-31',
        ]);

        MembershipRenewalAssignment::create([
            'user_id' => $member->id,
            'membership_renewal_id' => $expired->id,
        ]);

        $member->unsetRelation('membershipRenewalAssignments');
        $member->load('membershipRenewalAssignments.membershipRenewal');

        $this->assertSame('active', $member->membershipStatus());

        MembershipRenewalAssignment::query()
            ->where('user_id', $member->id)
            ->where('membership_renewal_id', $current->id)
            ->delete();

        $member->unsetRelation('membershipRenewalAssignments');
        $member->load('membershipRenewalAssignments.membershipRenewal');

        $this->assertSame('expired', $member->membershipStatus());
    }
}
