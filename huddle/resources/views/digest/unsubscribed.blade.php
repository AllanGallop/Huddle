<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Unsubscribed') }} — {{ config('app.name') }}</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="flex min-h-screen items-center justify-center bg-zinc-50 p-6 dark:bg-zinc-900">
        <div class="max-w-md rounded-xl border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-800">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">{{ __('You are unsubscribed') }}</h1>
            <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                {{ __(':email will no longer receive community digest emails.', ['email' => $user->email]) }}
            </p>
            <p class="mt-4 text-sm text-zinc-500">
                <a href="{{ route('notifications.edit') }}" class="font-medium text-huddle-primary hover:underline">{{ __('Re-enable in notification settings') }}</a>
            </p>
        </div>
    </body>
</html>
