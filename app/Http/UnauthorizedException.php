<?php

declare(strict_types=1);

namespace App\Http;

final class UnauthorizedException extends HttpException
{
    public function __construct(
        string $message = 'برای دسترسی به این بخش ابتدا وارد حساب کاربری خود شوید.'
    ) {
        parent::__construct(401, $message);
    }
}
