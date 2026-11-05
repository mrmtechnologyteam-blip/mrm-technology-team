<?php

declare(strict_types=1);

namespace App\Http;

interface MiddlewareInterface
{
    public function process(
        Request $request,
        callable $next
    ): Response;
}
