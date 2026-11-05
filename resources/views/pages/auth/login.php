<?php

declare(strict_types=1);

$appName = defined('APP_NAME') ? APP_NAME : 'MRM Technology Team';

$csrfToken = function_exists('csrf_token')
    ? csrf_token()
    : '';

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
$message = $_SESSION['flash_message'] ?? null;

unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['flash_message']);

function loginEscape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05000D">

    <title>ورود کاربران | <?= loginEscape($appName) ?></title>

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
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
            margin-bottom: 12px;
        }

        .auth-container h1 {
            text-align: center;
            font-size: 25px;
            margin-bottom: 10px;
        }

        .auth-description {
            text-align: center;
            color: #c7b8d5;
            margin-bottom: 28px;
            line-height: 1.8;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: #e9dff0;
        }

        .form-group input {
            width: 100%;
            padding: 14px;
            background: #05000D;
            border: 1px solid #49305d;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #D45CFF;
        }

        .submit-button {
            width: 100%;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .auth-message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            line-height: 1.7;
            background: rgba(212, 92, 255, .12);
            color: #E8B8FF;
        }

        .auth-error {
            color: #ff9b9b;
            font-size: 13px;
            margin-top: 7px;
        }

        .auth-links {
            text-align: center;
            margin-top: 24px;
            color: #c7b8d5;
            line-height: 2.2;
        }

        .auth-links a {
            color: #D45CFF;
            text-decoration: none;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #bda9ca;
            text-decoration: none;
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

    <h1>ورود به حساب کاربری</h1>

    <p class="auth-description">
        برای مدیریت پروژه‌ها و پیگیری درخواست‌های خود وارد شوید.
    </p>

    <?php if (is_string($message) && $message !== ''): ?>
        <div class="auth-message">
            <?= loginEscape($message) ?>
        </div>
    <?php endif; ?>

    <?php if (is_array($errors) && !empty($errors)): ?>
        <div class="auth-message">
            <?php foreach ($errors as $error): ?>
                <div class="auth-error">
                    <?= loginEscape($error) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="/login" method="POST" autocomplete="on">

        <input
            type="hidden"
            name="_csrf_token"
            value="<?= loginEscape($csrfToken) ?>"
        >

        <div class="form-group">
            <label for="email">آدرس ایمیل</label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= loginEscape($old['email'] ?? '') ?>"
                placeholder="example@email.com"
                maxlength="190"
                autocomplete="email"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">رمز عبور</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="رمز عبور خود را وارد کنید"
                autocomplete="current-password"
                required
            >
        </div>

        <button type="submit" class="button submit-button">
            ورود به حساب
        </button>

    </form>

    <div class="auth-links">
        <a href="/forgot-password">رمز عبور خود را فراموش کرده‌اید؟</a>
        <br>
        حساب ندارید؟
        <a href="/register">ثبت‌نام کنید</a>
    </div>

    <a href="/" class="back-home">
        بازگشت به صفحه اصلی
    </a>

</main>

</body>
</html>
