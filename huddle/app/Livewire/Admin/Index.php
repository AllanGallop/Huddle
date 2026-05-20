<?php

namespace App\Livewire\Admin;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\OrganizationSetting;
use App\Models\Role;
use App\Models\User;
use App\Models\UserFlags;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Admin')]
class Index extends Component
{
    use PasswordValidationRules, ProfileValidationRules;

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

    public bool $showTagModal = false;

    public ?int $editingTagId = null;

    public string $tag_name = '';

    public string $tag_description = '';

    /** @var array<int> */
    public array $assignedFlagIds = [];

    public function mount(): void
    {
        $this->loadBankDetails();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['users', 'tags', 'bank'], true)) {
            $this->activeTab = $tab;
        }
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->with(['role', 'flags'])
            ->orderBy('name')
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

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'password' => $validated['password'],
        ]);

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

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'password' => Hash::make(Str::password(32)),
        ]);

        Password::sendResetLink(['email' => $user->email]);

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

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
        ]);

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

        if ($user->isAdmin() && User::query()->where('role_id', 1)->count() <= 1) {
            $this->addError('user', __('You cannot remove the only admin account.'));

            return;
        }

        $user->delete();

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
