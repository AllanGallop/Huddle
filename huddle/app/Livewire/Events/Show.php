<?php

namespace App\Livewire\Events;

use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventVolunteer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public Event $event;

    public string $comment = '';

    public ?int $replyingTo = null;

    public bool $showEditModal = false;

    public string $name = '';

    public string $description = '';

    public string $location = '';

    public string $start_time = '';

    public string $end_time = '';

    public string $event_type = 'public';

    public string $event_status = 'draft';

    public bool $volunteer_required = false;

    public ?int $adminVolunteerUserId = null;

    public ?int $editingVolunteerId = null;

    public ?int $editVolunteerUserId = null;

    public function mount(Event $event): void
    {
        $this->authorize('view', $event);

        $this->event = $event->load('creator');
    }

    public function title(): string
    {
        return $this->event->name;
    }

    #[Computed]
    public function canManageEvent(): bool
    {
        return Auth::user()->canManageEvent($this->event);
    }

    #[Computed]
    public function isAdmin(): bool
    {
        return Auth::user()->isAdmin();
    }

    #[Computed]
    public function users()
    {
        return User::query()->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function availableVolunteerUsers()
    {
        $assignedIds = $this->event->volunteers()->pluck('user_id');

        return User::query()
            ->whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function comments()
    {
        return $this->event->topLevelComments()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function volunteers()
    {
        return $this->event->volunteers()->with(['user.membershipRenewalAssignments.membershipRenewal'])->get();
    }

    #[Computed]
    public function isVolunteering(): bool
    {
        return $this->event->volunteers()
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function openEditModal(): void
    {
        $this->authorizeManageEvent();

        $this->name = $this->event->name;
        $this->description = $this->event->description;
        $this->location = $this->event->location;
        $this->start_time = $this->event->start_time->format('Y-m-d\TH:i');
        $this->end_time = $this->event->end_time->format('Y-m-d\TH:i');
        $this->event_type = $this->event->event_type;
        $this->event_status = $this->event->event_status;
        $this->volunteer_required = $this->event->volunteer_required;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetValidation();
    }

    public function updateEvent(): void
    {
        $this->authorizeManageEvent();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:500'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'event_type' => ['required', 'in:'.implode(',', Event::TYPES)],
            'event_status' => ['required', 'in:'.implode(',', Event::STATUSES)],
            'volunteer_required' => ['boolean'],
        ]);

        $this->event->update($validated);
        $this->event->load('creator');
        $this->closeEditModal();
    }

    public function deleteEvent(): void
    {
        $this->authorizeManageEvent();

        $this->event->delete();

        $this->redirect(route('events.index'), navigate: true);
    }

    public function addComment(): void
    {
        $this->validate([
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        if ($this->replyingTo) {
            EventComment::query()
                ->where('event_id', $this->event->id)
                ->whereNull('parent_comment_id')
                ->findOrFail($this->replyingTo);
        }

        EventComment::create([
            'event_id' => $this->event->id,
            'user_id' => Auth::id(),
            'parent_comment_id' => $this->replyingTo,
            'comment' => $this->comment,
        ]);

        $this->reset('comment', 'replyingTo');
        unset($this->comments);
    }

    public function startReply(int $commentId): void
    {
        $this->replyingTo = $commentId;
        $this->resetValidation();
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
        $this->resetValidation();
    }

    public function toggleVolunteer(): void
    {
        $existing = EventVolunteer::query()
            ->where('event_id', $this->event->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            EventVolunteer::create([
                'event_id' => $this->event->id,
                'user_id' => Auth::id(),
            ]);
        }

        unset($this->volunteers, $this->isVolunteering, $this->availableVolunteerUsers);
    }

    public function addVolunteer(): void
    {
        $this->authorizeAdmin();

        $this->validate([
            'adminVolunteerUserId' => ['required', 'exists:users,id'],
        ]);

        EventVolunteer::firstOrCreate([
            'event_id' => $this->event->id,
            'user_id' => $this->adminVolunteerUserId,
        ]);

        $this->reset('adminVolunteerUserId');
        unset($this->volunteers, $this->isVolunteering, $this->availableVolunteerUsers);
    }

    public function startEditVolunteer(int $volunteerId): void
    {
        $this->authorizeAdmin();

        $volunteer = EventVolunteer::query()
            ->where('event_id', $this->event->id)
            ->findOrFail($volunteerId);

        $this->editingVolunteerId = $volunteer->id;
        $this->editVolunteerUserId = $volunteer->user_id;
    }

    public function cancelEditVolunteer(): void
    {
        $this->editingVolunteerId = null;
        $this->editVolunteerUserId = null;
        $this->resetValidation();
    }

    public function updateVolunteer(): void
    {
        $this->authorizeAdmin();

        $this->validate([
            'editVolunteerUserId' => ['required', 'exists:users,id'],
        ]);

        $volunteer = EventVolunteer::query()
            ->where('event_id', $this->event->id)
            ->findOrFail($this->editingVolunteerId);

        $alreadyAssigned = EventVolunteer::query()
            ->where('event_id', $this->event->id)
            ->where('user_id', $this->editVolunteerUserId)
            ->where('id', '!=', $volunteer->id)
            ->exists();

        if ($alreadyAssigned) {
            $this->addError('editVolunteerUserId', __('This user is already a volunteer on this event.'));

            return;
        }

        $volunteer->update(['user_id' => $this->editVolunteerUserId]);

        $this->cancelEditVolunteer();
        unset($this->volunteers, $this->isVolunteering, $this->availableVolunteerUsers);
    }

    public function removeVolunteer(int $volunteerId): void
    {
        $this->authorizeAdmin();

        EventVolunteer::query()
            ->where('event_id', $this->event->id)
            ->findOrFail($volunteerId)
            ->delete();

        unset($this->volunteers, $this->isVolunteering, $this->availableVolunteerUsers);
    }

    protected function authorizeManageEvent(): void
    {
        $this->authorize('update', $this->event);
    }

    protected function authorizeAdmin(): void
    {
        $this->authorize('manageVolunteers', $this->event);
    }

    public function render()
    {
        return view('livewire.events.show');
    }
}
