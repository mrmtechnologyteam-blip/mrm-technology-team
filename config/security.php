<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| MRM TECHNOLOGY TEAM
| Security Functions
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/bootstrap.php';

/*
|--------------------------------------------------------------------------
| HTML Escape
|--------------------------------------------------------------------------
*/

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

/*
|--------------------------------------------------------------------------
| Generate CSRF Token
|--------------------------------------------------------------------------
*/

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (
            !isset($_SESSION['_csrf_token']) ||
            !is_string($_SESSION['_csrf_token']) ||
            $_SESSION['_csrf_token'] === ''
        ) {
            $_SESSION['_csrf_token'] = bin2hex(
                random_bytes(32)
            );
        }

        return $_SESSION['_csrf_token'];
    }
}

/*
|--------------------------------------------------------------------------
| CSRF Hidden Input
|--------------------------------------------------------------------------
*/

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' .
            e(csrf_token()) .
            '">';
    }
}

/*
|--------------------------------------------------------------------------
| Verify Submitted CSRF Token
|--------------------------------------------------------------------------
*/

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token(string $submittedToken): bool
    {
        if (
            session_status() !== PHP_SESSION_ACTIVE ||
            $submittedToken === ''
        ) {
            return false;
        }

        $sessionToken = $_SESSION['_csrf_token'] ?? '';

        if (
            !is_string($sessionToken) ||
            $sessionToken === ''
        ) {
            return false;
        }

        return hash_equals(
            $sessionToken,
            $submittedToken
        );
    }
}

/*
|--------------------------------------------------------------------------
| Legacy POST CSRF Verification
|--------------------------------------------------------------------------
*/

if (!function_exists('csrf_verify')) {
    function csrf_verify(): bool
    {
        if (
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST'
        ) {
            return false;
        }

        $submittedToken = $_POST['_csrf_token'] ?? '';

        if (!is_string($submittedToken)) {
            return false;
        }

        return verify_csrf_token($submittedToken);
    }
}

/*
|--------------------------------------------------------------------------
| Regenerate Session ID
|--------------------------------------------------------------------------
*/

if (!function_exists('regenerate_session')) {
    function regenerate_session(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException(
                'Session is not active.'
            );
        }

        if (!session_regenerate_id(true)) {
            throw new RuntimeException(
                'Failed to regenerate session ID.'
            );
        }

        $_SESSION['_csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }
}

/*
|--------------------------------------------------------------------------
| Destroy User Session
|--------------------------------------------------------------------------
*/

if (!function_exists('destroy_user_session')) {
    function destroy_user_session(): void
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
