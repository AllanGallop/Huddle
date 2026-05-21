<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="relative hidden h-full flex-col bg-huddle-primary p-10 text-white lg:flex">
                <a
                    href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                    class="relative z-20 flex items-center"
                    wire:navigate
                >
                    <img
                        src="{{ \App\Support\Branding::bannerUrl(forDarkBackground: true) }}"
                        alt="{{ config('app.name', 'Huddle') }}"
                        class="h-12 w-auto max-w-full"
                    />
                    <span class="sr-only">{{ config('app.name', 'Huddle') }}</span>
                </a>

                <div class="relative z-20 mt-auto">
                    <flux:heading size="lg" class="text-white/90">
                        {{ __('Coordinate projects, events, and your community in one place.') }}
                    </flux:heading>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a
                        href="{{ auth()->check() ? route('dashboard') : route('login') }}"
                        class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden"
                        wire:navigate
                    >
                        <x-brand-mark variant="banner" class="h-12" />
                        <span class="sr-only">{{ config('app.name', 'Huddle') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
