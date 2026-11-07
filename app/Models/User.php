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
     * Normalize email address.
     */
    private function normalizeEmail(string $email): string
    {
        $email = trim($email);

        return function_exists('mb_strtolower')
            ? mb_strtolower($email, 'UTF-8')
            : strtolower($email);
    }

    /**
     * Find user by ID.
     */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

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
        $email = $this->normalizeEmail($email);

        if ($email === '') {
            return null;
        }

        $stmt = $this->db->prepare(
            "SELECT *
             FROM users
             WHERE email = :email
             LIMIT 1"
        );

        $stmt->execute([
            'email' => $email
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

        $email = $this->normalizeEmail($data['email']);

        if (!filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )) {
            throw new RuntimeException(
                'Invalid email address.'
            );
        }

        if ($this->findByEmail($email)) {
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
            'email' => $email,
            'password_hash' => $passwordHash,
            'whatsapp_number' => trim($data['whatsapp_number']),
            'country' => isset($data['country'])
                ? trim((string) $data['country'])
                : null
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
     * Update user profile information.
     */
    public function updateProfile(
        int $userId,
        string $fullName,
        string $whatsappNumber,
        ?string $country = null
    ): bool {
        if ($userId <= 0) {
            throw new RuntimeException(
                'شناسه کاربر معتبر نیست.'
            );
        }

        $fullName = trim($fullName);
        $whatsappNumber = trim($whatsappNumber);
        $country = $country !== null
            ? trim($country)
            : null;

        $nameLength = function_exists('mb_strlen')
            ? mb_strlen($fullName, 'UTF-8')
            : strlen($fullName);

        if ($nameLength < 3 || $nameLength > 150) {
            throw new RuntimeException(
                'نام کامل باید بین ۳ تا ۱۵۰ کاراکتر باشد.'
            );
        }

        if (
            $whatsappNumber === '' ||
            strlen($whatsappNumber) > 30
        ) {
            throw new RuntimeException(
                'شماره واتساپ معتبر نیست.'
            );
        }

        if ($country !== null && $country === '') {
            $country = null;
        }

        if ($country !== null && strlen($country) > 100) {
            throw new RuntimeException(
                'نام کشور نباید بیشتر از ۱۰۰ کاراکتر باشد.'
            );
        }

        $stmt = $this->db->prepare(
            "UPDATE users
             SET
                full_name = :full_name,
                whatsapp_number = :whatsapp_number,
                country = :country
             WHERE id = :id
             LIMIT 1"
        );

        $stmt->execute([
            'full_name' => $fullName,
            'whatsapp_number' => $whatsappNumber,
            'country' => $country,
            'id' => $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Update user password.
     */
    public function updatePassword(
        int $userId,
        string $newPassword
    ): bool {
        if ($userId <= 0) {
            throw new RuntimeException(
                'Invalid user ID.'
            );
        }

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
        if ($userId <= 0) {
            return false;
        }

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
