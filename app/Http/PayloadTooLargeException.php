<?php

declare(strict_types=1);

namespace App\Http;

final class PayloadTooLargeException extends HttpException
{
    public function __construct(
        string $message = 'حجم اطلاعات ارسالی بیش از حد مجاز است.'
    ) {
        parent::__construct(413, $message);
    }
}
