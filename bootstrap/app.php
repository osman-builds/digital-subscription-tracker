<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
                return null;
            }

            $expectsJson = $request->is('api/*') || $request->expectsJson();

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $safeMessages = [
                    401 => 'Unauthenticated.',
                    403 => 'Access denied.',
                    404 => 'Not found.',
                    405 => 'Method not allowed.',
                    419 => 'Session expired.',
                    429 => 'Too many requests.',
                    503 => 'Service unavailable.',
                ];

                if ($expectsJson) {
                    return response()->json([
                        'message' => $safeMessages[$status] ?? 'Request could not be processed.',
                    ], $status);
                }

                if (view()->exists("errors.{$status}")) {
                    return response()->view("errors.{$status}", [], $status);
                }

                if (! config('app.debug')) {
                    $fallbackView = $status >= 500 ? 'errors.500' : 'errors.404';

                    return response()->view($fallbackView, [], $status);
                }

                return null;
            }

            if (! config('app.debug')) {
                if ($expectsJson) {
                    return response()->json(['message' => 'Something went wrong.'], 500);
                }

                return response()->view('errors.500', [], 500);
            }

            return null;
        });
    })->create();
