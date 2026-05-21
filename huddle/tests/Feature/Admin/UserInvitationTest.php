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

        $admin = User::factory()->create(['role_id' => 1]);
        $role = Role::query()->where('name', 'member')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Admin\Index::class)
            ->call('openCreateUserModal', 'invite')
            ->set('name', 'Invited User')
            ->set('email', 'invited@example.com')
            ->set('role_id', $role->id)
            ->call('saveUser');

        $invited = User::query()->where('email', 'invited@example.com')->first();

        $this->assertNotNull($invited);
        Notification::assertSentTo($invited, UserInvitationNotification::class);
    }
}
