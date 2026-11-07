<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

$user = is_array($user ?? null) ? $user : [];
$isAdmin = (bool) ($isAdmin ?? false);

function accountProfileEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$csrfToken = function_exists('csrf_token')
    ? csrf_token()
    : '';

$flashMessage = $_SESSION['flash_message'] ?? null;

unset($_SESSION['flash_message']);

$fullName = (string) ($user['full_name'] ?? '');
$email = (string) ($user['email'] ?? '');
$whatsapp = (string) ($user['whatsapp_number'] ?? '');
$country = (string) ($user['country'] ?? '');

$accountStatus = (string) ($user['account_status'] ?? '');
$emailVerified = !empty($user['email_verified_at']);

$statusLabels = [
    'active' => 'فعال',
    'pending' => 'در انتظار تأیید',
    'suspended' => 'تعلیق‌شده',
    'blocked' => 'مسدودشده',
];

$statusLabel = $statusLabels[$accountStatus] ?? 'نامشخص';

$dashboardUrl = $isAdmin
    ? '/admin/dashboard'
    : '/dashboard';

$dashboardLabel = $isAdmin
    ? 'داشبورد مدیریت'
    : 'داشبورد مشتری';

?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="theme-color" content="#05000D">

    <title>
        حساب کاربری | <?= accountProfileEscape($appName) ?>
    </title>

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #05000D;
            color: #fff;
        }

        .account-wrapper {
            width: 100%;
            max-width: 1050px;
            margin: auto;
            padding: 25px 20px 60px;
        }

        .account-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #352044;
        }

        .account-brand {
            color: #D45CFF;
            font-size: 21px;
            font-weight: bold;
        }

        .account-nav {
            display: flex;
            align-items: center;
            gap: 17px;
            flex-wrap: wrap;
        }

        .account-nav a {
            color: #e8dff0;
            text-decoration: none;
            font-size: 13px;
        }

        .account-nav a:hover {
            color: #D45CFF;
        }

        .account-title {
            margin: 35px 0 25px;
        }

        .account-title h1 {
            font-size: 26px;
            margin-bottom: 10px;
        }

        .account-title p {
            color: #bda9ca;
            line-height: 1.9;
            font-size: 14px;
        }

        .account-message {
            padding: 14px 17px;
            margin-bottom: 22px;
            border-radius: 9px;
            background: rgba(155, 32, 255, .13);
            border: 1px solid #59336e;
            color: #edc9ff;
            line-height: 1.8;
        }

        .account-card {
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .card-heading {
            margin-bottom: 23px;
            padding-bottom: 17px;
            border-bottom: 1px solid #352044;
        }

        .card-heading h2 {
            color: #D45CFF;
            font-size: 19px;
            margin-bottom: 8px;
        }

        .card-heading p {
            color: #a997b8;
            font-size: 13px;
            line-height: 1.8;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 5px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: #e8dff0;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 13px 14px;
            background: #05000D;
            border: 1px solid #49305d;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: border-color .2s;
        }

        .form-group input:focus {
            border-color: #D45CFF;
        }

        .form-group input:disabled {
            opacity: .7;
            cursor: not-allowed;
        }

        .form-group small {
            display: block;
            margin-top: 7px;
            color: #9685a5;
            font-size: 11px;
            line-height: 1.7;
        }

        .submit-button {
            display: inline-block;
            margin-top: 22px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(
                135deg,
                #9b20ff,
                #d45cff
            );
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        .submit-button:hover {
            opacity: .88;
        }

        .account-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .info-item {
            padding: 15px;
            background: #0d0718;
            border: 1px solid #352044;
            border-radius: 9px;
        }

        .info-item small {
            display: block;
            color: #a997b8;
            margin-bottom: 9px;
            font-size: 12px;
        }

        .info-item strong {
            display: block;
            color: #fff;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .status-active {
            color: #69e5ad !important;
        }

        .status-inactive {
            color: #ffadad !important;
        }

        .account-footer {
            text-align: center;
            padding-top: 25px;
            color: #82738f;
            font-size: 12px;
        }

        @media (max-width: 600px) {

            .account-wrapper {
                padding: 15px 12px 40px;
            }

            .account-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .account-nav {
                gap: 12px;
            }

            .account-title h1 {
                font-size: 22px;
            }

            .account-card {
                padding: 19px 15px;
            }

            .form-grid,
            .account-info-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: auto;
            }

            .submit-button {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<div class="account-wrapper">

    <header class="account-header">

        <div class="account-brand">
            MRM Technology Team
        </div>

        <nav class="account-nav">

            <a href="<?= accountProfileEscape($dashboardUrl) ?>">
                <?= accountProfileEscape($dashboardLabel) ?>
            </a>

            <a href="/">
                صفحه اصلی
            </a>

        </nav>

    </header>

    <main>

        <section class="account-title">

            <h1>مدیریت حساب کاربری</h1>

            <p>
                در این بخش می‌توانید اطلاعات شخصی و رمز عبور
                حساب خود را مدیریت کنید.
            </p>

        </section>

        <?php if (
            is_string($flashMessage) &&
            $flashMessage !== ''
        ): ?>

            <div class="account-message">
                <?= accountProfileEscape($flashMessage) ?>
            </div>

        <?php endif; ?>

        <!-- Account Information -->

        <section class="account-card">

            <div class="card-heading">

                <h2>اطلاعات حساب</h2>

                <p>
                    وضعیت حساب و اطلاعات ثبت‌شده شما در سامانه.
                </p>

            </div>

            <div class="account-info-grid">

                <div class="info-item">

                    <small>وضعیت حساب</small>

                    <strong class="<?= $accountStatus === 'active'
                        ? 'status-active'
                        : 'status-inactive' ?>">

                        <?= accountProfileEscape($statusLabel) ?>

                    </strong>

                </div>

                <div class="info-item">

                    <small>تأیید ایمیل</small>

                    <strong class="<?= $emailVerified
                        ? 'status-active'
                        : 'status-inactive' ?>">

                        <?= $emailVerified
                            ? 'تأیید شده'
                            : 'تأیید نشده' ?>

                    </strong>

                </div>

                <div class="info-item">

                    <small>نوع حساب</small>

                    <strong>
                        <?= $isAdmin ? 'مدیر سیستم' : 'مشتری' ?>
                    </strong>

                </div>

                <div class="info-item">

                    <small>تاریخ ایجاد حساب</small>

                    <strong>
                        <?= accountProfileEscape(
                            $user['created_at'] ?? 'نامشخص'
                        ) ?>
                    </strong>

                </div>

            </div>

        </section>

        <!-- Profile Update -->

        <section class="account-card">

            <div class="card-heading">

                <h2>ویرایش اطلاعات شخصی</h2>

                <p>
                    اطلاعات تماس و مشخصات حساب خود را به‌روزرسانی کنید.
                </p>

            </div>

            <form
                action="/account/profile"
                method="POST"
                autocomplete="on"
            >

                <input
                    type="hidden"
                    name="_csrf_token"
                    value="<?= accountProfileEscape($csrfToken) ?>"
                >

                <div class="form-grid">

                    <div class="form-group">

                        <label for="full_name">
                            نام کامل
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            value="<?= accountProfileEscape($fullName) ?>"
                            maxlength="150"
                            autocomplete="name"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="email">
                            آدرس ایمیل
                        </label>

                        <input
                            type="email"
                            id="email"
                            value="<?= accountProfileEscape($email) ?>"
                            disabled
                        >

                        <small>
                            آدرس ایمیل از این بخش قابل تغییر نیست.
                        </small>

                    </div>

                    <div class="form-group">

                        <label for="whatsapp_number">
                            شماره واتساپ
                        </label>

                        <input
                            type="tel"
                            id="whatsapp_number"
                            name="whatsapp_number"
                            value="<?= accountProfileEscape($whatsapp) ?>"
                            maxlength="30"
                            autocomplete="tel"
                            placeholder="+93..."
                        >

                    </div>

                    <div class="form-group">

                        <label for="country">
                            کشور
                        </label>

                        <input
                            type="text"
                            id="country"
                            name="country"
                            value="<?= accountProfileEscape($country) ?>"
                            maxlength="100"
                            autocomplete="country-name"
                            placeholder="افغانستان"
                        >

                    </div>

                </div>

                <button
                    type="submit"
                    class="submit-button"
                >
                    ذخیره تغییرات
                </button>

            </form>

        </section>

        <!-- Password Change -->

        <section class="account-card">

            <div class="card-heading">

                <h2>تغییر رمز عبور</h2>

                <p>
                    برای امنیت بیشتر، رمز عبور قوی انتخاب کنید.
                    رمز عبور جدید باید حداقل ۸ کاراکتر داشته باشد.
                </p>

            </div>

            <form
                action="/account/password"
                method="POST"
                autocomplete="off"
            >

                <input
                    type="hidden"
                    name="_csrf_token"
                    value="<?= accountProfileEscape($csrfToken) ?>"
                >

                <div class="form-grid">

                    <div class="form-group full-width">

                        <label for="current_password">
                            رمز عبور فعلی
                        </label>

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            autocomplete="current-password"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="new_password">
                            رمز عبور جدید
                        </label>

                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

                    </div>

                    <div class="form-group">

                        <label for="confirm_password">
                            تکرار رمز عبور جدید
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >

                    </div>

                </div>

                <button
                    type="submit"
                    class="submit-button"
                >
                    تغییر رمز عبور
                </button>

            </form>

        </section>

    </main>

    <footer class="account-footer">

        © <?= date('Y') ?>
        <?= accountProfileEscape($appName) ?>
        — تمامی حقوق محفوظ است.

    </footer>

</div>

</body>
</html>
