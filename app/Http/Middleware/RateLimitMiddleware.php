<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\MiddlewareInterface;
use App\Http\RateLimiter;
use App\Http\Request;
use App\Http\Response;

final class RateLimitMiddleware implements MiddlewareInterface
{
    private RateLimiter $limiter;

    private int $maxAttempts;

    private int $decaySeconds;

    public function __construct(
        RateLimiter $limiter,
        int $maxAttempts = 120,
        int $decaySeconds = 60
    ) {
        $this->limiter = $limiter;
        $this->maxAttempts = max(1, $maxAttempts);
        $this->decaySeconds = max(1, $decaySeconds);
    }

    public function process(
        Request $request,
        callable $next
    ): Response {
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $ip = $request->ip() ?? 'unknown';

        $globalKey = 'http:' . hash('sha256', $ip);

        $globalAllowed = $this->limiter->attempt(
            $globalKey,
            $this->maxAttempts,
            $this->decaySeconds
        );

        if (!$globalAllowed) {
            return $this->tooManyRequests(
                $globalKey,
                'تعداد درخواست‌های شما بیش از حد مجاز است.'
            );
        }

        if ($request->isMethod('POST')) {
            $rules = [
                '/login' => [
                    'attempts' => 10,
                    'seconds' => 900,
                ],

                '/register' => [
                    'attempts' => 5,
                    'seconds' => 900,
                ],

                '/forgot-password' => [
                    'attempts' => 5,
                    'seconds' => 900,
                ],

                '/reset-password' => [
                    'attempts' => 10,
                    'seconds' => 900,
                ],
            ];

            $path = $request->path();

            if (isset($rules[$path])) {
                $rule = $rules[$path];

                $key = 'auth:'
                    . hash('sha256', $ip)
                    . ':'
                    . hash('sha256', $path);

                $allowed = $this->limiter->attempt(
                    $key,
                    $rule['attempts'],
                    $rule['seconds']
                );

                if (!$allowed) {
                    return $this->tooManyRequests(
                        $key,
                        'تعداد تلاش‌های شما بیش از حد مجاز است. لطفاً ۱۵ دقیقه بعد دوباره تلاش کنید.'
                    );
                }
            }
        }

        return $next($request);
    }

    private function tooManyRequests(
        string $key,
        string $message
    ): Response {
        $retryAfter = max(
            1,
            $this->limiter->availableIn($key)
        );

        return Response::json(
            [
                'success' => false,
                'error' => 'too_many_requests',
                'message' => $message,
                'retry_after' => $retryAfter,
            ],
            429,
            [
                'Retry-After' => (string) $retryAfter,
                'Cache-Control' => 'no-store',
            ]
        );
    }
}
