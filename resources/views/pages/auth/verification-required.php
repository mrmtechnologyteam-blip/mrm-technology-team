<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

$pending = $_SESSION['pending_verification'] ?? null;

$email = is_array($pending)
    ? (string) ($pending['email'] ?? '')
    : '';

$message = $_SESSION['flash_message'] ?? null;
$errors = $_SESSION['errors'] ?? [];

unset(
    $_SESSION['flash_message'],
    $_SESSION['errors']
);

$escape = static function (mixed $value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

$emailAvailable = filter_var(
    $email,
    FILTER_VALIDATE_EMAIL
) !== false;

if (!is_array($errors)) {
    $errors = [];
}
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
            background: #05000D;
        }

        .verification-container {
            width: 100%;
            max-width: 520px;
            padding: 38px 30px;
            text-align: center;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, .35);
        }

        .verification-icon {
            width: 85px;
            height: 85px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            border-radius: 50%;
            background: rgba(155, 32, 255, .15);
            border: 1px solid #9b20ff;
            color: #D45CFF;
            font-size: 38px;
        }

        .brand {
            color: #D45CFF;
            font-size: 21px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 25px;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .description {
            color: #c7b8d5;
            line-height: 2;
            margin-bottom: 22px;
        }

        .email-box {
            padding: 15px;
            margin: 20px 0;
            border-radius: 10px;
            background: #05000D;
            border: 1px solid #49305d;
            color: #D45CFF;
            direction: ltr;
            overflow-wrap: anywhere;
        }

        .notice {
            padding: 15px;
            margin-top: 22px;
            border-radius: 10px;
            background: rgba(255, 184, 77, .09);
            border: 1px solid rgba(255, 184, 77, .35);
            color: #ffd28a;
            line-height: 2;
            font-size: 13px;
            text-align: right;
        }

        .success-message {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 9px;
            background: rgba(60, 200, 130, .10);
            border: 1px solid rgba(60, 200, 130, .25);
            color: #91e6b7;
            line-height: 1.8;
        }

        .error-message {
            padding: 12px 15px;
            margin-bottom: 18px;
            border-radius: 9px;
            background: rgba(255, 70, 90, .10);
            border: 1px solid rgba(255, 70, 90, .30);
            color: #ff9ca8;
            line-height: 1.9;
            text-align: right;
        }

        .error-message ul {
            margin: 0;
            padding-right: 20px;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #D45CFF;
            text-decoration: none;
        }

        .back-link:hover {
            color: #ffffff;
        }

        @media (max-width: 480px) {
            .verification-container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

<main class="verification-container">

    <div class="brand">
        MRM Technology Team
    </div>

    <div class="verification-icon">
        ✉
    </div>

    <h1>تأیید آدرس ایمیل</h1>

    <?php if (is_string($message) && $message !== ''): ?>
        <div class="success-message">
            <?= $escape($message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <?php if (is_string($error) && $error !== ''): ?>
                        <li><?= $escape($error) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <p class="description">
        حساب کاربری شما ایجاد شده است.
        برای فعال‌سازی حساب، باید آدرس ایمیل خود را تأیید کنید.
    </p>

    <?php if ($emailAvailable): ?>

        <div class="email-box">
            <?= $escape($email) ?>
        </div>

    <?php else: ?>

        <div class="notice">
            اطلاعات ثبت‌نام در نشست فعلی پیدا نشد.
            لطفاً دوباره وارد مسیر ثبت‌نام شوید.
        </div>

    <?php endif; ?>

    <div class="notice">
        <strong>وضعیت سامانه ارسال ایمیل</strong>
        <br>
        اتصال به سرور ایمیل هنوز تنظیم نشده است.
        بنابراین در حال حاضر ایمیل تأیید به آدرس بالا ارسال نمی‌شود.
        پس از راه‌اندازی سرویس ایمیل، لینک تأیید به‌صورت خودکار ارسال خواهد شد.
    </div>

    <a href="/" class="back-link">
        بازگشت به صفحه اصلی
    </a>

</main>

</body>
</html>
