<?php

declare(strict_types=1);

namespace App\Http;

final class ConflictException extends HttpException
{
    public function __construct(
        string $message = 'این درخواست با وضعیت فعلی اطلاعات سازگار نیست.'
    ) {
        parent::__construct(409, $message);
    }
}
