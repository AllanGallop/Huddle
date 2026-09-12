<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-br from-teal-50 via-amber-50/50 to-lime-50/40 text-zinc-900 antialiased dark:bg-none dark:bg-[radial-gradient(ellipse_at_top,_#1a3d3d_0%,_#171717_45%,_#0f0f0f_100%)] dark:text-zinc-100">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-teal-200/70 bg-white/90 shadow-sm backdrop-blur-sm dark:border-teal-900/50 dark:bg-zinc-950/80 dark:shadow-none">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                <flux:sidebar.group :heading="__('Work')" class="grid">
                    <flux:sidebar.item icon="layout-grid" :href="route('projects.index')" :current="request()->routeIs('projects.*')" wire:navigate>
                        {{ __('Projects') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="presentation-chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>
                        {{ __('Reports') }}
                    </flux:sidebar.item>
                    @if (auth()->user()->isAdmin())
                        <flux:sidebar.item icon="building-storefront" :href="route('customers.index')" :current="request()->routeIs('customers.*')" wire:navigate>
                            {{ __('Customers') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Community')" class="grid">
                    <flux:sidebar.item icon="calendar" :href="route('events.index')" :current="request()->routeIs('events.*')" wire:navigate>
                        {{ __('Events') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('members.index')" :current="request()->routeIs('members.*')" wire:navigate>
                        {{ __('Members') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Resources')" class="grid">
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('forms.index')" :current="request()->routeIs('forms.*')" wire:navigate>
                        {{ __('Forms') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="book-open" :href="route('wiki.show')" :current="request()->routeIs('wiki.*')" wire:navigate>
                        {{ __('Wiki') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="check-badge"
                        :href="route('accreditations.index')"
                        :current="request()->routeIs('accreditations.*') || request()->routeIs('mentors.*')"
                        wire:navigate
                    >
                        {{ __('Accreditations') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if (auth()->user()->isAdmin())
                    <flux:sidebar.group :heading="__('Organisation')" class="grid">
                        <flux:sidebar.item icon="shield-check" :href="route('admin.index')" :current="request()->routeIs('admin.*')" wire:navigate>
                            {{ __('Admin') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    :avatar="auth()->user()->avatarUrl()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <x-user-avatar :user="auth()->user()" size="sm" />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        <x-cookie-consent />

        @fluxScripts
    </body>
</html>
