<?php

namespace App\Livewire\Accreditations;

use App\Models\Accreditation;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Accreditations')]
class Index extends Component
{
    /** @var list<int> */
    public array $expandedIds = [];

    #[Url]
    public string $tab = 'browse';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(): void
    {
        if (! in_array($this->tab, ['browse', 'mentors'], true)) {
            $this->tab = 'browse';
        }
    }

    public function updatedTab(string $value): void
    {
        if (! in_array($value, ['browse', 'mentors'], true)) {
            $this->tab = 'browse';
        }
    }

    public function toggle(int $accreditationId): void
    {
        if (in_array($accreditationId, $this->expandedIds, true)) {
            $this->expandedIds = array_values(array_filter(
                $this->expandedIds,
                fn (int $id) => $id !== $accreditationId,
            ));

            return;
        }

        $this->expandedIds[] = $accreditationId;
    }

    public function expandAll(): void
    {
        $this->expandedIds = $this->accreditations->pluck('id')->all();
    }

    public function collapseAll(): void
    {
        $this->expandedIds = [];
    }

    public function isExpanded(int $accreditationId): bool
    {
        return in_array($accreditationId, $this->expandedIds, true);
    }

    #[Computed]
    public function accreditations(): Collection
    {
        $search = trim($this->search);

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
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhereHas('mentors', fn ($mentorQuery) => $mentorQuery->where('name', 'like', $like))
                        ->orWhereHas(
                            'assignments',
                            fn ($assignmentQuery) => $assignmentQuery
                                ->where('is_active', true)
                                ->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $like)),
                        );
                });
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function mentors(): Collection
    {
        $search = trim($this->search);

        return User::query()
            ->whereHas(
                'mentoredAccreditations',
                fn ($query) => $query->where('is_active', true),
            )
            ->with([
                'mentoredAccreditations' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('name'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhereHas(
                            'mentoredAccreditations',
                            fn ($accreditationQuery) => $accreditationQuery
                                ->where('is_active', true)
                                ->where('name', 'like', $like),
                        );
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.accreditations.index');
    }
}
