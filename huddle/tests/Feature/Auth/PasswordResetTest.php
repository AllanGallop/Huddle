<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.request'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.request'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
            $response = $this->get(route('password.reset', $notification->token));
            $response->assertOk();

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.request'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login', absolute: false));

            $this->assertTrue(Hash::check('password', $user->refresh()->password));

            return true;
        });
    }

    public function test_user_can_log_in_repeatedly_after_password_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.request'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])->assertSessionHasNoErrors();

            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'new-password-123',
            ])->assertRedirect(route('dashboard', absolute: false));

            $this->assertAuthenticatedAs($user);

            $this->post(route('logout'));
            $this->assertGuest();

            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'new-password-123',
            ])->assertRedirect(route('dashboard', absolute: false));

            $this->assertAuthenticatedAs($user);

            return true;
        });
    }

    public function test_authenticated_users_are_redirected_away_from_invite_reset_links(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('password.reset', ['token' => 'stale-token-from-old-email']))
            ->assertRedirect(route('dashboard', absolute: false));
    }
}
