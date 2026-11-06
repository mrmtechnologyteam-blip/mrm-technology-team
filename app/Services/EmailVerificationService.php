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

    /**
     * Issue a verification token.
     */
    public function issueToken(
        int $userId,
        int $validMinutes = 60,
        bool $replaceExisting = true
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
            $validMinutes,
            $replaceExisting
        );
    }

    /**
     * Remove previous unused tokens after successful email delivery.
     */
    public function cleanupOtherTokens(
        int $userId,
        string $keepToken
    ): int {
        return $this->tokens->deleteOtherUnusedTokens(
            $userId,
            $keepToken
        );
    }

    /**
     * Verify email and consume token atomically.
     */
    public function verify(string $token): bool
    {
        $token = trim($token);

        if (
            strlen($token) !== 64 ||
            !ctype_xdigit($token)
        ) {
            return false;
        }

        $userId = $this->tokens->consumeAndVerify($token);

        return $userId !== null;
    }

    /**
     * Remove expired and used tokens.
     */
    public function cleanup(): int
    {
        return $this->tokens->deleteExpired();
    }
}
