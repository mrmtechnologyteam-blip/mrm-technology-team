<?php

declare(strict_types=1);

namespace App\Http;

use Throwable;

final class ExceptionHandler
{
    public function handle(Throwable $exception): Response
    {
        if ($exception instanceof ValidationException) {
            return Response::json([
                'success' => false,
                'error' => 'validation_error',
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof HttpException) {
            return Response::json([
                'success' => false,
                'error' => $this->errorCode($exception->statusCode()),
                'message' => $exception->getMessage(),
            ], $exception->statusCode(), $exception->headers());
        }

        error_log(sprintf(
            '[ExceptionHandler] %s in %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));

        if (defined('APP_DEBUG') && APP_DEBUG === true) {
            return Response::json([
                'success' => false,
                'error' => 'internal_server_error',
                'message' => $exception->getMessage(),
            ], 500);
        }

        return Response::json([
            'success' => false,
            'error' => 'internal_server_error',
            'message' => 'خطای داخلی سرور رخ داده است.',
        ], 500);
    }

    private function errorCode(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'bad_request',
            401 => 'unauthorized',
            403 => 'forbidden',
            404 => 'not_found',
            405 => 'method_not_allowed',
            409 => 'conflict',
            413 => 'payload_too_large',
            415 => 'unsupported_media_type',
            422 => 'unprocessable_entity',
            429 => 'too_many_requests',
            default => 'http_error',
        };
    }
}
