<?php

namespace App\Providers;

use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Password::defaults(function () {
            return $this->app->isProduction()
                ? Password::min(8)->mixedCase()->numbers()
                : Password::min(8);
        });

        Event::listen(Verified::class, function (Verified $event): void {
            $event->user->notify(new WelcomeNotification);
        });
    }
}
