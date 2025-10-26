<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Public news routes (no authentication required)
Volt::route('news', 'public.news.index')->name('public.news.index');
Volt::route('news/{news}', 'public.news.show')->name('public.news.show');

Route::get('/', function () {
    return redirect()->route('public.news.index');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Volt::route('dashboard/news', 'news.index')->name('news.index');
    Volt::route('dashboard/news/{news}', 'news.show')->name('news.show');

    Volt::route('api-keys', 'api-keys.index')->name('api-keys.index');
    Volt::route('api-keys/create', 'api-keys.create')->name('api-keys.create');
    Volt::route('api-keys/{apiKey}/edit', 'api-keys.edit')->name('api-keys.edit');
});
