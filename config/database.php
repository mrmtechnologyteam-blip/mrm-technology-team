<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| MRM TECHNOLOGY TEAM
| Database Connection
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/bootstrap.php';

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
*/

function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = app_env('DB_HOST', 'localhost');
    $port = app_env('DB_PORT', '3306');
    $name = app_env('DB_NAME', 'mrm_technology_team');
    $user = app_env('DB_USER', 'root');
    $pass = app_env('DB_PASS', '');

    /*
    |--------------------------------------------------------------------------
    | Validate Configuration
    |--------------------------------------------------------------------------
    */

    if (
        !is_string($host) ||
        !is_string($port) ||
        !is_string($name) ||
        !is_string($user) ||
        !is_string($pass) ||
        !ctype_digit($port)
    ) {
        throw new RuntimeException(
            'Invalid database configuration.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDO Connection
    |--------------------------------------------------------------------------
    */

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $name
    );

    try {

        $connection = new PDO(
            $dsn,
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                PDO::ATTR_DEFAULT_FETCH_MODE =>
                    PDO::FETCH_ASSOC,

                PDO::ATTR_EMULATE_PREPARES => false,

                PDO::ATTR_PERSISTENT => false,

                PDO::MYSQL_ATTR_INIT_COMMAND =>
                    "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );

        return $connection;

    } catch (PDOException $exception) {

        error_log(
            '[MRM DATABASE ERROR] ' .
            $exception->getMessage()
        );

        throw new RuntimeException(
            'Database connection failed.'
        );
    }
}
