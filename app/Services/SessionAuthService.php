<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class SessionAuthService
{
    private AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function login(string $email, string $password): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session is not active.');
        }

        $user = $this->auth->login($email, $password);

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['authenticated_at'] = time();

        return $user;
    }

    public function isAuthenticated(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE
            && !empty($_SESSION['logged_in'])
            && isset($_SESSION['user_id'])
            && (int) $_SESSION['user_id'] > 0;
    }

    public function userId(): ?int
    {
        return $this->isAuthenticated()
            ? (int) $_SESSION['user_id']
            : null;
    }

    public function userRole(): ?string
    {
        return $this->isAuthenticated()
            ? ($_SESSION['user_role'] ?? null)
            : null;
    }

    public function requireAuth(): void
    {
        if (!$this->isAuthenticated() || !$this->currentUser()) {
            throw new RuntimeException('Authentication required.');
        }
    }

    public function requireAdmin(): void
    {
        $this->requireAuth();

        if ($this->userRole() !== 'admin') {
            throw new RuntimeException('Administrator access required.');
        }
    }

    public function currentUser(): ?array
    {
        $userId = $this->userId();

        if ($userId === null) {
            return null;
        }

        $user = $this->auth->getUser($userId);

        if (
            !$user ||
            $user['account_status'] !== 'active' ||
            empty($user['email_verified_at'])
        ) {
            $this->logout();
            return null;
        }

        // همگام‌سازی نقش و مشخصات با دیتابیس
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        return $user;
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        session_destroy();
    }
}
