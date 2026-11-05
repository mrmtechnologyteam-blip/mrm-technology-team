<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| MRM TECHNOLOGY TEAM
| Application Bootstrap
|--------------------------------------------------------------------------
*/

define('BASE_PATH', dirname(__DIR__));

/*
|--------------------------------------------------------------------------
| Load Environment Variables
|--------------------------------------------------------------------------
*/

$envFile = BASE_PATH . '/.env';

if (file_exists($envFile) && is_readable($envFile)) {

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value);

        if ($name === '') {
            continue;
        }

        if (
            strlen($value) >= 2 &&
            (
                ($value[0] === '"' && str_ends_with($value, '"')) ||
                ($value[0] === "'" && str_ends_with($value, "'"))
            )
        ) {
            $value = substr($value, 1, -1);
        }

        if (getenv($name) === false) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Environment Helper
|--------------------------------------------------------------------------
*/

if (!function_exists('app_env')) {

    function app_env(
        string $key,
        mixed $default = null
    ): mixed {

        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            'empty' => '',
            default => $value
        };
    }
}

/*
|--------------------------------------------------------------------------
| Application Settings
|--------------------------------------------------------------------------
*/

define('APP_NAME', app_env('APP_NAME', 'MRM Technology Team'));

define('APP_ENV', app_env('APP_ENV', 'production'));

define('APP_DEBUG', app_env('APP_DEBUG', false));

define('APP_URL', rtrim(
    (string) app_env('APP_URL', 'http://localhost'),
    '/'
));

date_default_timezone_set('Asia/Kabul');

/*
|--------------------------------------------------------------------------
| Error Reporting
|--------------------------------------------------------------------------
*/

if (APP_DEBUG === true) {

    error_reporting(E_ALL);

    ini_set('display_errors', '1');

} else {

    error_reporting(0);

    ini_set('display_errors', '0');
    ini_set('log_errors', '1');

    ini_set(
        'error_log',
        BASE_PATH . '/storage/logs/php-error.log'
    );
}

/*
|--------------------------------------------------------------------------
| Security Headers
|--------------------------------------------------------------------------
*/

if (!headers_sent()) {

    header('X-Content-Type-Options: nosniff');

    header('X-Frame-Options: SAMEORIGIN');

    header('Referrer-Policy: strict-origin-when-cross-origin');

    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

}

/*
|--------------------------------------------------------------------------
| Secure Session Configuration
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    $isHttps = (
        !empty($_SERVER['HTTPS']) &&
        strtolower((string) $_SERVER['HTTPS']) !== 'off'
    );

    ini_set('session.use_only_cookies', '1');

    ini_set('session.use_strict_mode', '1');

    session_name('MRM_SESSION');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

/*
|--------------------------------------------------------------------------
| Application Bootstrap Completed
|--------------------------------------------------------------------------
*/
