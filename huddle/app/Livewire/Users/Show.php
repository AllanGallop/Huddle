<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public User $profileUser;

    public function mount(User $user): void
    {
        $this->profileUser = $user->load([
            'role',
            'flags',
            'membershipRenewalAssignments' => fn ($query) => $query
                ->with('membershipRenewal')
                ->orderByDesc('membership_renewal_id'),
            'accreditationAssignments' => fn ($query) => $query
                ->with('accreditation')
                ->orderBy('accreditation_id'),
        ]);
    }

    public function title(): string
    {
        return $this->profileUser->name;
    }

    public function render()
    {
        return view('livewire.users.show');
    }
}
