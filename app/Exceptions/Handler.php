<?php

namespace App\Exceptions;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof RequestException) {
            return response()->json([
                'error' => [
                    'code' => 'AI_TIMEOUT',
                    'message' => 'AI timed out, suggest handoff to human',
                    'details' => [],
                ],
            ], 503);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            $status = $this->resolveStatusCode($e);
            $payload = [
                'error' => [
                    'code' => $this->resolveErrorCode($status),
                    'message' => $this->resolveErrorMessage($status),
                    'details' => [],
                ],
            ];

            return response()->json($payload, $status);
        }

        if ($e instanceof AuthorizationException) {
            return response()->view('errors.403', [], 403);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->view('errors.404', [], 404);
        }

        if ($this->isHttpException($e)) {
            $status = $e->getStatusCode();
            if (in_array($status, [403, 404], true)) {
                $view = "errors.$status";
                return response()->view($view, [], $status);
            }
        }

        // برای خطاهای ناشناخته، مطابق درخواست همیشه 404 سفارشی نشان داده می‌شود
        return response()->view('errors.404', [], 404);
    }

    protected function resolveStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        if ($e instanceof AuthorizationException) {
            return 403;
        }

        if ($e instanceof ModelNotFoundException) {
            return 404;
        }

        return 500;
    }

    protected function resolveErrorCode(int $status): string
    {
        return match ($status) {
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            default => 'GENERAL_ERROR',
        };
    }

    protected function resolveErrorMessage(int $status): string
    {
        return match ($status) {
            403 => 'شما مجوز دسترسی به این بخش را ندارید.',
            404 => 'مورد درخواستی پیدا نشد.',
            default => 'خطای غیرمنتظره‌ای رخ داده است.',
        };
    }
}
