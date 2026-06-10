<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->canManageProject($project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->canManageProject($project);
    }

    public function manageFinancials(User $user, Project $project): bool
    {
        return $user->canManageProjectFinancials($project);
    }

    public function uploadImage(User $user, Project $project): bool
    {
        return $user->canManageProject($project);
    }

    public function manageVolunteers(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function assignLeader(User $user): bool
    {
        return $user->isAdmin();
    }
}
