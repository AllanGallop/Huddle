<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use ProfileValidationRules;
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public $photo = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function updatedPhoto(): void
    {
        $this->uploadPhoto();
    }

    public function uploadPhoto(): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        /** @var TemporaryUploadedFile $photo */
        $photo = $this->photo;
        $user = Auth::user();

        $user->deleteAvatarFile();

        $path = $photo->store('avatars/'.$user->id, 'public');

        $user->forceFill(['avatar_path' => $path])->save();

        $this->reset('photo');
        $this->dispatch('profile-updated', name: $user->name);
    }

    public function removePhoto(): void
    {
        $user = Auth::user();

        $user->deleteAvatarFile();
        $user->forceFill(['avatar_path' => null])->save();

        $this->reset('photo');
        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }

    #[Computed]
    public function membership()
    {
        return Auth::user()->load([
            'roles',
            'flags',
            'membershipRenewalAssignments' => fn ($query) => $query
                ->with('membershipRenewal')
                ->orderByDesc('membership_renewal_id'),
            'accreditationAssignments' => fn ($query) => $query
                ->with('accreditation')
                ->orderBy('accreditation_id'),
        ]);
    }
}
