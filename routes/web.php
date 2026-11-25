<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoSandboxController;
use App\Http\Controllers\ExpenditureController;
use App\Http\Controllers\InsightAlertController;
use App\Http\Controllers\ReceiptScanController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\EnsureSandboxSessionValid;
use App\Http\Middleware\RejectBotSubmissions;
use Illuminate\Support\Facades\Route;

// ── Public: splash landing page ───────────────────────────────────────────
Route::get('/', function () {
    // Logged-in users go straight to dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('splash');
})->name('splash');

Route::middleware('guest')->group(function () {
    Route::get('/demo', [DemoSandboxController::class, 'show'])->name('demo.show');
    Route::post('/demo/start', [DemoSandboxController::class, 'start'])
        ->middleware([RejectBotSubmissions::class.':sandbox', 'throttle:5,10'])
        ->name('demo.start');
});

// ── Public AI Concierge (rate-limited per IP) ──────────────────────────────
Route::post('/api/ai/concierge', [AiChatController::class, 'concierge'])
    ->middleware('throttle:10,1')
    ->name('ai.concierge');

// ── Protected routes (require login) ─────────────────────────────────────
Route::middleware(['auth', 'verified', EnsureSandboxSessionValid::class])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/subscriptions/export', [SubscriptionController::class, 'export'])->name('subscriptions.export');
    Route::get('/subscriptions/export/pdf', [SubscriptionController::class, 'exportPdf'])->name('subscriptions.export_pdf');
    Route::get('/subscriptions/resolve-icon', [SubscriptionController::class, 'resolveIcon'])
        ->middleware('throttle:60,1')
        ->name('subscriptions.resolve_icon');
    Route::post('/subscriptions/scan-receipt', [ReceiptScanController::class, 'scan'])
        ->middleware('throttle:10,1')
        ->name('subscriptions.scan_receipt');
    Route::post('/subscriptions/{subscription}/apply-receipt', [ReceiptScanController::class, 'applyToExisting'])
        ->name('subscriptions.apply_receipt');
    Route::post('/subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])
        ->name('subscriptions.renew');
    Route::resource('subscriptions', SubscriptionController::class);

    Route::post('/insights/{insightAlert}/read', [InsightAlertController::class, 'markRead'])->name('insights.read');
    Route::post('/insights/{insightAlert}/dismiss', [InsightAlertController::class, 'dismiss'])->name('insights.dismiss');

    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);

    Route::get('/expenditure', [ExpenditureController::class, 'index'])->name('expenditure.index');
    Route::get('/expenditure/export/csv', [ExpenditureController::class, 'exportCsv'])->name('expenditure.export_csv');
    Route::get('/expenditure/export/pdf', [ExpenditureController::class, 'exportPdf'])->name('expenditure.export_pdf');

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

    // ── Personal Financial AI Copilot (user/session scoped) ─────────────────
    Route::post('/api/ai/copilot', [AiChatController::class, 'copilot'])
        ->middleware('throttle:20,1')
        ->name('ai.copilot');
});

// ── Authentication routes ──────────────────────────────────────────────────
require __DIR__.'/auth.php';
