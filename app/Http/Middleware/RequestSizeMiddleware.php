<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\MiddlewareInterface;
use App\Http\Request;
use App\Http\Response;

final class RequestSizeMiddleware implements MiddlewareInterface
{
    private int $maxBytes;

    public function __construct(int $maxBytes = 10_485_760)
    {
        if ($maxBytes < 1) {
            throw new \InvalidArgumentException(
                'Maximum request size must be greater than zero.'
            );
        }

        $this->maxBytes = $maxBytes;
    }

    public function process(
        Request $request,
        callable $next
    ): Response {
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? null;

        if ($contentLength !== null) {
            if (
                !is_scalar($contentLength)
                || !ctype_digit((string) $contentLength)
            ) {
                return $this->tooLarge($request);
            }

            if ((int) $contentLength > $this->maxBytes) {
                return $this->tooLarge($request);
            }
        }

        return $next($request);
    }

    private function tooLarge(Request $request): Response
    {
        $message = 'حجم اطلاعات ارسالی بیش از حد مجاز است.';

        if ($request->wantsJson()) {
            return Response::json([
                'success' => false,
                'message' => $message,
            ], 413);
        }

        return Response::html(
            '<!DOCTYPE html>
            <html lang="fa" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>413 - حجم درخواست بیش از حد مجاز</title>
            </head>
            <body>
                <h1>413</h1>
                <p>' . htmlspecialchars(
                    $message,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) . '</p>
                <a href="/">بازگشت به صفحه اصلی</a>
            </body>
            </html>',
            413
        );
    }
}
