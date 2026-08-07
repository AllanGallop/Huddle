<?php

namespace App\Livewire\Accreditations;

use App\Models\Accreditation;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Accreditations')]
class Index extends Component
{
    public ?int $expandedId = null;

    public function toggle(int $accreditationId): void
    {
        $this->expandedId = $this->expandedId === $accreditationId ? null : $accreditationId;
    }

    #[Computed]
    public function accreditations()
    {
        return Accreditation::query()
            ->where('is_active', true)
            ->with([
                'mentors' => fn ($query) => $query->orderBy('name'),
                'assignments' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with(['user' => fn ($userQuery) => $userQuery->orderBy('name')]),
            ])
            ->withCount([
                'assignments as active_holders_count' => fn ($query) => $query->where('is_active', true),
                'mentors',
            ])
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.accreditations.index');
    }
}
