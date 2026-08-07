<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use App\Notifications\UserInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class UserInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_invite_sends_custom_invitation_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $role = Role::query()->where('name', 'member')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Index::class)
            ->call('openCreateUserModal', 'invite')
            ->set('name', 'Invited User')
            ->set('email', 'invited@example.com')
            ->set('assignedRoleIds', [$role->id])
            ->call('saveUser');

        $invited = User::query()->where('email', 'invited@example.com')->first();

        $this->assertNotNull($invited);
        Notification::assertSentTo($invited, UserInvitationNotification::class);
    }

    public function test_admin_can_resend_invitation(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $member = User::factory()->create([
            'email' => 'pending@example.com',
            'email_verified_at' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\Index::class)
            ->call('resendInvitation', $member->id)
            ->assertHasNoErrors();

        Notification::assertSentTo($member, UserInvitationNotification::class);
    }

    public function test_admin_cannot_resend_invitation_to_self(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Admin\Index::class)
            ->call('resendInvitation', $admin->id)
            ->assertHasErrors('user');

        Notification::assertNothingSent();
    }
}
