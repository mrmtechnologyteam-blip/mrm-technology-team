<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailVerificationToken;
use App\Models\User;
use PDO;
use RuntimeException;

final class EmailVerificationService
{
    private EmailVerificationToken $tokens;
    private User $users;

    public function __construct(PDO $db)
    {
        $this->tokens = new EmailVerificationToken($db);
        $this->users = new User($db);
    }

    public function issueToken(
        int $userId,
        int $validMinutes = 60
    ): string {
        if ($userId <= 0) {
            throw new RuntimeException('Invalid user ID.');
        }

        $user = $this->users->findById($userId);

        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        if (!empty($user['email_verified_at'])) {
            throw new RuntimeException('Email is already verified.');
        }

        return $this->tokens->createForUser(
            $userId,
            $validMinutes
        );
    }

    public function verify(string $token): bool
    {
        $token = trim($token);

        if (
            strlen($token) !== 64 ||
            !ctype_xdigit($token)
        ) {
            return false;
        }

        // مصرف توکن؛ این متد تراکنش خودش را مدیریت می‌کند.
        $userId = $this->tokens->consume($token);

        if ($userId === null) {
            return false;
        }

        $user = $this->users->findById($userId);

        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        if (!empty($user['email_verified_at'])) {
            return true;
        }

        if (!$this->users->verifyEmail($userId)) {
            throw new RuntimeException('Email verification failed.');
        }

        return true;
    }

    public function cleanup(): int
    {
        return $this->tokens->deleteExpired();
    }
}
