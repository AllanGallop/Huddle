<x-mail::message>
# {{ __('Reset your password') }}

{{ __('Hello :name,', ['name' => $user->name]) }}

{{ __('You are receiving this email because we received a password reset request for your :app account.', ['app' => config('app.name')]) }}

<x-mail::button :url="$url">
{{ __('Reset password') }}
</x-mail::button>

{{ __('This link will expire in :count minutes.', ['count' => $expireMinutes]) }}

{{ __('If you did not request a password reset, you can safely ignore this email. Your password will not change.') }}

{{ __('Thanks') }},<br>
{{ config('app.name') }}
</x-mail::message>
