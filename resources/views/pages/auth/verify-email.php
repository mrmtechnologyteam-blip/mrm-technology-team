<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

$csrfToken = function_exists('csrf_token')
    ? csrf_token()
    : '';

$token = (string) ($token ?? '');

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

    <title>تأیید ایمیل | <?= $escape($appName) ?></title>

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
            padding: 20px;
        }

        .verification-container {
            width: 100%;
            max-width: 460px;
            padding: 35px;
            text-align: center;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 18px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, .35);
        }

        .logo {
            color: #D45CFF;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        h1 {
            font-size: 25px;
            margin-bottom: 15px;
        }

        p {
            color: #c7b8d5;
            line-height: 2;
            margin-bottom: 25px;
        }

        button {
            width: 100%;
            padding: 14px;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .back-link {
            display: block;
            margin-top: 22px;
            color: #D45CFF;
            text-decoration: none;
        }
    </style>
</head>

<body>

<main class="verification-container">

    <div class="logo">
        MRM Technology Team
    </div>

    <h1>تأیید آدرس ایمیل</h1>

    <p>
        برای فعال‌سازی حساب کاربری خود، روی دکمه زیر کلیک کنید.
    </p>

    <form action="/verify-email" method="POST">

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

        <button type="submit" class="button">
            تأیید ایمیل و فعال‌سازی حساب
        </button>

    </form>

    <a href="/login" class="back-link">
        بازگشت به صفحه ورود
    </a>

</main>

</body>
</html>
