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

    Route::resource('subscriptions', SubscriptionController::class);

    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);

    Route::get('/expenditure', [ExpenditureController::class, 'index'])->name('expenditure.index');

    // Settings
    Route::get('/settings',                    [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings/preferences',      [SettingsController::class, 'updatePreferences'])->name('settings.preferences');
    Route::patch('/settings/account',          [SettingsController::class, 'updateAccount'])->name('settings.account');
    Route::patch('/settings/password',         [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/dark-mode',         [SettingsController::class, 'updateDarkMode'])->name('settings.dark_mode');
});

// ── Breeze auth routes ────────────────────────────────────────────────────
require __DIR__ . '/auth.php';
