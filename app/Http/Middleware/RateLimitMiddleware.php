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

        $ip = $request->ip();

        if ($ip === null || $ip === '') {
            $ip = 'unknown';
        }

        $key = 'http:' . hash('sha256', $ip);

        $allowed = $this->limiter->attempt(
            $key,
            $this->maxAttempts,
            $this->decaySeconds
        );

        if (!$allowed) {
            $retryAfter = max(
                1,
                $this->limiter->availableIn($key)
            );

            return Response::json(
                [
                    'success' => false,
                    'error' => 'too_many_requests',
                    'message' => 'تعداد درخواست‌های شما بیش از حد مجاز است. لطفاً کمی بعد دوباره تلاش کنید.',
                    'retry_after' => $retryAfter,
                ],
                429,
                [
                    'Retry-After' => (string) $retryAfter,
                    'Cache-Control' => 'no-store',
                ]
            );
        }

        return $next($request);
    }
}
