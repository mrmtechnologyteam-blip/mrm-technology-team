<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\MiddlewareInterface;
use App\Http\Request;
use App\Http\Response;

final class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function process(
        Request $request,
        callable $next
    ): Response {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'X-Permitted-Cross-Domain-Policies' => 'none',
        ];

        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value);
        }

        if (
            isset($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== ''
            && strtolower((string) $_SERVER['HTTPS']) !== 'off'
        ) {
            $response->setHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
