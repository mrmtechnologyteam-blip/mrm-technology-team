<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use InvalidArgumentException;

final class EmailVerificationToken
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a verification token for a user.
     */
    public function createForUser(
        int $userId,
        int $validMinutes = 60
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
                'DELETE FROM email_verification_tokens
                 WHERE user_id = :user_id
                   AND used_at IS NULL'
            );

            $delete->execute([
                'user_id' => $userId
            ]);

            $insert = $this->db->prepare(
                'INSERT INTO email_verification_tokens
                    (user_id, token_hash, expires_at)
                 VALUES
                    (:user_id, :token_hash, :expires_at)'
            );

            $insert->execute([
                'user_id' => $userId,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt
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

    /**
     * Consume token and activate account atomically.
     */
    public function consumeAndVerify(string $token): ?int
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
                 FROM email_verification_tokens
                 WHERE token_hash = :token_hash
                   AND used_at IS NULL
                   AND expires_at > UTC_TIMESTAMP()
                 LIMIT 1
                 FOR UPDATE'
            );

            $stmt->execute([
                'token_hash' => $tokenHash
            ]);

            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $this->db->commit();
                return null;
            }

            $userId = (int) $record['user_id'];

            $userUpdate = $this->db->prepare(
                'UPDATE users
                 SET email_verified_at = COALESCE(
                         email_verified_at,
                         UTC_TIMESTAMP()
                     ),
                     account_status = CASE
                         WHEN account_status = "pending"
                         THEN "active"
                         ELSE account_status
                     END
                 WHERE id = :user_id'
            );

            $userUpdate->execute([
                'user_id' => $userId
            ]);

            if ($userUpdate->rowCount() < 1) {
                $checkUser = $this->db->prepare(
                    'SELECT id
                     FROM users
                     WHERE id = :user_id
                     LIMIT 1'
                );

                $checkUser->execute([
                    'user_id' => $userId
                ]);

                if (!$checkUser->fetchColumn()) {
                    throw new \RuntimeException(
                        'Verification user not found.'
                    );
                }
            }

            $tokenUpdate = $this->db->prepare(
                'UPDATE email_verification_tokens
                 SET used_at = UTC_TIMESTAMP()
                 WHERE id = :id
                   AND used_at IS NULL'
            );

            $tokenUpdate->execute([
                'id' => (int) $record['id']
            ]);

            if ($tokenUpdate->rowCount() !== 1) {
                throw new \RuntimeException(
                    'Verification token could not be consumed.'
                );
            }

            $this->db->commit();

            return $userId;

        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Consume a verification token.
     */
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
                 FROM email_verification_tokens
                 WHERE token_hash = :token_hash
                   AND used_at IS NULL
                   AND expires_at > UTC_TIMESTAMP()
                 LIMIT 1
                 FOR UPDATE'
            );

            $stmt->execute([
                'token_hash' => $tokenHash
            ]);

            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $this->db->commit();
                return null;
            }

            $update = $this->db->prepare(
                'UPDATE email_verification_tokens
                 SET used_at = UTC_TIMESTAMP()
                 WHERE id = :id
                   AND used_at IS NULL'
            );

            $update->execute([
                'id' => (int) $record['id']
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

    /**
     * Delete expired or used tokens.
     */
    public function deleteExpired(): int
    {
        $stmt = $this->db->prepare(
            'DELETE FROM email_verification_tokens
             WHERE expires_at <= UTC_TIMESTAMP()
                OR used_at IS NOT NULL'
        );

        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Delete all tokens belonging to a user.
     */
    public function deleteByUser(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare(
            'DELETE FROM email_verification_tokens
             WHERE user_id = :user_id'
        );

        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->rowCount();
    }
}
