<?php

declare(strict_types=1);

$message = is_array($message ?? null) ? $message : [];

function adminContactShowEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$messageId = (int) ($message['id'] ?? 0);

$statuses = [
    'new' => 'جدید',
    'in_progress' => 'در حال بررسی',
    'replied' => 'پاسخ داده‌شده',
    'closed' => 'بسته‌شده',
];

$currentStatus = (string) ($message['status'] ?? 'new');

if (!isset($statuses[$currentStatus])) {
    $currentStatus = 'new';
}

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

$csrfToken = function_exists('csrf_token')
    ? csrf_token()
    : '';

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
        جزئیات پیام | <?= adminContactShowEscape($appName) ?>
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #05000D;
            color: #fff;
            font-family: Tahoma, Arial, sans-serif;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

        .container {
            max-width: 1100px;
            width: 100%;
            margin: auto;
            padding: 22px 18px 50px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            padding-bottom: 20px;
            border-bottom: 1px solid #352044;
        }

        .brand {
            color: #D45CFF;
            font-size: 20px;
            font-weight: bold;
        }

        .brand small {
            display: block;
            color: #a995b8;
            font-size: 11px;
            font-weight: normal;
            margin-top: 7px;
        }

        .navigation {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .navigation a {
            color: #e8dff0;
            font-size: 13px;
        }

        .navigation a:hover {
            color: #D45CFF;
        }

        .page-heading {
            margin: 28px 0;
            padding: 24px;
            background: linear-gradient(120deg, #25103b, #140D23);
            border: 1px solid #49305d;
            border-radius: 14px;
        }

        .page-heading h1 {
            margin: 0 0 10px;
            font-size: 23px;
        }

        .page-heading h1 span {
            color: #D45CFF;
        }

        .page-heading p {
            margin: 0;
            color: #bda9ca;
            font-size: 13px;
            line-height: 2;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .card {
            background: #140D23;
            border: 1px solid #49305d;
            border-radius: 13px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .card-title {
            color: #D45CFF;
            font-size: 17px;
            margin: 0 0 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #352044;
        }

        .info-row {
            display: grid;
            grid-template-columns: 125px 1fr;
            gap: 12px;
            padding: 13px 0;
            border-bottom: 1px solid #30203d;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #a995b8;
            font-size: 12px;
        }

        .info-value {
            color: #fff;
            font-size: 13px;
            line-height: 1.9;
            overflow-wrap: anywhere;
        }

        .message-body {
            background: #0D0718;
            border: 1px solid #49305d;
            border-radius: 9px;
            padding: 18px;
            color: #e8dff0;
            font-size: 13px;
            line-height: 2.2;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            min-height: 150px;
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-group label {
            display: block;
            color: #c7b8d5;
            font-size: 12px;
            margin-bottom: 9px;
        }

        .form-control {
            width: 100%;
            min-height: 43px;
            padding: 11px 12px;
            background: #0D0718;
            color: #fff;
            border: 1px solid #49305d;
            border-radius: 7px;
            outline: none;
            font-family: inherit;
            font-size: 13px;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
            line-height: 2;
        }

        .form-control:focus {
            border-color: #D45CFF;
        }

        .form-control option {
            background: #140D23;
        }

        .button {
            min-height: 42px;
            padding: 10px 17px;
            border-radius: 7px;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-family: inherit;
            font-size: 12px;
        }

        .button-primary {
            background: #9b20ff;
            color: #fff;
        }

        .button-primary:hover {
            background: #b34aff;
        }

        .button-secondary {
            background: transparent;
            border-color: #604374;
            color: #d7c4e5;
        }

        .button-danger {
            background: #6e1c31;
            color: #fff;
            border-color: #a63a55;
        }

        .button-danger:hover {
            background: #92233f;
        }

        .status-badge {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            background: #49305d;
            color: #e5c1ff;
            font-size: 12px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .help-text {
            color: #9685a5;
            font-size: 11px;
            line-height: 1.9;
            margin-top: 10px;
        }

        .contact-link {
            color: #D45CFF;
            overflow-wrap: anywhere;
        }

        .contact-link:hover {
            text-decoration: underline;
        }

        .danger-card {
            border-color: #713044;
        }

        .danger-card .card-title {
            color: #ff829c;
        }

        .footer {
            text-align: center;
            color: #82738f;
            font-size: 12px;
            padding-top: 25px;
        }

        @media (max-width: 750px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 14px 11px 35px;
            }

            .page-heading {
                padding: 19px 15px;
            }

            .page-heading h1 {
                font-size: 20px;
            }

            .info-row {
                grid-template-columns: 105px 1fr;
            }

            .button {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <header class="header">

        <div class="brand">
            MRM Technology Team
            <small>مدیریت پیام‌های تماس</small>
        </div>

        <nav class="navigation">
            <a href="/admin/dashboard">داشبورد مدیریت</a>
            <a href="/admin/contact-messages">صندوق پیام‌ها</a>
            <a href="/">صفحه اصلی</a>
        </nav>

    </header>

    <main>

        <section class="page-heading">

            <h1>
                جزئیات <span>پیام مشتری</span>
            </h1>

            <p>
                اطلاعات پیام دریافتی را مشاهده و وضعیت رسیدگی به آن را مدیریت کنید.
            </p>

        </section>

        <?php if ($messageId <= 0): ?>

            <section class="card">
                <p>اطلاعات پیام معتبر نیست.</p>
                <a
                    href="/admin/contact-messages"
                    class="button button-secondary"
                >
                    بازگشت به صندوق پیام‌ها
                </a>
            </section>

        <?php else: ?>

            <div class="content-grid">

                <div>

                    <section class="card">

                        <h2 class="card-title">
                            اطلاعات فرستنده
                        </h2>

                        <div class="info-row">
                            <div class="info-label">شماره پیام</div>
                            <div class="info-value">
                                #<?= $messageId ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">نام کامل</div>
                            <div class="info-value">
                                <?= adminContactShowEscape($message['full_name'] ?? '-') ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">ایمیل</div>
                            <div class="info-value">
                                <a
                                    class="contact-link"
                                    href="mailto:<?= adminContactShowEscape($message['email'] ?? '') ?>"
                                >
                                    <?= adminContactShowEscape($message['email'] ?? '-') ?>
                                </a>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">واتساپ</div>
                            <div class="info-value">
                                <?php
                                $whatsapp = trim(
                                    (string) ($message['whatsapp_number'] ?? '')
                                );
                                ?>

                                <?php if ($whatsapp !== ''): ?>

                                    <a
                                        class="contact-link"
                                        href="https://wa.me/<?= preg_replace('/\D+/', '', $whatsapp) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <?= adminContactShowEscape($whatsapp) ?>
                                    </a>

                                <?php else: ?>

                                    ثبت نشده است

                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">تاریخ دریافت</div>
                            <div class="info-value">
                                <?= adminContactShowEscape($message['created_at'] ?? '-') ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">آخرین تغییر</div>
                            <div class="info-value">
                                <?= adminContactShowEscape($message['updated_at'] ?? '-') ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">مسئول رسیدگی</div>
                            <div class="info-value">
                                <?= adminContactShowEscape($message['admin_name'] ?? 'تعیین نشده') ?>
                            </div>
                        </div>

                    </section>

                    <section class="card">

                        <h2 class="card-title">
                            موضوع پیام
                        </h2>

                        <div class="info-value" style="font-weight:bold;margin-bottom:20px;">
                            <?= adminContactShowEscape($message['subject'] ?? '-') ?>
                        </div>

                        <h2 class="card-title">
                            متن پیام
                        </h2>

                        <div class="message-body"><?= adminContactShowEscape($message['message'] ?? '') ?></div>

                    </section>

                </div>

                <aside>

                    <section class="card">

                        <h2 class="card-title">
                            وضعیت فعلی
                        </h2>

                        <div style="margin-bottom:20px;">
                            <span class="status-badge">
                                <?= adminContactShowEscape($statuses[$currentStatus]) ?>
                            </span>
                        </div>

                        <form
                            action="/admin/contact-messages/<?= $messageId ?>/status"
                            method="POST"
                        >

                            <input
                                type="hidden"
                                name="_csrf_token"
                                value="<?= adminContactShowEscape($csrfToken) ?>"
                            >

                            <div class="form-group">

                                <label for="status">
                                    تغییر وضعیت پیام
                                </label>

                                <select
                                    name="status"
                                    id="status"
                                    class="form-control"
                                    required
                                >

                                    <?php foreach ($statuses as $key => $label): ?>

                                        <option
                                            value="<?= adminContactShowEscape($key) ?>"
                                            <?= $currentStatus === $key ? 'selected' : '' ?>
                                        >
                                            <?= adminContactShowEscape($label) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <button
                                type="submit"
                                class="button button-primary"
                            >
                                ذخیره وضعیت
                            </button>

                        </form>

                    </section>

                    <section class="card">

                        <h2 class="card-title">
                            یادداشت داخلی مدیریت
                        </h2>

                        <form
                            action="/admin/contact-messages/<?= $messageId ?>/note"
                            method="POST"
                        >

                            <input
                                type="hidden"
                                name="_csrf_token"
                                value="<?= adminContactShowEscape($csrfToken) ?>"
                            >

                            <div class="form-group">

                                <label for="admin_note">
                                    یادداشت مدیر
                                </label>

                                <textarea
                                    name="admin_note"
                                    id="admin_note"
                                    class="form-control"
                                    maxlength="10000"
                                    placeholder="یادداشت‌های داخلی درباره پیگیری این پیام..."
                                ><?= adminContactShowEscape($message['admin_note'] ?? '') ?></textarea>

                            </div>

                            <button
                                type="submit"
                                class="button button-primary"
                            >
                                ذخیره یادداشت
                            </button>

                            <p class="help-text">
                                این یادداشت داخلی است و برای فرستنده پیام نمایش داده نمی‌شود.
                            </p>

                        </form>

                    </section>

                    <section class="card danger-card">

                        <h2 class="card-title">
                            حذف پیام
                        </h2>

                        <p class="help-text">
                            با حذف این پیام، اطلاعات آن از صندوق پیام‌ها پاک خواهد شد.
                            این عملیات قابل بازگشت نیست.
                        </p>

                        <form
                            action="/admin/contact-messages/<?= $messageId ?>/delete"
                            method="POST"
                            onsubmit="return confirm('آیا از حذف دائمی این پیام اطمینان دارید؟');"
                        >

                            <input
                                type="hidden"
                                name="_csrf_token"
                                value="<?= adminContactShowEscape($csrfToken) ?>"
                            >

                            <button
                                type="submit"
                                class="button button-danger"
                            >
                                حذف دائمی پیام
                            </button>

                        </form>

                    </section>

                    <a
                        href="/admin/contact-messages"
                        class="button button-secondary"
                        style="width:100%;"
                    >
                        بازگشت به صندوق پیام‌ها
                    </a>

                </aside>

            </div>

        <?php endif; ?>

    </main>

    <footer class="footer">
        © <?= date('Y') ?> MRM Technology Team — تمامی حقوق محفوظ است.
    </footer>

</div>

</body>
</html>
