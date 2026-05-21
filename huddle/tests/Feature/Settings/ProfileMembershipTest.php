<?php

namespace Tests\Feature\Settings;

use App\Models\Accreditation;
use App\Models\AccreditationAssignment;
use App\Models\MembershipRenewal;
use App\Models\MembershipRenewalAssignment;
use App\Models\User;
use App\Models\UserFlags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_settings_shows_user_tags_and_accreditations(): void
    {
        $user = User::factory()->create(['role_id' => 2]);

        $tag = UserFlags::create(['name' => 'Mentor', 'description' => 'Accreditation Mentor']);
        $user->flags()->attach($tag);

        $accreditation = Accreditation::create([
            'name' => 'First Aid',
            'description' => 'Basic training',
            'is_active' => true,
        ]);

        AccreditationAssignment::create([
            'user_id' => $user->id,
            'accreditation_id' => $accreditation->id,
            'is_active' => true,
        ]);

        $renewal = MembershipRenewal::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        MembershipRenewalAssignment::create([
            'user_id' => $user->id,
            'membership_renewal_id' => $renewal->id,
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Settings\Profile::class)
            ->assertSee('Your membership')
            ->assertSee('2026')
            ->assertSee('Mentor')
            ->assertSee('First Aid')
            ->assertSee('Active');
    }
}
