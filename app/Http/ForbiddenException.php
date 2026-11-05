<?php

declare(strict_types=1);

namespace App\Http;

final class ForbiddenException extends HttpException
{
    public function __construct(
        string $message = 'شما اجازه دسترسی به این بخش را ندارید.'
    ) {
        parent::__construct(403, $message);
    }
}
