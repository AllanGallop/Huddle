<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    public function create(User $user): bool
    {
        return $user->canManageForms();
    }

    public function take(User $user, Form $form): bool
    {
        return $form->canTake($user);
    }

    public function manage(User $user, Form $form): bool
    {
        return $form->canManage($user);
    }
}
