<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\MiddlewareInterface;
use App\Http\Request;
use App\Http\Response;

final class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = [
        'GET',
        'HEAD',
        'OPTIONS',
    ];

    public function process(
        Request $request,
        callable $next
    ): Response {
        $method = strtoupper($request->method());

        if (in_array($method, self::SAFE_METHODS, true)) {
            return $next($request);
        }

        if (!function_exists('verify_csrf_token')) {
            error_log('[CSRF] CSRF verification function is unavailable.');

            return $this->failure($request, 500);
        }

        $token = $request->input('_csrf_token', '');

        if (!is_string($token) || $token === '') {
            return $this->failure($request, 419);
        }

        if (!verify_csrf_token($token)) {
            return $this->failure($request, 419);
        }

        return $next($request);
    }

    private function failure(
        Request $request,
        int $statusCode
    ): Response {
        $message = $statusCode === 419
            ? 'درخواست نامعتبر یا منقضی شده است. صفحه را تازه‌سازی کنید.'
            : 'امکان بررسی امنیت درخواست وجود ندارد.';

        if ($request->wantsJson()) {
            return Response::json([
                'success' => false,
                'message' => $message,
            ], $statusCode);
        }

        return Response::html(
            '<!DOCTYPE html>
            <html lang="fa" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>خطای امنیتی</title>
            </head>
            <body>
                <h1>' . $statusCode . '</h1>
                <p>' . htmlspecialchars(
                    $message,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) . '</p>
                <a href="/">بازگشت به صفحه اصلی</a>
            </body>
            </html>',
            $statusCode
        );
    }
}
