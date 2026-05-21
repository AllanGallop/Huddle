<?php

namespace App\Livewire\Members;

use App\Models\User;
use App\Models\UserFlags;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Members')]
class Index extends Component
{
    public string $search = '';

    public string $membershipFilter = '';

    public string $tagFilter = '';

    public function clearFilters(): void
    {
        $this->reset(['search', 'membershipFilter', 'tagFilter']);
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->search !== ''
            || $this->membershipFilter !== ''
            || $this->tagFilter !== '';
    }

    #[Computed]
    public function tags()
    {
        return UserFlags::query()
            ->whereHas('users')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function members()
    {
        $query = User::query()
            ->with([
                'role',
                'flags',
                'membershipRenewalAssignments.membershipRenewal',
            ]);

        $this->applyFilters($query);

        return $query->orderBy('name')->get();
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhereHas('flags', fn (Builder $flags) => $flags->where('name', 'like', $term));
            });
        }

        if ($this->tagFilter !== '') {
            $query->whereHas('flags', fn (Builder $flags) => $flags->whereKey((int) $this->tagFilter));
        }

        match ($this->membershipFilter) {
            'active' => $query->membershipActive(),
            'expired' => $query->membershipExpired(),
            'none' => $query->withoutMembership(),
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.members.index');
    }
}
