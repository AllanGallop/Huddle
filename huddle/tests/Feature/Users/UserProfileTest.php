<?php

namespace Tests\Feature\Users;

use App\Models\Accreditation;
use App\Models\AccreditationAssignment;
use App\Models\User;
use App\Models\UserFlags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_user_profile(): void
    {
        $user = User::factory()->create();

        $this->get(route('users.show', $user))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_limited_profile(): void
    {
        $viewer = User::factory()->create(['role_id' => 2]);
        $member = User::factory()->create([
            'role_id' => 2,
            'name' => 'Alex Member',
            'email' => 'alex.secret@example.com',
        ]);

        $tag = UserFlags::create(['name' => 'Committee', 'description' => 'Committee member']);
        $member->flags()->attach($tag);

        $accreditation = Accreditation::create([
            'name' => 'First Aid',
            'description' => 'Basic training',
            'is_active' => true,
        ]);

        AccreditationAssignment::create([
            'user_id' => $member->id,
            'accreditation_id' => $accreditation->id,
            'is_active' => true,
        ]);

        $this->actingAs($viewer)
            ->get(route('users.show', $member))
            ->assertOk()
            ->assertSee('Alex Member')
            ->assertSee('Member')
            ->assertSee('Committee')
            ->assertSee('First Aid')
            ->assertSee('Active')
            ->assertDontSee('alex.secret@example.com');
    }
}
