<x-mail::message>
# {{ __('Welcome to :app', ['app' => config('app.name')]) }}

{{ __('Hello :name,', ['name' => $user->name]) }}

{{ __('An administrator has created an account for you on :app. Use the button below to choose your password and sign in.', ['app' => config('app.name')]) }}

<x-mail::button :url="$url">
{{ __('Set your password') }}
</x-mail::button>

{{ __('This invitation link will expire in :count minutes.', ['count' => $expireMinutes]) }}

{{ __('If you were not expecting this invitation, you can ignore this email.') }}

{{ __('Thanks') }},<br>
{{ config('app.name') }}
</x-mail::message>
