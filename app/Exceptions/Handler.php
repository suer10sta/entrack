<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (HttpExceptionInterface $error, $request) {
            $status = $error->getStatusCode();
            if ($request->expectsJson() && in_array($status, [401, 403, 404, 419, 429, 500, 503], true)
                && in_array($error->getMessage(), ['', 'Unauthenticated.', 'Forbidden', 'Not Found', 'Too Many Attempts.', 'CSRF token mismatch.', 'Server Error', 'Service Unavailable', 'This action is unauthorized.'], true)) {
                return response()->json(['message' => __('messages.http_'.$status)], $status, $error->getHeaders());
            }
        });
        $this->renderable(function (Throwable $error, $request) {
            if ($request->expectsJson() && !config('app.debug')
                && !$error instanceof HttpExceptionInterface
                && !$error instanceof AuthenticationException
                && !$error instanceof ValidationException) {
                return response()->json(['message' => __('messages.http_500')], 500);
            }
        });
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return new JsonResponse(['message' => __('messages.http_401')], 401);
        }

        return parent::unauthenticated($request, $exception);
    }
}
