<?php

declare(strict_types=1);

namespace App\Http;

final class NotFoundException extends HttpException
{
    public function __construct(
        string $message = 'صفحه یا منبع موردنظر پیدا نشد.'
    ) {
        parent::__construct(404, $message);
    }
}
