<?php

declare(strict_types=1);

namespace App\Http;

final class TooManyRequestsException extends HttpException
{
    public function __construct(
        string $message = 'تعداد درخواست‌های شما بیش از حد مجاز است.',
        int $retryAfter = 60
    ) {
        $retryAfter = max(1, $retryAfter);

        parent::__construct(
            429,
            $message,
            [
                'Retry-After' => (string) $retryAfter,
                'Cache-Control' => 'no-store',
            ]
        );
    }
}
