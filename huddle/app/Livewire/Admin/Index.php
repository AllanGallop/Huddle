<?php

namespace App\Livewire\Admin;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\MembershipRenewal;
use App\Models\MembershipRenewalAssignment;
use App\Models\OrganizationSetting;
use App\Models\Role;
use App\Models\User;
use App\Models\UserFlags;
use App\Notifications\UserInvitationNotification;
use App\Services\BrandingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Admin')]
class Index extends Component
{
    use PasswordValidationRules, ProfileValidationRules, WithFileUploads;

    public string $activeTab = 'users';

    public bool $showUserModal = false;

    public ?int $editingUserId = null;

    public string $userModalMode = 'add';

    public string $name = '';

    public string $email = '';

    public ?int $role_id = null;

    public string $password = '';

    public string $password_confirmation = '';

    public string $account_name = '';

    public string $bank_name = '';

    public string $sort_code = '';

    public string $account_number = '';

    public string $iban = '';

    public string $payment_instructions = '';

    public $logoUpload = null;

    public $faviconUpload = null;

    public $bannerLightUpload = null;

    public $bannerDarkUpload = null;

    public bool $showTagModal = false;

    public ?int $editingTagId = null;

    public string $tag_name = '';

    public string $tag_description = '';

    /** @var array<int> */
    public array $assignedFlagIds = [];

    public string $membershipTab = 'periods';

    public bool $showRenewalModal = false;

    public ?int $editingRenewalId = null;

    public string $renewal_name = '';

    public string $renewal_start_date = '';

    public string $renewal_end_date = '';

    public bool $showMembershipAssignmentModal = false;

    public ?int $editingMembershipAssignmentId = null;

    public ?int $membership_assignment_user_id = null;

    public ?int $membership_assignment_renewal_id = null;

    public function mount(): void
    {
        $this->loadBankDetails();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['users', 'tags', 'membership', 'bank', 'branding'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function setMembershipTab(string $tab): void
    {
        if (in_array($tab, ['periods', 'assignments'], true)) {
            $this->membershipTab = $tab;
        }
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->with([
                'role',
                'flags',
                'membershipRenewalAssignments.membershipRenewal',
            ])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function membershipRenewals()
    {
        return MembershipRenewal::query()
            ->withCount('assignments')
            ->orderByDesc('name')
            ->get();
    }

    #[Computed]
    public function membershipAssignments()
    {
        return MembershipRenewalAssignment::query()
            ->with(['user.membershipRenewalAssignments.membershipRenewal', 'membershipRenewal'])
            ->orderByDesc('updated_at')
            ->get();
    }

    #[Computed]
    public function flags()
    {
        return UserFlags::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function roles()
    {
        return Role::query()->orderBy('id')->get();
    }

    public function openCreateUserModal(string $mode = 'add'): void
    {
        $this->resetUserForm();
        $this->userModalMode = $mode === 'invite' ? 'invite' : 'add';
        $this->role_id = Role::query()->where('name', 'member')->value('id') ?? 2;
        $this->showUserModal = true;
    }

    public function openEditUserModal(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->userModalMode = 'edit';
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        $this->password = '';
        $this->password_confirmation = '';
        $this->assignedFlagIds = $user->flags->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->showUserModal = true;
    }

    public function closeUserModal(): void
    {
        $this->showUserModal = false;
        $this->resetUserForm();
        $this->resetValidation();
    }

    public function saveUser(): void
    {
        if ($this->userModalMode === 'edit') {
            $this->updateUser();

            return;
        }

        if ($this->userModalMode === 'invite') {
            $this->inviteUser();

            return;
        }

        $this->createUser();
    }

    protected function createUser(): void
    {
        $validated = $this->validate([
            ...$this->profileRules(),
            'role_id' => ['required', 'exists:roles,id'],
            'password' => $this->passwordRules(),
        ]);

        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $user->role_id = $validated['role_id'];
        $user->save();

        $this->syncUserFlags($user);

        $this->closeUserModal();
        unset($this->users);
        session()->flash('status', __('User created successfully.'));
    }

    protected function inviteUser(): void
    {
        $validated = $this->validate([
            ...$this->profileRules(),
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::password(32)),
        ]);
        $user->role_id = $validated['role_id'];
        $user->save();

        $token = Password::broker()->createToken($user);
        $user->notify(new UserInvitationNotification($token));

        $this->syncUserFlags($user);

        $this->closeUserModal();
        unset($this->users);
        session()->flash('status', __('Invitation sent. :name can set their password via the email link.', ['name' => $user->name]));
    }

    protected function updateUser(): void
    {
        $user = User::query()->findOrFail($this->editingUserId);

        $rules = [
            ...$this->profileRules($user->id),
            'role_id' => ['required', 'exists:roles,id'],
        ];

        if ($this->password !== '') {
            $rules['password'] = $this->passwordRules();
        }

        $validated = $this->validate($rules);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
        $user->role_id = $validated['role_id'];
        $user->save();

        if (! empty($validated['password'] ?? null)) {
            $user->update(['password' => $validated['password']]);
        }

        $this->syncUserFlags($user);

        $this->closeUserModal();
        unset($this->users);
        session()->flash('status', __('User updated successfully.'));
    }

    public function openCreateTagModal(): void
    {
        $this->resetTagForm();
        $this->showTagModal = true;
    }

    public function openEditTagModal(int $tagId): void
    {
        $tag = UserFlags::query()->findOrFail($tagId);

        $this->editingTagId = $tag->id;
        $this->tag_name = $tag->name;
        $this->tag_description = $tag->description ?? '';
        $this->showTagModal = true;
    }

    public function closeTagModal(): void
    {
        $this->showTagModal = false;
        $this->resetTagForm();
        $this->resetValidation();
    }

    public function saveTag(): void
    {
        $validated = $this->validate([
            'tag_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('user_flags', 'name')->ignore($this->editingTagId),
            ],
            'tag_description' => ['nullable', 'string', 'max:500'],
        ]);

        $data = [
            'name' => $validated['tag_name'],
            'description' => $validated['tag_description'] ?? '',
        ];

        if ($this->editingTagId) {
            UserFlags::query()->findOrFail($this->editingTagId)->update($data);
            session()->flash('status', __('Tag updated successfully.'));
        } else {
            UserFlags::create($data);
            session()->flash('status', __('Tag created successfully.'));
        }

        $this->closeTagModal();
        unset($this->flags);
    }

    public function updatedRenewalName(): void
    {
        if (preg_match('/^\d{4}$/', $this->renewal_name) !== 1) {
            return;
        }

        $this->renewal_start_date = "{$this->renewal_name}-01-01";
        $this->renewal_end_date = "{$this->renewal_name}-12-31";
    }

    public function openCreateRenewalModal(): void
    {
        $this->resetRenewalForm();
        $this->showRenewalModal = true;
    }

    public function openEditRenewalModal(int $renewalId): void
    {
        $renewal = MembershipRenewal::query()->findOrFail($renewalId);

        $this->editingRenewalId = $renewal->id;
        $this->renewal_name = $renewal->name;
        $this->renewal_start_date = $renewal->start_date->format('Y-m-d');
        $this->renewal_end_date = $renewal->end_date->format('Y-m-d');
        $this->showRenewalModal = true;
    }

    public function closeRenewalModal(): void
    {
        $this->showRenewalModal = false;
        $this->resetRenewalForm();
        $this->resetValidation();
    }

    public function saveRenewal(): void
    {
        $validated = $this->validate([
            'renewal_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('membership_renewals', 'name')->ignore($this->editingRenewalId),
            ],
            'renewal_start_date' => ['required', 'date'],
            'renewal_end_date' => ['required', 'date', 'after_or_equal:renewal_start_date'],
        ]);

        $data = [
            'name' => $validated['renewal_name'],
            'start_date' => $validated['renewal_start_date'],
            'end_date' => $validated['renewal_end_date'],
        ];

        if ($this->editingRenewalId) {
            MembershipRenewal::query()->findOrFail($this->editingRenewalId)->update($data);
            session()->flash('status', __('Membership period updated successfully.'));
        } else {
            MembershipRenewal::create($data);
            session()->flash('status', __('Membership period created successfully.'));
        }

        $this->closeRenewalModal();
        unset($this->membershipRenewals, $this->membershipAssignments, $this->users);
    }

    public function deleteRenewal(int $renewalId): void
    {
        $renewal = MembershipRenewal::query()->findOrFail($renewalId);
        $renewal->assignments()->delete();
        $renewal->delete();

        unset($this->membershipRenewals, $this->membershipAssignments, $this->users);
        session()->flash('status', __('Membership period deleted successfully.'));
    }

    public function openCreateMembershipAssignmentModal(): void
    {
        $this->resetMembershipAssignmentForm();
        $this->showMembershipAssignmentModal = true;
    }

    public function openEditMembershipAssignmentModal(int $assignmentId): void
    {
        $assignment = MembershipRenewalAssignment::query()->findOrFail($assignmentId);

        $this->editingMembershipAssignmentId = $assignment->id;
        $this->membership_assignment_user_id = $assignment->user_id;
        $this->membership_assignment_renewal_id = $assignment->membership_renewal_id;
        $this->showMembershipAssignmentModal = true;
    }

    public function closeMembershipAssignmentModal(): void
    {
        $this->showMembershipAssignmentModal = false;
        $this->resetMembershipAssignmentForm();
        $this->resetValidation();
    }

    public function saveMembershipAssignment(): void
    {
        $validated = $this->validate([
            'membership_assignment_user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('membership_renewal_assignments', 'user_id')
                    ->where(fn ($query) => $query->where('membership_renewal_id', $this->membership_assignment_renewal_id))
                    ->ignore($this->editingMembershipAssignmentId),
            ],
            'membership_assignment_renewal_id' => ['required', 'exists:membership_renewals,id'],
        ]);

        $data = [
            'user_id' => $validated['membership_assignment_user_id'],
            'membership_renewal_id' => $validated['membership_assignment_renewal_id'],
        ];

        if ($this->editingMembershipAssignmentId) {
            MembershipRenewalAssignment::query()
                ->findOrFail($this->editingMembershipAssignmentId)
                ->update($data);
            session()->flash('status', __('Membership assignment updated successfully.'));
        } else {
            MembershipRenewalAssignment::create($data);
            session()->flash('status', __('Membership assigned successfully.'));
        }

        $this->closeMembershipAssignmentModal();
        unset($this->membershipAssignments, $this->membershipRenewals, $this->users);
    }

    public function deleteMembershipAssignment(int $assignmentId): void
    {
        MembershipRenewalAssignment::query()->findOrFail($assignmentId)->delete();

        unset($this->membershipAssignments, $this->membershipRenewals, $this->users);
        session()->flash('status', __('Membership assignment removed successfully.'));
    }

    protected function resetRenewalForm(): void
    {
        $this->reset([
            'editingRenewalId',
            'renewal_name',
            'renewal_start_date',
            'renewal_end_date',
        ]);
    }

    protected function resetMembershipAssignmentForm(): void
    {
        $this->reset([
            'editingMembershipAssignmentId',
            'membership_assignment_user_id',
            'membership_assignment_renewal_id',
        ]);
    }

    public function deleteTag(int $tagId): void
    {
        $tag = UserFlags::query()->findOrFail($tagId);
        $tag->users()->detach();
        $tag->delete();

        unset($this->flags, $this->users);
        session()->flash('status', __('Tag deleted successfully.'));
    }

    protected function syncUserFlags(User $user): void
    {
        $this->validate([
            'assignedFlagIds' => ['array'],
            'assignedFlagIds.*' => ['integer', 'exists:user_flags,id'],
        ]);

        $user->flags()->sync($this->assignedFlagIds);
    }

    protected function resetTagForm(): void
    {
        $this->reset(['editingTagId', 'tag_name', 'tag_description']);
    }

    public function deleteUser(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        if ($user->id === Auth::id()) {
            $this->addError('user', __('You cannot delete your own account.'));

            return;
        }

        if ($user->isAdmin() && User::query()->whereHas('role', fn ($query) => $query->where('name', 'admin'))->count() <= 1) {
            $this->addError('user', __('You cannot remove the only admin account.'));

            return;
        }

        $erasure = app(\App\Services\UserDataErasureService::class);

        if ($erasure->isPlaceholder($user)) {
            $this->addError('user', __('This system account cannot be deleted.'));

            return;
        }

        $erasure->erase($user);

        unset($this->users);
        session()->flash('status', __('User deleted successfully.'));
    }

    public function saveBankDetails(): void
    {
        $validated = $this->validate([
            'account_name' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'sort_code' => ['nullable', 'string', 'max:32'],
            'account_number' => ['nullable', 'string', 'max:32'],
            'iban' => ['nullable', 'string', 'max:64'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
        ]);

        OrganizationSetting::instance()->update($validated);

        session()->flash('status', __('Bank details saved.'));
    }

    public function saveBranding(BrandingService $branding): void
    {
        $this->validate([
            'logoUpload' => ['nullable', 'file', 'mimes:svg,png,jpg,jpeg,webp', 'max:2048'],
            'faviconUpload' => ['nullable', 'file', 'mimes:svg,png,ico', 'max:512'],
            'bannerLightUpload' => ['nullable', 'file', 'mimes:svg,png,jpg,jpeg,webp', 'max:2048'],
            'bannerDarkUpload' => ['nullable', 'file', 'mimes:svg,png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $settings = OrganizationSetting::instance();

        if ($this->logoUpload) {
            $branding->storeUpload($settings, $this->logoUpload, 'logo');
        }

        if ($this->faviconUpload) {
            $branding->storeUpload($settings, $this->faviconUpload, 'favicon');
        }

        if ($this->bannerLightUpload) {
            $branding->storeUpload($settings, $this->bannerLightUpload, 'banner_light');
        }

        if ($this->bannerDarkUpload) {
            $branding->storeUpload($settings, $this->bannerDarkUpload, 'banner_dark');
        }

        $this->reset(['logoUpload', 'faviconUpload', 'bannerLightUpload', 'bannerDarkUpload']);
        $settings->refresh();

        session()->flash('status', __('Branding saved.'));
    }

    public function resetBranding(BrandingService $branding): void
    {
        $branding->resetBranding(OrganizationSetting::instance());

        $this->reset(['logoUpload', 'faviconUpload', 'bannerLightUpload', 'bannerDarkUpload']);

        session()->flash('status', __('Branding reset to defaults.'));
    }

    #[Computed]
    public function organizationSettings(): OrganizationSetting
    {
        return OrganizationSetting::instance();
    }

    protected function loadBankDetails(): void
    {
        $settings = OrganizationSetting::instance();

        $this->account_name = $settings->account_name ?? '';
        $this->bank_name = $settings->bank_name ?? '';
        $this->sort_code = $settings->sort_code ?? '';
        $this->account_number = $settings->account_number ?? '';
        $this->iban = $settings->iban ?? '';
        $this->payment_instructions = $settings->payment_instructions ?? '';
    }

    protected function resetUserForm(): void
    {
        $this->reset([
            'editingUserId',
            'name',
            'email',
            'role_id',
            'password',
            'password_confirmation',
            'assignedFlagIds',
        ]);
        $this->assignedFlagIds = [];
        $this->userModalMode = 'add';
    }

    public function render()
    {
        return view('livewire.admin.index');
    }
}
