<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a
                    href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                    class="flex flex-col items-center gap-2 font-medium"
                    wire:navigate
                >
                    <x-brand-mark variant="banner" class="h-12" />
                    <span class="sr-only">{{ config('app.name', 'Huddle') }}</span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        <x-cookie-consent />

        @fluxScripts
    </body>
</html>
