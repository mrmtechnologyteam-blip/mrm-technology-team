<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;

final class PasswordResetToken
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createForUser(
        int $userId,
        int $validMinutes = 30
    ): string {
        if ($userId <= 0) {
            throw new InvalidArgumentException('Invalid user ID.');
        }

        $validMinutes = max(5, min($validMinutes, 1440));

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $expiresAt = gmdate(
            'Y-m-d H:i:s',
            time() + ($validMinutes * 60)
        );

        $this->db->beginTransaction();

        try {
            $delete = $this->db->prepare(
                'DELETE FROM password_reset_tokens
                 WHERE user_id = :user_id'
            );

            $delete->execute([
                'user_id' => $userId,
            ]);

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
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function consume(string $token): ?int
    {
        $token = trim($token);

        if (
            strlen($token) !== 64 ||
            !ctype_xdigit($token)
        ) {
            return null;
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
                return null;
            }

            $update = $this->db->prepare(
                'UPDATE password_reset_tokens
                 SET used_at = UTC_TIMESTAMP()
                 WHERE id = :id
                   AND used_at IS NULL'
            );

            $update->execute([
                'id' => (int) $record['id'],
            ]);

            if ($update->rowCount() !== 1) {
                $this->db->rollBack();
                return null;
            }

            $userId = (int) $record['user_id'];

            $this->db->commit();

            return $userId;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function deleteExpired(): int
    {
        $stmt = $this->db->prepare(
            'DELETE FROM password_reset_tokens
             WHERE expires_at <= UTC_TIMESTAMP()
                OR used_at IS NOT NULL'
        );

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function deleteByUser(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM password_reset_tokens
             WHERE user_id = :user_id'
        );

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return $stmt->rowCount();
    }
}
