<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;
use Throwable;

final class ValidationException extends RuntimeException
{
    private array $errors;

    private array $oldInput;

    public function __construct(
        array $errors,
        array $oldInput = [],
        string $message = 'اطلاعات واردشده معتبر نیست.',
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 422, $previous);

        $this->errors = $errors;
        $this->oldInput = $oldInput;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $messages) {
            if (is_array($messages)) {
                if (isset($messages[0])) {
                    return (string) $messages[0];
                }

                continue;
            }

            return (string) $messages;
        }

        return null;
    }

    public function oldInput(): array
    {
        return $this->oldInput;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }
}
