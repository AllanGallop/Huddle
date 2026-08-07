<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'privacy_policy_accepted_at' => now(),
            'privacy_policy_version' => config('gdpr.policy_version'),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (\App\Models\User $user): void {
            if ($user->roles()->exists()) {
                return;
            }

            $memberRoleId = Role::query()->where('name', 'member')->value('id');
            if ($memberRoleId) {
                $user->roles()->attach($memberRoleId);
            }
        });
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->afterCreating(function (\App\Models\User $user): void {
            $adminRoleId = Role::query()->where('name', 'admin')->value('id');
            if ($adminRoleId) {
                $user->roles()->sync([$adminRoleId]);
            }
        });
    }

    public function withRole(string $name): static
    {
        return $this->afterCreating(function (\App\Models\User $user) use ($name): void {
            $roleId = Role::query()->where('name', $name)->value('id');
            if ($roleId) {
                $user->roles()->syncWithoutDetaching([$roleId]);
            }
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
