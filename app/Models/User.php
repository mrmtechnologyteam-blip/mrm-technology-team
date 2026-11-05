<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;

final class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find user by ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM users
             WHERE id = :id
             LIMIT 1"
        );

        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM users
             WHERE email = :email
             LIMIT 1"
        );

        $stmt->execute([
            'email' => mb_strtolower(trim($email))
        ]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Create a new customer account.
     */
    public function createCustomer(array $data): int
    {
        $required = [
            'full_name',
            'email',
            'password',
            'whatsapp_number'
        ];

        foreach ($required as $field) {
            if (
                !isset($data[$field]) ||
                !is_string($data[$field]) ||
                trim($data[$field]) === ''
            ) {
                throw new RuntimeException(
                    "Missing required field: {$field}"
                );
            }
        }

        if (!filter_var(
            $data['email'],
            FILTER_VALIDATE_EMAIL
        )) {
            throw new RuntimeException(
                'Invalid email address.'
            );
        }

        if ($this->findByEmail($data['email'])) {
            throw new RuntimeException(
                'Email address already exists.'
            );
        }

        $passwordHash = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );

        if ($passwordHash === false) {
            throw new RuntimeException(
                'Password hashing failed.'
            );
        }

        $stmt = $this->db->prepare(
            "INSERT INTO users (
                full_name,
                email,
                password_hash,
                whatsapp_number,
                country,
                role,
                account_status
            ) VALUES (
                :full_name,
                :email,
                :password_hash,
                :whatsapp_number,
                :country,
                'customer',
                'pending'
            )"
        );

        $stmt->execute([
            'full_name' => trim($data['full_name']),
            'email' => mb_strtolower(trim($data['email'])),
            'password_hash' => $passwordHash,
            'whatsapp_number' => trim($data['whatsapp_number']),
            'country' => $data['country'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Verify user password.
     */
    public function verifyPassword(
        array $user,
        string $password
    ): bool {
        return isset($user['password_hash']) &&
            password_verify(
                $password,
                $user['password_hash']
            );
    }

    /**
     * Update user password.
     */
    public function updatePassword(
        int $userId,
        string $newPassword
    ): bool {
        $passwordHash = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        if ($passwordHash === false) {
            throw new RuntimeException(
                'Password hashing failed.'
            );
        }

        $stmt = $this->db->prepare(
            "UPDATE users
             SET password_hash = :password_hash
             WHERE id = :id"
        );

        $stmt->execute([
            'password_hash' => $passwordHash,
            'id' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Update email verification status.
     */
    public function verifyEmail(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users
             SET email_verified_at = CURRENT_TIMESTAMP,
                 account_status = 'active'
             WHERE id = :id
               AND email_verified_at IS NULL"
        );

        $stmt->execute(['id' => $userId]);

        return $stmt->rowCount() > 0;
    }
}
