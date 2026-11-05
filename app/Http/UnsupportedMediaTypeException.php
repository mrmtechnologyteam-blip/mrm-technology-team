<?php

declare(strict_types=1);

namespace App\Http;

final class UnsupportedMediaTypeException extends HttpException
{
    public function __construct(
        string $message = 'نوع محتوای ارسالی پشتیبانی نمی‌شود.'
    ) {
        parent::__construct(415, $message);
    }
}
