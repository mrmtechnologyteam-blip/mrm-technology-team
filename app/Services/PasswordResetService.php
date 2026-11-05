<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\User;
use PDO;
use RuntimeException;

final class PasswordResetService
{
    private PasswordResetToken $tokens;
    private User $users;

    public function __construct(PDO $db)
    {
        $this->tokens = new PasswordResetToken($db);
        $this->users = new User($db);
    }

    public function issueToken(
        int $userId,
        int $validMinutes = 30
    ): string {
        if ($userId <= 0) {
            throw new RuntimeException('Invalid user ID.');
        }

        $user = $this->users->findById($userId);

        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        return $this->tokens->createForUser(
            $userId,
            $validMinutes
        );
    }

    public function resetPassword(
        string $token,
        string $newPassword
    ): bool {
        $token = trim($token);

        if (
            strlen($token) !== 64 ||
            !ctype_xdigit($token)
        ) {
            return false;
        }

        if (
            strlen($newPassword) < 8 ||
            strlen($newPassword) > 128 ||
            !preg_match('/[A-Z]/', $newPassword) ||
            !preg_match('/[a-z]/', $newPassword) ||
            !preg_match('/[0-9]/', $newPassword)
        ) {
            throw new RuntimeException(
                'Password must contain at least 8 characters, uppercase, lowercase and a number.'
            );
        }

        $userId = $this->tokens->consume($token);

        if ($userId === null) {
            return false;
        }

        $user = $this->users->findById($userId);

        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        if (!$this->users->updatePassword($userId, $newPassword)) {
            throw new RuntimeException('Password update failed.');
        }

        return true;
    }

    public function cleanup(): int
    {
        return $this->tokens->deleteExpired();
    }
}
