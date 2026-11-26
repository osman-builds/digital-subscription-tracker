<?php

namespace App\Providers;

use App\Auth\SandboxUserProvider;
use App\Contracts\AiProviderInterface;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\Ai\GeminiProvider;
use App\Services\FxRateService;
use App\Services\SandboxCategoryStore;
use App\Services\SandboxSubscriptionStore;
use Illuminate\Auth\Events\Verified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    private const HOURLY_KEY_PREFIX = 'hourly:';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(FxRateService::class);
        $this->app->bind(AiProviderInterface::class, GeminiProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();

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
            if ($event->user instanceof User && $event->user->hasRealVerifiedEmail()) {
                $event->user->notify(new WelcomeNotification);
            }
        });
    }

    /**
     * Configure composite burst + hourly rate limiters for cost-sensitive AI endpoints.
     */
    private function configureRateLimiters(): void
    {
        RateLimiter::for('ai-concierge', function (Request $request): array {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(10)->by($ip),
                Limit::perHour(60)->by(self::HOURLY_KEY_PREFIX.$ip),
            ];
        });

        RateLimiter::for('ai-copilot', function (Request $request): array {
            $user = $request->user();
            $identity = ($user !== null ? $user->id : 'guest').'|'.$request->ip();

            return [
                Limit::perMinute(20)->by($identity),
                Limit::perHour(120)->by(self::HOURLY_KEY_PREFIX.$identity),
            ];
        });

        RateLimiter::for('ai-receipt-scan', function (Request $request): array {
            $user = $request->user();
            $identity = ($user !== null ? $user->id : 'guest').'|'.$request->ip();

            return [
                Limit::perMinute(10)->by($identity),
                Limit::perHour(40)->by(self::HOURLY_KEY_PREFIX.$identity),
            ];
        });
    }
}
