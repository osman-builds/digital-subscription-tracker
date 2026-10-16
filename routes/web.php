<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenditureController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Redirect root
Route::get('/', fn () => redirect()->route('dashboard'));

// ── Protected routes (require login) ─────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('subscriptions', SubscriptionController::class);

    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);

    Route::get('/expenditure', [ExpenditureController::class, 'index'])->name('expenditure.index');
});

// ── Breeze auth routes ────────────────────────────────────────────────────────
require __DIR__ . '/auth.php';
