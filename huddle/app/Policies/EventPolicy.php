<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function view(User $user, Event $event): bool
    {
        return $user->canViewEvent($event);
    }

    public function update(User $user, Event $event): bool
    {
        return $user->canManageEvent($event);
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->canManageEvent($event);
    }

    public function manageVolunteers(User $user, Event $event): bool
    {
        return $user->isAdmin();
    }
}
