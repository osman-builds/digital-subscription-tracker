<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class SafeExceptionRenderer
{
    /**
     * Safe, generic JSON error messages keyed by HTTP status code.
     *
     * @var array<int, string>
     */
    private const SAFE_JSON_MESSAGES = [
        401 => 'Unauthenticated.',
        403 => 'Access denied.',
        404 => 'Not found.',
        405 => 'Method not allowed.',
        419 => 'Session expired.',
        429 => 'Too many requests.',
        503 => 'Service unavailable.',
    ];

    /**
     * Render a safe, leak-free HTTP response for unhandled exceptions.
     */
    public function render(Throwable $exception, Request $request): ?Response
    {
        $response = null;

        if (! $this->shouldDelegateToFramework($exception)) {
            $expectsJson = $request->is('api/*') || $request->expectsJson();
            $response = $exception instanceof HttpExceptionInterface
                ? $this->renderHttpException($exception, $expectsJson)
                : $this->renderServerFailure($expectsJson);
        }

        return $response;
    }

    /**
     * Allow Laravel to handle standard validation and authentication redirects natively.
     */
    private function shouldDelegateToFramework(Throwable $exception): bool
    {
        return $exception instanceof ValidationException || $exception instanceof AuthenticationException;
    }

    /**
     * Render a sanitized HTTP exception response without exposing route or framework internals.
     */
    private function renderHttpException(HttpExceptionInterface $exception, bool $expectsJson): ?Response
    {
        $status = $exception->getStatusCode();

        if ($expectsJson) {
            $message = self::SAFE_JSON_MESSAGES[$status] ?? 'Request could not be processed.';

            return response()->json(['message' => $message], $status);
        }

        $view = $this->resolveErrorView($status);

        return $view !== null ? response()->view($view, [], $status) : null;
    }

    /**
     * Resolve the branded Blade error view name for an HTTP status code.
     */
    private function resolveErrorView(int $status): ?string
    {
        $view = view()->exists("errors.{$status}") ? "errors.{$status}" : null;

        if ($view === null && ! config('app.debug')) {
            $view = $status >= 500 ? 'errors.500' : 'errors.404';
        }

        return $view;
    }

    /**
     * Render a generic 500 failure response when debug mode is disabled.
     */
    private function renderServerFailure(bool $expectsJson): ?Response
    {
        if (config('app.debug')) {
            return null;
        }

        return $expectsJson
            ? response()->json(['message' => 'Something went wrong.'], 500)
            : response()->view('errors.500', [], 500);
    }
}
