<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class FlashMessageService
{
    private const SESSION_KEY = '_flash_messages';

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session is not active.');
        }
    }

    public function add(
        string $message,
        string $type = 'info'
    ): void {
        $allowedTypes = [
            'success',
            'error',
            'warning',
            'info',
        ];

        if (!in_array($type, $allowedTypes, true)) {
            $type = 'info';
        }

        $message = trim($message);

        if ($message === '') {
            return;
        }

        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        $_SESSION[self::SESSION_KEY][] = [
            'message' => $message,
            'type' => $type,
        ];
    }

    public function success(string $message): void
    {
        $this->add($message, 'success');
    }

    public function error(string $message): void
    {
        $this->add($message, 'error');
    }

    public function warning(string $message): void
    {
        $this->add($message, 'warning');
    }

    public function info(string $message): void
    {
        $this->add($message, 'info');
    }

    public function getAll(): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];

        unset($_SESSION[self::SESSION_KEY]);

        return is_array($messages) ? $messages : [];
    }

    public function hasMessages(): bool
    {
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    public function clear(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
