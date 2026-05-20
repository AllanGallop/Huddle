<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectImage;
use App\Models\ProjectVolunteer;
use App\Models\User;
use App\Mail\ProjectInvoiceMail;
use App\Mail\ProjectQuoteMail;
use App\Services\ProjectDocumentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Project $project;

    public string $comment = '';

    public ?int $replyingTo = null;

    public $photo;

    public bool $showEditModal = false;

    public string $name = '';

    public string $description = '';

    public string $project_status = 'draft';

    public bool $volunteer_required = false;

    public ?int $leader_id = null;

    public ?int $adminVolunteerUserId = null;

    public ?int $editingVolunteerId = null;

    public ?int $editVolunteerUserId = null;

    public int $activeImageIndex = 0;

    public ?string $due_date = null;

    public string $financial_status = '';

    public string $quote_amount = '';

    public string $invoice_amount = '';

    public string $deposit_amount = '';

    public string $payment_amount = '';

    public string $quote_notes = '';

    public string $invoice_notes = '';

    public string $activeTab = 'overview';

    public string $documentEmail = '';

    public function mount(Project $project): void
    {
        $this->project = $project->load(['leader', 'creator']);

        if (Auth::user()->canManageProjectFinancials($this->project)) {
            $this->fillFinancialFields();
            $this->documentEmail = $this->project->leader->email ?? Auth::user()->email;
        }
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['overview', 'finance'], true)) {
            return;
        }

        if ($tab === 'finance' && ! $this->canManageFinancials) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function title(): string
    {
        return $this->project->name;
    }

    #[Computed]
    public function canManageProject(): bool
    {
        return Auth::user()->canManageProject($this->project);
    }

    #[Computed]
    public function isAdmin(): bool
    {
        return Auth::user()->isAdmin();
    }

    #[Computed]
    public function canManageFinancials(): bool
    {
        return Auth::user()->canManageProjectFinancials($this->project);
    }

    #[Computed]
    public function users()
    {
        return User::query()->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function availableVolunteerUsers()
    {
        $assignedIds = $this->project->volunteers()->pluck('user_id');

        return User::query()
            ->whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function comments()
    {
        return $this->project->topLevelComments()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function images()
    {
        return $this->project->images()->latest()->get();
    }

    #[Computed]
    public function volunteers()
    {
        return $this->project->volunteers()->with('user')->get();
    }

    #[Computed]
    public function isVolunteering(): bool
    {
        return $this->project->volunteers()
            ->where('user_id', Auth::id())
            ->exists();
    }

    #[Computed]
    public function activeImage(): ?ProjectImage
    {
        $images = $this->images;

        if ($images->isEmpty()) {
            return null;
        }

        $index = min($this->activeImageIndex, $images->count() - 1);

        return $images[$index];
    }

    public function setActiveImage(int $index): void
    {
        if ($index >= 0 && $index < $this->images->count()) {
            $this->activeImageIndex = $index;
        }
    }

    public function nextImage(): void
    {
        $count = $this->images->count();

        if ($count > 0) {
            $this->activeImageIndex = ($this->activeImageIndex + 1) % $count;
        }
    }

    public function previousImage(): void
    {
        $count = $this->images->count();

        if ($count > 0) {
            $this->activeImageIndex = ($this->activeImageIndex - 1 + $count) % $count;
        }
    }

    protected function clampActiveImageIndex(): void
    {
        $count = $this->images->count();

        if ($count === 0) {
            $this->activeImageIndex = 0;

            return;
        }

        if ($this->activeImageIndex >= $count) {
            $this->activeImageIndex = $count - 1;
        }
    }

    public function openEditModal(): void
    {
        $this->authorizeManageProject();

        $this->name = $this->project->name;
        $this->description = $this->project->description;
        $this->project_status = $this->project->project_status;
        $this->volunteer_required = $this->project->volunteer_required;
        $this->leader_id = $this->project->leader_id;
        $this->due_date = $this->project->due_date?->format('Y-m-d');
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetValidation();
    }

    public function updateProject(): void
    {
        $this->authorizeManageProject();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'project_status' => ['required', 'in:'.implode(',', Project::STATUSES)],
            'volunteer_required' => ['boolean'],
            'leader_id' => ['required', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        $this->project->update($validated);
        $this->project->load(['leader', 'creator']);

        if ($this->canManageFinancials) {
            $this->due_date = $this->project->due_date?->format('Y-m-d');
        }

        $this->closeEditModal();
    }

    public function saveFinancials(): void
    {
        $this->authorizeFinancials();

        $validated = $this->validate([
            'due_date' => ['nullable', 'date'],
            'financial_status' => ['nullable', 'string', 'in:,'.implode(',', Project::FINANCIAL_STATUSES)],
            'quote_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'invoice_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'payment_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'quote_notes' => ['nullable', 'string', 'max:5000'],
            'invoice_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'due_date' => $validated['due_date'] ?: null,
            'financial_status' => $validated['financial_status'] ?: null,
            'quote_amount' => $validated['quote_amount'] !== '' ? $validated['quote_amount'] : null,
            'invoice_amount' => $validated['invoice_amount'] !== '' ? $validated['invoice_amount'] : null,
            'deposit_amount' => $validated['deposit_amount'] !== '' ? $validated['deposit_amount'] : null,
            'payment_amount' => $validated['payment_amount'] !== '' ? $validated['payment_amount'] : null,
            'quote_notes' => $validated['quote_notes'] ?: null,
            'invoice_notes' => $validated['invoice_notes'] ?: null,
        ];

        $this->syncFinancialTimestamps($data);

        $this->project->update($data);
        $this->project->refresh();
        $this->fillFinancialFields();

        session()->flash('status', __('Financial details saved.'));
    }

    public function emailQuote(): void
    {
        $this->sendDocumentEmail('quote');
    }

    public function emailInvoice(): void
    {
        $this->sendDocumentEmail('invoice');
    }

    protected function sendDocumentEmail(string $type): void
    {
        $this->authorizeFinancials();

        $this->validate([
            'documentEmail' => ['required', 'email'],
        ]);

        if ($type === 'quote') {
            if (! $this->project->quote_amount) {
                $this->addError('documentEmail', __('Set a quote amount before sending.'));

                return;
            }

            $documents = app(ProjectDocumentService::class);
            $pdf = $documents->quotePdf($this->project);
            Mail::to($this->documentEmail)->send(new ProjectQuoteMail(
                $this->project,
                $pdf->output(),
                $documents->quoteFilename($this->project),
            ));
            session()->flash('status', __('Quote PDF sent to :email.', ['email' => $this->documentEmail]));
        } else {
            if (! $this->project->invoice_amount) {
                $this->addError('documentEmail', __('Set an invoice amount before sending.'));

                return;
            }

            $documents = app(ProjectDocumentService::class);
            $pdf = $documents->invoicePdf($this->project);
            Mail::to($this->documentEmail)->send(new ProjectInvoiceMail(
                $this->project,
                $pdf->output(),
                $documents->invoiceFilename($this->project),
            ));
            session()->flash('status', __('Invoice PDF sent to :email.', ['email' => $this->documentEmail]));
        }
    }

    protected function fillFinancialFields(): void
    {
        $this->due_date = $this->project->due_date?->format('Y-m-d');
        $this->financial_status = $this->project->financial_status ?? '';
        $this->quote_amount = $this->project->quote_amount !== null ? (string) $this->project->quote_amount : '';
        $this->invoice_amount = $this->project->invoice_amount !== null ? (string) $this->project->invoice_amount : '';
        $this->deposit_amount = $this->project->deposit_amount !== null ? (string) $this->project->deposit_amount : '';
        $this->payment_amount = $this->project->payment_amount !== null ? (string) $this->project->payment_amount : '';
        $this->quote_notes = $this->project->quote_notes ?? '';
        $this->invoice_notes = $this->project->invoice_notes ?? '';
    }

    protected function syncFinancialTimestamps(array &$data): void
    {
        $status = $data['financial_status'] ?? null;

        if ($status === 'quoted' && ! $this->project->quoted_at) {
            $data['quoted_at'] = now();
        }

        if ($status === 'invoiced' && ! $this->project->invoiced_at) {
            $data['invoiced_at'] = now();
        }

        if ($status === 'deposit_paid' && ! $this->project->deposit_paid_at) {
            $data['deposit_paid_at'] = now();
        }

        if ($status === 'paid' && ! $this->project->paid_at) {
            $data['paid_at'] = now();
        }
    }

    public function deleteProject(): void
    {
        $this->authorizeManageProject();

        foreach ($this->project->images as $image) {
            Storage::disk('public')->delete($image->image_url);
        }

        $this->project->delete();

        $this->redirect(route('projects.index'), navigate: true);
    }

    public function addComment(): void
    {
        $this->validate([
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        if ($this->replyingTo) {
            ProjectComment::query()
                ->where('project_id', $this->project->id)
                ->whereNull('parent_comment_id')
                ->findOrFail($this->replyingTo);
        }

        ProjectComment::create([
            'project_id' => $this->project->id,
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

    public function uploadImage(): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ]);

        $path = $this->photo->store('projects/'.$this->project->id, 'public');

        ProjectImage::create([
            'project_id' => $this->project->id,
            'image_url' => $path,
        ]);

        $this->reset('photo');
        unset($this->images, $this->activeImage);
        $this->activeImageIndex = $this->images->count() - 1;
    }

    public function deleteImage(int $imageId): void
    {
        if (! $this->canManageProject) {
            abort(403);
        }

        $image = ProjectImage::query()
            ->where('project_id', $this->project->id)
            ->findOrFail($imageId);

        Storage::disk('public')->delete($image->image_url);
        $image->delete();

        unset($this->images, $this->activeImage);
        $this->clampActiveImageIndex();
    }

    public function toggleVolunteer(): void
    {
        $existing = ProjectVolunteer::query()
            ->where('project_id', $this->project->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ProjectVolunteer::create([
                'project_id' => $this->project->id,
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

        ProjectVolunteer::firstOrCreate([
            'project_id' => $this->project->id,
            'user_id' => $this->adminVolunteerUserId,
        ]);

        $this->reset('adminVolunteerUserId');
        unset($this->volunteers, $this->isVolunteering, $this->availableVolunteerUsers);
    }

    public function startEditVolunteer(int $volunteerId): void
    {
        $this->authorizeAdmin();

        $volunteer = ProjectVolunteer::query()
            ->where('project_id', $this->project->id)
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

        $volunteer = ProjectVolunteer::query()
            ->where('project_id', $this->project->id)
            ->findOrFail($this->editingVolunteerId);

        $alreadyAssigned = ProjectVolunteer::query()
            ->where('project_id', $this->project->id)
            ->where('user_id', $this->editVolunteerUserId)
            ->where('id', '!=', $volunteer->id)
            ->exists();

        if ($alreadyAssigned) {
            $this->addError('editVolunteerUserId', __('This user is already a volunteer on this project.'));

            return;
        }

        $volunteer->update(['user_id' => $this->editVolunteerUserId]);

        $this->cancelEditVolunteer();
        unset($this->volunteers, $this->isVolunteering, $this->availableVolunteerUsers);
    }

    public function removeVolunteer(int $volunteerId): void
    {
        $this->authorizeAdmin();

        ProjectVolunteer::query()
            ->where('project_id', $this->project->id)
            ->findOrFail($volunteerId)
            ->delete();

        unset($this->volunteers, $this->isVolunteering, $this->availableVolunteerUsers);
    }

    protected function authorizeManageProject(): void
    {
        if (! Auth::user()->canManageProject($this->project)) {
            abort(403);
        }
    }

    protected function authorizeAdmin(): void
    {
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }
    }

    protected function authorizeFinancials(): void
    {
        if (! Auth::user()->canManageProjectFinancials($this->project)) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.projects.show');
    }
}
