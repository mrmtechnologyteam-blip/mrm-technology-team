<?php

declare(strict_types=1);

namespace App\Http;

final class BadRequestException extends HttpException
{
    public function __construct(
        string $message = 'درخواست ارسال‌شده معتبر نیست.'
    ) {
        parent::__construct(400, $message);
    }
}
