<?php

namespace App\Services;

use App\Models\MembershipRenewalAssignment;
use App\Models\User;
use App\Notifications\UserInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserInvitationService
{
    /**
     * @param  array<int>  $roleIds
     * @param  array<int>  $flagIds
     */
    public function invite(
        string $name,
        string $email,
        array $roleIds,
        array $flagIds = [],
        ?int $membershipRenewalId = null,
    ): User {
        return DB::transaction(function () use ($name, $email, $roleIds, $flagIds, $membershipRenewalId) {
            $user = new User([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::password(32)),
            ]);
            $user->save();
            $user->roles()->sync($roleIds);

            if ($flagIds !== []) {
                $user->flags()->sync($flagIds);
            }

            if ($membershipRenewalId !== null) {
                MembershipRenewalAssignment::create([
                    'user_id' => $user->id,
                    'membership_renewal_id' => $membershipRenewalId,
                ]);
            }

            $token = Password::broker()->createToken($user);
            $user->notify(new UserInvitationNotification($token));

            return $user;
        });
    }
}
