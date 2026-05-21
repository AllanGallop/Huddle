@props([
    'variant' => 'logo',
])

@php
    $appName = config('app.name', 'Huddle');
@endphp

@if ($variant === 'banner')
    <img
        src="{{ \App\Support\Branding::bannerUrl() }}"
        alt="{{ $appName }}"
        {{ $attributes->class('h-10 w-auto max-w-full dark:hidden') }}
    />
    <img
        src="{{ \App\Support\Branding::bannerUrl(forDarkBackground: true) }}"
        alt="{{ $appName }}"
        {{ $attributes->class('hidden h-10 w-auto max-w-full dark:block') }}
    />
@else
    <img
        src="{{ \App\Support\Branding::logoUrl() }}"
        alt="{{ $appName }}"
        {{ $attributes->class('size-10 object-contain') }}
    />
@endif
