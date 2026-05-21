<?php

namespace App\Livewire\Mentors;

use App\Models\Accreditation;
use App\Models\AccreditationAssignment;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Mentors')]
class Index extends Component
{
    public string $activeTab = 'accreditations';

    public bool $showAccreditationModal = false;

    public ?int $editingAccreditationId = null;

    public string $accreditation_name = '';

    public string $accreditation_description = '';

    public bool $accreditation_is_active = true;

    public bool $showAssignmentModal = false;

    public ?int $editingAssignmentId = null;

    public ?int $assignment_user_id = null;

    public ?int $assignment_accreditation_id = null;

    public bool $assignment_is_active = true;

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['accreditations', 'assignments'], true)) {
            $this->activeTab = $tab;
        }
    }

    #[Computed]
    public function accreditations()
    {
        return Accreditation::query()
            ->withCount('assignments')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function assignments()
    {
        return AccreditationAssignment::query()
            ->with(['user.membershipRenewalAssignments.membershipRenewal', 'accreditation'])
            ->orderByDesc('updated_at')
            ->get();
    }

    #[Computed]
    public function users()
    {
        return User::query()->orderBy('name')->get();
    }

    public function openCreateAccreditationModal(): void
    {
        $this->resetAccreditationForm();
        $this->showAccreditationModal = true;
    }

    public function openEditAccreditationModal(int $accreditationId): void
    {
        $accreditation = Accreditation::query()->findOrFail($accreditationId);

        $this->editingAccreditationId = $accreditation->id;
        $this->accreditation_name = $accreditation->name;
        $this->accreditation_description = $accreditation->description ?? '';
        $this->accreditation_is_active = $accreditation->is_active;
        $this->showAccreditationModal = true;
    }

    public function closeAccreditationModal(): void
    {
        $this->showAccreditationModal = false;
        $this->resetAccreditationForm();
        $this->resetValidation();
    }

    public function saveAccreditation(): void
    {
        $validated = $this->validate([
            'accreditation_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('accreditations', 'name')->ignore($this->editingAccreditationId),
            ],
            'accreditation_description' => ['nullable', 'string', 'max:1000'],
            'accreditation_is_active' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['accreditation_name'],
            'description' => $validated['accreditation_description'] ?? '',
            'is_active' => $validated['accreditation_is_active'],
        ];

        if ($this->editingAccreditationId) {
            Accreditation::query()->findOrFail($this->editingAccreditationId)->update($data);
            session()->flash('status', __('Accreditation updated successfully.'));
        } else {
            Accreditation::create($data);
            session()->flash('status', __('Accreditation created successfully.'));
        }

        $this->closeAccreditationModal();
        unset($this->accreditations, $this->assignments);
    }

    public function deleteAccreditation(int $accreditationId): void
    {
        $accreditation = Accreditation::query()->findOrFail($accreditationId);
        $accreditation->assignments()->delete();
        $accreditation->delete();

        unset($this->accreditations, $this->assignments);
        session()->flash('status', __('Accreditation deleted successfully.'));
    }

    public function openCreateAssignmentModal(): void
    {
        $this->resetAssignmentForm();
        $this->showAssignmentModal = true;
    }

    public function openEditAssignmentModal(int $assignmentId): void
    {
        $assignment = AccreditationAssignment::query()->findOrFail($assignmentId);

        $this->editingAssignmentId = $assignment->id;
        $this->assignment_user_id = $assignment->user_id;
        $this->assignment_accreditation_id = $assignment->accreditation_id;
        $this->assignment_is_active = $assignment->is_active;
        $this->showAssignmentModal = true;
    }

    public function closeAssignmentModal(): void
    {
        $this->showAssignmentModal = false;
        $this->resetAssignmentForm();
        $this->resetValidation();
    }

    public function saveAssignment(): void
    {
        $validated = $this->validate([
            'assignment_user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('accreditation_assignments')
                    ->where(fn ($query) => $query->where('accreditation_id', $this->assignment_accreditation_id))
                    ->ignore($this->editingAssignmentId),
            ],
            'assignment_accreditation_id' => ['required', 'exists:accreditations,id'],
            'assignment_is_active' => ['boolean'],
        ]);

        $data = [
            'user_id' => $validated['assignment_user_id'],
            'accreditation_id' => $validated['assignment_accreditation_id'],
            'is_active' => $validated['assignment_is_active'],
        ];

        if ($this->editingAssignmentId) {
            AccreditationAssignment::query()
                ->findOrFail($this->editingAssignmentId)
                ->update($data);
            session()->flash('status', __('Assignment updated successfully.'));
        } else {
            AccreditationAssignment::create($data);
            session()->flash('status', __('Accreditation assigned successfully.'));
        }

        $this->closeAssignmentModal();
        unset($this->assignments, $this->accreditations);
    }

    public function deleteAssignment(int $assignmentId): void
    {
        AccreditationAssignment::query()->findOrFail($assignmentId)->delete();

        unset($this->assignments, $this->accreditations);
        session()->flash('status', __('Assignment removed successfully.'));
    }

    protected function resetAccreditationForm(): void
    {
        $this->reset([
            'editingAccreditationId',
            'accreditation_name',
            'accreditation_description',
            'accreditation_is_active',
        ]);
        $this->accreditation_is_active = true;
    }

    protected function resetAssignmentForm(): void
    {
        $this->reset([
            'editingAssignmentId',
            'assignment_user_id',
            'assignment_accreditation_id',
            'assignment_is_active',
        ]);
        $this->assignment_is_active = true;
    }

    public function render()
    {
        return view('livewire.mentors.index');
    }
}
