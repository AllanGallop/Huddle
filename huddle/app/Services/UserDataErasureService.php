<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventVolunteer;
use App\Models\Form;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectVolunteer;
use App\Models\Role;
use App\Models\User;
use App\Models\WikiPage;
use App\Models\WikiPageVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserDataErasureService
{
    public function erase(User $user): void
    {
        if ($this->isPlaceholder($user)) {
            throw new \InvalidArgumentException('The GDPR placeholder account cannot be erased.');
        }

        DB::transaction(function () use ($user): void {
            $placeholder = $this->placeholderUser();

            $this->deleteSessions($user);
            $this->deletePasswordResetTokens($user);

            ProjectVolunteer::query()->where('user_id', $user->id)->delete();
            EventVolunteer::query()->where('user_id', $user->id)->delete();
            ProjectComment::query()->where('user_id', $user->id)->delete();
            EventComment::query()->where('user_id', $user->id)->delete();

            $user->flags()->detach();
            $user->accreditationAssignments()->delete();
            $user->membershipRenewalAssignments()->delete();

            DB::table('project_tasks')->where('user_id', $user->id)->delete();

            Project::query()->where('leader_id', $user->id)->update(['leader_id' => $placeholder->id]);
            Project::query()->where('created_by', $user->id)->update(['created_by' => $placeholder->id]);

            Event::query()->where('created_by', $user->id)->update(['created_by' => $placeholder->id]);

            Form::query()->where('created_by', $user->id)->update(['created_by' => $placeholder->id]);

            WikiPage::query()->where('created_by', $user->id)->update(['created_by' => $placeholder->id]);
            WikiPage::query()->where('updated_by', $user->id)->update(['updated_by' => $placeholder->id]);
            WikiPageVersion::query()->where('created_by', $user->id)->update(['created_by' => $placeholder->id]);

            $user->delete();
        });
    }

    public function isPlaceholder(User $user): bool
    {
        return strcasecmp($user->email, (string) config('gdpr.placeholder_email')) === 0;
    }

    protected function placeholderUser(): User
    {
        $memberRoleId = Role::query()->where('name', 'member')->value('id') ?? 2;

        $user = User::query()->firstOrNew([
            'email' => config('gdpr.placeholder_email'),
        ]);

        if (! $user->exists) {
            $user->name = config('gdpr.placeholder_name');
            $user->password = Hash::make(Str::password(64));
            $user->role_id = $memberRoleId;
            $user->email_verified_at = now();
            $user->digest_opt_out = true;
            $user->save();
        }

        return $user;
    }

    protected function deleteSessions(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }

    protected function deletePasswordResetTokens(User $user): void
    {
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
    }
}
