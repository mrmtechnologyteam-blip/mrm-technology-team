<?php

declare(strict_types=1);

namespace App\Http;

final class MethodNotAllowedException extends HttpException
{
    public function __construct(
        array $allowedMethods = [],
        string $message = 'متد درخواست‌شده برای این مسیر مجاز نیست.'
    ) {
        $allowedMethods = array_values(array_unique(array_filter(
            $allowedMethods,
            static fn ($method): bool =>
                is_string($method) &&
                preg_match('/^[A-Z]+$/', $method) === 1
        )));

        $headers = [];

        if ($allowedMethods !== []) {
            $headers['Allow'] = implode(', ', $allowedMethods);
        }

        parent::__construct(405, $message, $headers);
    }
}
