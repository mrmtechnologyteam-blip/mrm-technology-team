<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailVerificationToken;
use App\Models\User;
use PDO;
use RuntimeException;
use Throwable;

final class EmailVerificationService
{
    private PDO $db;
    private EmailVerificationToken $tokens;
    private User $users;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->tokens = new EmailVerificationToken($db);
        $this->users = new User($db);
    }

    /**
     * ایجاد توکن تأیید ایمیل برای کاربر
     */
    public function issueToken(int $userId, int $validMinutes = 60): string
    {
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

    /**
     * بررسی توکن و تأیید ایمیل کاربر
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

        $this->db->beginTransaction();

        try {
            $userId = $this->tokens->consume($token);

            if ($userId === null) {
                $this->db->rollBack();
                return false;
            }

            $user = $this->users->findById($userId);

            if (!$user) {
                throw new RuntimeException('User not found.');
            }

            if (!empty($user['email_verified_at'])) {
                $this->db->commit();
                return true;
            }

            $this->users->verifyEmail($userId);

            $this->db->commit();

            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * پاک‌سازی توکن‌های منقضی یا مصرف‌شده
     */
    public function cleanup(): int
    {
        return $this->tokens->deleteExpired();
    }
}
