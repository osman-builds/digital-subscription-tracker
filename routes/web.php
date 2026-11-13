<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenditureController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ── Public: splash landing page ───────────────────────────────────────────
Route::get('/', function () {
    // Logged-in users go straight to dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('splash');
})->name('splash');

// ── Protected routes (require login) ─────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/subscriptions/export', [SubscriptionController::class, 'export'])->name('subscriptions.export');
    Route::get('/subscriptions/resolve-icon', [SubscriptionController::class, 'resolveIcon'])
        ->middleware('throttle:60,1')
        ->name('subscriptions.resolve_icon');
    Route::resource('subscriptions', SubscriptionController::class);

    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);

    Route::get('/expenditure', [ExpenditureController::class, 'index'])->name('expenditure.index');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::get('/settings/security', [SettingsController::class, 'security'])->name('settings.security');
    Route::get('/settings/about', [SettingsController::class, 'about'])->name('settings.about');

    Route::patch('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences');
    Route::patch('/settings/account', [SettingsController::class, 'updateAccount'])->name('settings.account');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/settings/account', [SettingsController::class, 'deleteAccount'])->name('settings.delete_account');
    Route::post('/settings/dark-mode', [SettingsController::class, 'updateDarkMode'])->name('settings.dark_mode');
});

// ── Authentication routes ──────────────────────────────────────────────────
require __DIR__.'/auth.php';
