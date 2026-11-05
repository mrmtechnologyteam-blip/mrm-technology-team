<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

$csrfToken = function_exists('csrf_token')
    ? csrf_token()
    : '';

$errors = $_SESSION['errors'] ?? [];
$message = $_SESSION['flash_message'] ?? null;

unset(
    $_SESSION['errors'],
    $_SESSION['flash_message']
);

$token = isset($token) && is_string($token)
    ? $token
    : '';

$escape = static function (mixed $value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05000D">

    <title>تغییر رمز عبور | <?= $escape($appName) ?></title>

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, .35);
        }

        .auth-logo {
            text-align: center;
            color: #D45CFF;
            font-size: 23px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        h1 {
            text-align: center;
            font-size: 25px;
            margin-bottom: 12px;
        }

        .description {
            text-align: center;
            color: #c7b8d5;
            margin-bottom: 28px;
            line-height: 1.9;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 9px;
            color: #e9dff0;
        }

        input {
            width: 100%;
            padding: 14px;
            background: #05000D;
            border: 1px solid #49305d;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #D45CFF;
        }

        .password-hint {
            color: #bda9ca;
            font-size: 12px;
            margin-top: 8px;
            line-height: 1.9;
        }

        .submit-button {
            width: 100%;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 5px;
        }

        .auth-message {
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 18px;
            line-height: 1.9;
            background: rgba(212, 92, 255, .12);
            color: #E8B8FF;
        }

        .auth-error {
            color: #ff9b9b;
            font-size: 13px;
            margin-top: 5px;
        }

        .auth-links {
            text-align: center;
            margin-top: 24px;
            line-height: 2.2;
        }

        .auth-links a,
        .back-home {
            color: #D45CFF;
            text-decoration: none;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #bda9ca;
        }

        @media (max-width: 480px) {
            .auth-container {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>

<main class="auth-container">

    <div class="auth-logo">
        MRM Technology Team
    </div>

    <h1>تنظیم رمز عبور جدید</h1>

    <p class="description">
        رمز عبور جدید خود را وارد کنید و برای تأیید،
        آن را یک‌بار دیگر تکرار نمایید.
    </p>

    <?php if (is_string($message) && $message !== ''): ?>
        <div class="auth-message">
            <?= $escape($message) ?>
        </div>
    <?php endif; ?>

    <?php if (is_array($errors) && !empty($errors)): ?>
        <div class="auth-message">
            <?php foreach ($errors as $error): ?>
                <div class="auth-error">
                    <?= $escape($error) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="/reset-password" method="POST" autocomplete="on">

        <input
            type="hidden"
            name="_csrf_token"
            value="<?= $escape($csrfToken) ?>"
        >

        <input
            type="hidden"
            name="token"
            value="<?= $escape($token) ?>"
        >

        <div class="form-group">

            <label for="password">
                رمز عبور جدید
            </label>

            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                maxlength="128"
                autocomplete="new-password"
                required
            >

            <div class="password-hint">
                رمز عبور باید حداقل ۸ و حداکثر ۱۲۸ نویسه داشته باشد
                و شامل حروف بزرگ انگلیسی، حروف کوچک انگلیسی و عدد باشد.
            </div>

        </div>

        <div class="form-group">

            <label for="password_confirmation">
                تکرار رمز عبور جدید
            </label>

            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                minlength="8"
                maxlength="128"
                autocomplete="new-password"
                required
            >

        </div>

        <button type="submit" class="button submit-button">
            ذخیره رمز عبور جدید
        </button>

    </form>

    <div class="auth-links">
        <a href="/login">
            بازگشت به صفحه ورود
        </a>
    </div>

    <a href="/" class="back-home">
        بازگشت به صفحه اصلی
    </a>

</main>

</body>
</html>
