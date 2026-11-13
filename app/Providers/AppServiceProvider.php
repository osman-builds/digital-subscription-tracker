<?php

namespace App\Providers;

use App\Auth\SandboxUserProvider;
use App\Models\Category;
use App\Models\Subscription;
use App\Notifications\WelcomeNotification;
use App\Services\SandboxCategoryStore;
use App\Services\SandboxSubscriptionStore;
use Illuminate\Auth\Events\Verified;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
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
        Auth::provider('sandbox-eloquent', function ($app, array $config): SandboxUserProvider {
            return new SandboxUserProvider($app['hash'], $config['model']);
        });

        Paginator::useBootstrapFive();

        Password::defaults(function () {
            return $this->app->isProduction()
                ? Password::min(8)->mixedCase()->numbers()
                : Password::min(8);
        });

        Route::bind('subscription', function (string $value): Subscription {
            $user = auth()->user();
            if ($user !== null && $user->isSandbox()) {
                return app(SandboxSubscriptionStore::class)->findOrFail($user->id, (int) $value);
            }

            return Subscription::findOrFail($value);
        });

        Route::bind('category', function (string $value): Category {
            $user = auth()->user();
            if ($user !== null && $user->isSandbox()) {
                return app(SandboxCategoryStore::class)->findOrFail($user->id, (int) $value);
            }

            return Category::findOrFail($value);
        });

        Event::listen(Verified::class, function (Verified $event): void {
            if ($event->user->hasRealVerifiedEmail()) {
                $event->user->notify(new WelcomeNotification);
            }
        });
    }
}
