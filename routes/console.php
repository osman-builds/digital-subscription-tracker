<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Schedule
|--------------------------------------------------------------------------
|
| Register all scheduled tasks here. On Windows/Laragon, trigger the
| scheduler every minute via Task Scheduler:
|   Program: C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe
|   Args:    C:\laragon\www\subscription-tracker\artisan schedule:run
|
*/

// Refresh FX rates every 6 hours (free API tier: 1500 req/month)
Schedule::command('fx:refresh')->everySixHours();

// Send renewal reminders daily at 8am — checks 3 days ahead
Schedule::command('subscriptions:remind --days=3')->dailyAt('08:00');

// Create daily encrypted database snapshot at 2am
Schedule::command('app:backup --encrypt')->dailyAt('02:00');

// Prune 90-day old read/dismissed insights and AI extraction audits daily at 3am
Schedule::command('model:prune')->dailyAt('03:00');
