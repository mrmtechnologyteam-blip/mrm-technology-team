<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    private int $statusCode;

    private array $headers;

    public function __construct(
        int $statusCode,
        string $message = '',
        array $headers = [],
        ?Throwable $previous = null
    ) {
        if ($statusCode < 400 || $statusCode > 599) {
            throw new \InvalidArgumentException(
                'HTTP exception status must be between 400 and 599.'
            );
        }

        foreach ($headers as $name => $value) {
            if (
                !is_string($name) ||
                !is_string($value) ||
                preg_match('/[\r\n]/', $name . $value)
            ) {
                throw new \InvalidArgumentException(
                    'Invalid HTTP exception header.'
                );
            }
        }

        parent::__construct(
            $message !== '' ? $message : 'HTTP request failed.',
            $statusCode,
            $previous
        );

        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function headers(): array
    {
        return $this->headers;
    }
}
