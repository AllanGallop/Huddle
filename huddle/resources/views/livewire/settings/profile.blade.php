<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile Settings') }}</flux:heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your photo, name, and email address')">
        <div class="my-6 space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <x-user-avatar :user="auth()->user()" size="xl" />

                <div class="min-w-0 flex-1 space-y-3">
                    <div>
                        <flux:heading size="sm">{{ __('Profile photo') }}</flux:heading>
                        <flux:text class="mt-1 text-sm">{{ __('JPG, PNG, or WebP up to 2MB. Shown across Huddle.') }}</flux:text>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-huddle-primary px-3 py-2 text-sm font-medium text-white transition hover:bg-huddle-primary/90">
                            <x-material-icon name="upload" class="text-[1.125rem]" />
                            {{ __('Upload photo') }}
                            <input
                                type="file"
                                wire:model="photo"
                                accept="image/jpeg,image/png,image/webp,image/gif"
                                class="sr-only"
                            />
                        </label>

                        @if (auth()->user()->avatar_path)
                            <flux:button type="button" variant="ghost" wire:click="removePhoto" wire:confirm="{{ __('Remove your profile photo?') }}">
                                {{ __('Remove') }}
                            </flux:button>
                        @endif
                    </div>

                    <div wire:loading wire:target="photo" class="text-xs text-zinc-500">{{ __('Uploading...') }}</div>
                    @error('photo')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <x-action-message class="text-sm" on="profile-updated">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            </div>
        </div>

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <flux:separator class="my-8" />

        <div>
            <flux:heading size="lg">{{ __('Your membership') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Membership period, tags, and accreditations on your account. Contact an administrator or mentor to request changes.') }}</flux:text>

            <x-user-membership :user="$this->membership" class="mt-6 rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/50" />
        </div>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>
</section>
