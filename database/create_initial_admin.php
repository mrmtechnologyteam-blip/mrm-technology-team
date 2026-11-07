<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| MRM TECHNOLOGY TEAM
| Secure Initial Administrator Creator
|--------------------------------------------------------------------------
| CLI ONLY - Never execute through a browser.
|--------------------------------------------------------------------------
*/

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Access denied.\n");
}

require_once dirname(__DIR__) . '/config/init.php';

use PDO;
use Throwable;

function adminPrompt(string $label): string
{
    echo $label;
    return trim((string) fgets(STDIN));
}

function adminHiddenPrompt(string $label): string
{
    echo $label;

    $hidden = false;

    if (DIRECTORY_SEPARATOR === '/') {
        system('stty -echo');
        $hidden = true;
    }

    try {
        $value = trim((string) fgets(STDIN));
    } finally {
        if ($hidden) {
            system('stty echo');
            echo PHP_EOL;
        }
    }

    return $value;
}

function adminLength(string $value): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : strlen($value);
}

function createInitialAdmin(): void
{
    echo PHP_EOL;
    echo "========================================\n";
    echo " MRM TECHNOLOGY TEAM\n";
    echo " INITIAL ADMINISTRATOR CREATION\n";
    echo "========================================\n\n";

    $db = db();

    /*
    |--------------------------------------------------------------------------
    | Prevent Multiple Initial Admin Creation
    |--------------------------------------------------------------------------
    */

    $lock = $db->query(
        "SELECT GET_LOCK(
            'mrm_initial_admin_creation',
            10
        )"
    )->fetchColumn();

    if ((int) $lock !== 1) {
        throw new RuntimeException(
            'امکان دریافت قفل ایجاد مدیر وجود ندارد.'
        );
    }

    try {
        $adminCount = (int) $db->query(
            "SELECT COUNT(*)
             FROM users
             WHERE role = 'admin'"
        )->fetchColumn();

        if ($adminCount > 0) {
            throw new RuntimeException(
                'یک مدیر سیستم از قبل وجود دارد. عملیات متوقف شد.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Collect Administrator Information
        |--------------------------------------------------------------------------
        */

        $fullName = adminPrompt(
            'نام کامل مدیر: '
        );

        if (
            adminLength($fullName) < 3 ||
            adminLength($fullName) > 150
        ) {
            throw new RuntimeException(
                'نام باید بین ۳ تا ۱۵۰ کاراکتر باشد.'
            );
        }

        $email = strtolower(
            adminPrompt('ایمیل مدیر: ')
        );

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 190
        ) {
            throw new RuntimeException(
                'آدرس ایمیل معتبر نیست.'
            );
        }

        $whatsapp = adminPrompt(
            'شماره واتساپ مدیر: '
        );

        if (
            $whatsapp === '' ||
            strlen($whatsapp) > 30
        ) {
            throw new RuntimeException(
                'شماره واتساپ معتبر نیست.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Check Existing Email
        |--------------------------------------------------------------------------
        */

        $check = $db->prepare(
            "SELECT id
             FROM users
             WHERE email = :email
             LIMIT 1"
        );

        $check->execute([
            'email' => $email
        ]);

        if ($check->fetchColumn()) {
            throw new RuntimeException(
                'این ایمیل قبلاً در سیستم ثبت شده است.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Secure Password Input
        |--------------------------------------------------------------------------
        */

        $password = adminHiddenPrompt(
            'رمز عبور مدیر: '
        );

        $confirmPassword = adminHiddenPrompt(
            'تکرار رمز عبور: '
        );

        if ($password !== $confirmPassword) {
            throw new RuntimeException(
                'رمزهای عبور یکسان نیستند.'
            );
        }

        if (
            strlen($password) < 12 ||
            strlen($password) > 128 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)
        ) {
            throw new RuntimeException(
                'رمز باید حداقل ۱۲ کاراکتر و شامل حروف بزرگ، کوچک و عدد باشد.'
            );
        }

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        if ($passwordHash === false) {
            throw new RuntimeException(
                'ساخت هش رمز عبور ناموفق بود.'
            );
        }

        unset($password, $confirmPassword);

        /*
        |--------------------------------------------------------------------------
        | Create Administrator
        |--------------------------------------------------------------------------
        */

        $db->beginTransaction();

        $insert = $db->prepare(
            "INSERT INTO users (
                full_name,
                email,
                password_hash,
                whatsapp_number,
                country,
                role,
                email_verified_at,
                account_status
            ) VALUES (
                :full_name,
                :email,
                :password_hash,
                :whatsapp_number,
                :country,
                'admin',
                CURRENT_TIMESTAMP,
                'active'
            )"
        );

        $insert->execute([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => $passwordHash,
            'whatsapp_number' => $whatsapp,
            'country' => 'Afghanistan'
        ]);

        $adminId = (int) $db->lastInsertId();

        $db->commit();

        echo PHP_EOL;
        echo "========================================\n";
        echo " ADMIN CREATED SUCCESSFULLY\n";
        echo "========================================\n";
        echo "Admin ID: {$adminId}\n";
        echo "Name: {$fullName}\n";
        echo "Email: {$email}\n";
        echo "Role: Administrator\n";
        echo "Status: Active\n";
        echo "Email: Verified\n";
        echo "========================================\n";
        echo "رمز عبور نمایش داده یا ذخیره نشد.\n";

    } catch (Throwable $exception) {

        if ($db->inTransaction()) {
            $db->rollBack();
        }

        throw $exception;

    } finally {

        $db->query(
            "SELECT RELEASE_LOCK(
                'mrm_initial_admin_creation'
            )"
        );
    }
}

try {
    createInitialAdmin();

} catch (Throwable $exception) {

    fwrite(
        STDERR,
        PHP_EOL .
        "ERROR: " .
        $exception->getMessage() .
        PHP_EOL
    );

    exit(1);
}
