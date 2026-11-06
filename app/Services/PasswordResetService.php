<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use PDO;
use RuntimeException;
use Throwable;

final class PasswordResetService
{
    private PDO $db;
    private User $users;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->users = new User($db);
    }

    public function issueToken(
        int $userId,
        int $validMinutes = 30,
        bool $replaceExisting = true
    ): string {
        if ($userId <= 0) {
            throw new RuntimeException('Invalid user ID.');
        }

        $user = $this->users->findById($userId);

        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        $validMinutes = max(5, min($validMinutes, 1440));

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $expiresAt = gmdate(
            'Y-m-d H:i:s',
            time() + ($validMinutes * 60)
        );

        if ($this->db->inTransaction()) {
            throw new RuntimeException(
                'A database transaction is already active.'
            );
        }

        $this->db->beginTransaction();

        try {
            if ($replaceExisting) {
                $delete = $this->db->prepare(
                    'DELETE FROM password_reset_tokens
                     WHERE user_id = :user_id'
                );

                $delete->execute([
                    'user_id' => $userId,
                ]);
            }

            $insert = $this->db->prepare(
                'INSERT INTO password_reset_tokens
                    (user_id, token_hash, expires_at)
                 VALUES
                    (:user_id, :token_hash, :expires_at)'
            );

            $insert->execute([
                'user_id' => $userId,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
            ]);

            $this->db->commit();

            return $token;

        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Delete previous reset tokens after successful email delivery.
     */
    public function cleanupOtherTokens(
        int $userId,
        string $keepToken
    ): int {
        if ($userId <= 0) {
            throw new RuntimeException('Invalid user ID.');
        }

        if (
            strlen($keepToken) !== 64 ||
            !ctype_xdigit($keepToken)
        ) {
            throw new RuntimeException('Invalid reset token.');
        }

        $keepHash = hash('sha256', $keepToken);

        $stmt = $this->db->prepare(
            'DELETE FROM password_reset_tokens
             WHERE user_id = :user_id
               AND token_hash <> :token_hash
               AND used_at IS NULL'
        );

        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => $keepHash,
        ]);

        return $stmt->rowCount();
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
                'Password does not meet security requirements.'
            );
        }

        if ($this->db->inTransaction()) {
            throw new RuntimeException(
                'A database transaction is already active.'
            );
        }

        $tokenHash = hash('sha256', $token);

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                'SELECT id, user_id
                 FROM password_reset_tokens
                 WHERE token_hash = :token_hash
                   AND used_at IS NULL
                   AND expires_at > UTC_TIMESTAMP()
                 LIMIT 1
                 FOR UPDATE'
            );

            $stmt->execute([
                'token_hash' => $tokenHash,
            ]);

            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $this->db->commit();
                return false;
            }

            $userId = (int) $record['user_id'];

            $user = $this->users->findById($userId);

            if (!$user) {
                throw new RuntimeException(
                    'User associated with reset token was not found.'
                );
            }

            if (!$this->users->updatePassword(
                $userId,
                $newPassword
            )) {
                throw new RuntimeException(
                    'Password update failed.'
                );
            }

            $updateToken = $this->db->prepare(
                'UPDATE password_reset_tokens
                 SET used_at = UTC_TIMESTAMP()
                 WHERE id = :id
                   AND used_at IS NULL'
            );

            $updateToken->execute([
                'id' => (int) $record['id'],
            ]);

            if ($updateToken->rowCount() !== 1) {
                throw new RuntimeException(
                    'Reset token could not be consumed.'
                );
            }

            $this->db->commit();

            return true;

        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function cleanup(): int
    {
        $stmt = $this->db->prepare(
            'DELETE FROM password_reset_tokens
             WHERE expires_at <= UTC_TIMESTAMP()
                OR used_at IS NOT NULL'
        );

        $stmt->execute();

        return $stmt->rowCount();
    }
}
