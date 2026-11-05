<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

$csrfToken = function_exists('csrf_token')
    ? csrf_token()
    : '';

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

unset($_SESSION['errors'], $_SESSION['old']);

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

    <title>ثبت‌نام | <?= $escape($appName) ?></title>

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
            max-width: 500px;
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

        h1 {
            text-align: center;
            font-size: 25px;
            margin-bottom: 10px;
        }

        .description {
            text-align: center;
            color: #c7b8d5;
            margin-bottom: 28px;
            line-height: 1.8;
        }

        .form-group {
            margin-bottom: 18px;
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

        .submit-button {
            width: 100%;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 8px;
        }

        .auth-message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            line-height: 1.8;
            background: rgba(255, 70, 100, .10);
            color: #ffaaaa;
        }

        .auth-error {
            font-size: 13px;
            margin-top: 5px;
        }

        .password-hint {
            color: #bda9ca;
            font-size: 12px;
            margin-top: 7px;
            line-height: 1.8;
        }

        .auth-links {
            text-align: center;
            margin-top: 24px;
            color: #c7b8d5;
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

    <h1>ایجاد حساب کاربری</h1>

    <p class="description">
        برای ثبت درخواست پروژه و استفاده از خدمات ما حساب بسازید.
    </p>

    <?php if (is_array($errors) && !empty($errors)): ?>
        <div class="auth-message">
            <?php foreach ($errors as $error): ?>
                <div class="auth-error">
                    <?= $escape($error) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="/register" method="POST" autocomplete="on">

        <input
            type="hidden"
            name="_csrf_token"
            value="<?= $escape($csrfToken) ?>"
        >

        <div class="form-group">
            <label for="full_name">نام کامل</label>

            <input
                type="text"
                id="full_name"
                name="full_name"
                value="<?= $escape($old['full_name'] ?? '') ?>"
                maxlength="150"
                autocomplete="name"
                required
            >
        </div>

        <div class="form-group">
            <label for="email">آدرس ایمیل</label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= $escape($old['email'] ?? '') ?>"
                maxlength="190"
                autocomplete="email"
                placeholder="example@email.com"
                required
            >
        </div>

        <div class="form-group">
            <label for="whatsapp_number">شماره واتساپ</label>

            <input
                type="tel"
                id="whatsapp_number"
                name="whatsapp_number"
                value="<?= $escape($old['whatsapp_number'] ?? '') ?>"
                maxlength="30"
                autocomplete="tel"
                placeholder="+93..."
                required
            >
        </div>

        <div class="form-group">
            <label for="country">کشور</label>

            <input
                type="text"
                id="country"
                name="country"
                value="<?= $escape($old['country'] ?? '') ?>"
                maxlength="100"
                autocomplete="country-name"
                placeholder="نام کشور"
            >
        </div>

        <div class="form-group">
            <label for="password">رمز عبور</label>

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
                حداقل ۸ کاراکتر شامل حروف بزرگ، حروف کوچک و عدد.
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">
                تکرار رمز عبور
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
            ایجاد حساب کاربری
        </button>

    </form>

    <div class="auth-links">
        قبلاً حساب ساخته‌اید؟
        <a href="/login">وارد شوید</a>
    </div>

    <a href="/" class="back-home">
        بازگشت به صفحه اصلی
    </a>

</main>

</body>
</html>
