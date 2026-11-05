<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailVerificationToken;
use PDO;
use InvalidArgumentException;
use RuntimeException;

final class AuthService
{
    private PDO $db;
    private EmailVerificationToken $verificationTokens;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->verificationTokens = new EmailVerificationToken($db);
    }

    /**
     * Normalize text to lowercase.
     */
    private function normalizeText(string $value): string
    {
        $value = trim($value);

        return function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);
    }

    /**
     * Get UTF-8 string length with fallback.
     */
    private function stringLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }

        $count = preg_match_all('/./us', $value, $matches);

        return $count !== false ? $count : strlen($value);
    }

    /**
     * Register a customer.
     */
    public function register(array $data): array
    {
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $email = $this->normalizeText((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $whatsapp = trim((string) ($data['whatsapp_number'] ?? ''));
        $country = trim((string) ($data['country'] ?? ''));

        if ($fullName === '' || $this->stringLength($fullName) > 150) {
            throw new InvalidArgumentException('نام کامل معتبر نیست.');
        }

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $this->stringLength($email) > 190
        ) {
            throw new InvalidArgumentException('ایمیل معتبر نیست.');
        }

        if (
            strlen($password) < 8 ||
            strlen($password) > 128 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)
        ) {
            throw new InvalidArgumentException(
                'رمز عبور باید حداقل ۸ کاراکتر و شامل حروف بزرگ، کوچک و عدد باشد.'
            );
        }

        if ($whatsapp === '' || $this->stringLength($whatsapp) > 30) {
            throw new InvalidArgumentException('شماره واتساپ معتبر نیست.');
        }

        if ($this->stringLength($country) > 100) {
            throw new InvalidArgumentException('نام کشور بیش از حد طولانی است.');
        }

        $check = $this->db->prepare(
            'SELECT id FROM users WHERE email = :email LIMIT 1'
        );
        $check->execute(['email' => $email]);

        if ($check->fetchColumn()) {
            throw new RuntimeException('این ایمیل قبلاً ثبت شده است.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false) {
            throw new RuntimeException('ساخت رمز عبور با مشکل مواجه شد.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (
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
                "customer",
                "pending"
            )'
        );

        $stmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => $passwordHash,
            'whatsapp_number' => $whatsapp,
            'country' => $country !== '' ? $country : null,
        ]);

        $userId = (int) $this->db->lastInsertId();

        try {
            $token = $this->verificationTokens->createForUser($userId);
        } catch (\Throwable $exception) {
            $delete = $this->db->prepare(
                'DELETE FROM users
                 WHERE id = :id
                   AND role = "customer"'
            );

            $delete->execute(['id' => $userId]);

            throw $exception;
        }

        return [
            'user_id' => $userId,
            'email' => $email,
            'verification_token' => $token,
            'account_status' => 'pending',
        ];
    }

    /**
     * Login user.
     */
    public function login(string $email, string $password): array
    {
        $email = $this->normalizeText($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            throw new InvalidArgumentException('ایمیل یا رمز عبور معتبر نیست.');
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                full_name,
                email,
                password_hash,
                role,
                account_status,
                email_verified_at
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new RuntimeException('ایمیل یا رمز عبور اشتباه است.');
        }

        if ($user['account_status'] === 'suspended') {
            throw new RuntimeException('حساب کاربری شما مسدود شده است.');
        }

        if (empty($user['email_verified_at'])) {
            throw new RuntimeException('ابتدا ایمیل خود را تأیید کنید.');
        }

        if ($user['account_status'] !== 'active') {
            throw new RuntimeException('حساب کاربری شما هنوز فعال نشده است.');
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);

            if ($newHash === false) {
                throw new RuntimeException('به‌روزرسانی رمز عبور ناموفق بود.');
            }

            $update = $this->db->prepare(
                'UPDATE users
                 SET password_hash = :password_hash
                 WHERE id = :id'
            );

            $update->execute([
                'password_hash' => $newHash,
                'id' => (int) $user['id'],
            ]);
        }

        unset($user['password_hash']);

        return $user;
    }

    /**
     * Get user information.
     */
    public function getUser(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT
                id,
                full_name,
                email,
                whatsapp_number,
                country,
                role,
                email_verified_at,
                account_status,
                created_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute(['id' => $userId]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
}
