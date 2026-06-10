<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function viewFinancials(User $user): bool
    {
        return $user->canViewFinancialReports();
    }
}
