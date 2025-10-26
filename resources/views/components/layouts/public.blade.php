<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    <a href="{{ route('public.news.index') }}" class="flex items-center space-x-2" wire:navigate>
                        <x-app-logo />
                        <span class="text-xl font-semibold text-zinc-900 dark:text-white">News Portal</span>
                    </a>

                    <div class="flex items-center gap-4">
                        @auth
                            <flux:button :href="route('dashboard')" variant="ghost" size="sm" wire:navigate>
                                {{ __('Dashboard') }}
                            </flux:button>
                        @else
                            <flux:button :href="route('login')" variant="ghost" size="sm" wire:navigate>
                                {{ __('Log in') }}
                            </flux:button>
                            @if (Route::has('register'))
                                <flux:button :href="route('register')" variant="primary" size="sm" wire:navigate>
                                    {{ __('Register') }}
                                </flux:button>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </flux:header>

        <flux:main class="container mx-auto px-4 py-8">
            {{ $slot }}
        </flux:main>

        <footer class="border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 mt-auto">
            <div class="container mx-auto px-4 py-6">
                <flux:text class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                    &copy; {{ date('Y') }} News Portal. All rights reserved.
                </flux:text>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>

