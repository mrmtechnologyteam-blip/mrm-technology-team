<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

$settings = is_array($settings ?? null) ? $settings : [];
$groupedSettings = is_array($groupedSettings ?? null)
    ? $groupedSettings
    : [];

$totalSettings = (int) ($totalSettings ?? count($settings));

$escape = static function (mixed $value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

$csrfToken = function_exists('csrf_token')
    ? csrf_token()
    : '';

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

$groupLabels = [
    'general' => 'تنظیمات عمومی',
    'contact' => 'اطلاعات تماس',
    'email' => 'تنظیمات ایمیل',
    'security' => 'امنیت سیستم',
    'appearance' => 'ظاهر وب‌سایت',
    'social' => 'شبکه‌های اجتماعی',
];

$typeLabels = [
    'text' => 'متن',
    'textarea' => 'متن چندخطی',
    'boolean' => 'فعال / غیرفعال',
    'number' => 'عدد',
    'email' => 'ایمیل',
    'url' => 'آدرس اینترنتی',
    'json' => 'داده JSON',
];

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05000D">

    <title>
        تنظیمات سیستم | <?= $escape($appName) ?>
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
            font-family: Arial, sans-serif;
        }

        .settings-wrapper {
            width: 100%;
            max-width: 1250px;
            margin: auto;
            padding: 22px 20px 55px;
        }

        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
            padding: 15px 0 22px;
            border-bottom: 1px solid #352044;
        }

        .brand {
            color: #D45CFF;
            font-size: 21px;
            font-weight: bold;
        }

        .brand small {
            display: block;
            color: #a995b8;
            font-size: 11px;
            font-weight: normal;
            margin-top: 7px;
        }

        .header-nav {
            display: flex;
            gap: 17px;
            align-items: center;
            flex-wrap: wrap;
        }

        .header-nav a {
            color: #e8dff0;
            text-decoration: none;
            font-size: 13px;
        }

        .header-nav a:hover {
            color: #D45CFF;
        }

        .page-heading {
            margin: 28px 0 22px;
            padding: 26px;
            background: linear-gradient(120deg, #25103b, #140D23);
            border: 1px solid #49305d;
            border-radius: 14px;
        }

        .page-heading h1 {
            margin: 0 0 12px;
            font-size: 25px;
        }

        .page-heading h1 span {
            color: #D45CFF;
        }

        .page-heading p {
            color: #c7b8d5;
            font-size: 13px;
            line-height: 2;
            margin: 0;
        }

        .notice {
            padding: 14px 17px;
            margin-bottom: 20px;
            border: 1px solid #49305d;
            border-radius: 9px;
            background: rgba(155, 32, 255, .12);
            color: #e8b8ff;
            font-size: 13px;
            line-height: 1.9;
        }

        .summary-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            padding: 19px 22px;
            margin-bottom: 24px;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 12px;
        }

        .summary-card span {
            color: #bda9ca;
            font-size: 13px;
        }

        .summary-card strong {
            color: #D45CFF;
            font-size: 25px;
        }

        .settings-group {
            margin-bottom: 24px;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 13px;
            overflow: hidden;
        }

        .group-heading {
            padding: 20px 22px;
            background: #1b1030;
            border-bottom: 1px solid #352044;
        }

        .group-heading h2 {
            margin: 0 0 8px;
            color: #D45CFF;
            font-size: 18px;
        }

        .group-heading p {
            margin: 0;
            color: #9b8ca8;
            font-size: 12px;
            line-height: 1.9;
        }

        .settings-list {
            padding: 5px 22px;
        }

        .setting-item {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) minmax(250px, 1.5fr);
            gap: 25px;
            align-items: start;
            padding: 22px 0;
            border-bottom: 1px solid #352044;
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .setting-info label {
            display: block;
            color: #eee;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 9px;
        }

        .setting-info small {
            display: block;
            color: #9584a2;
            font-size: 11px;
            line-height: 1.9;
            overflow-wrap: anywhere;
        }

        .setting-key {
            direction: ltr;
            text-align: right;
            color: #b68ad1 !important;
            margin-top: 7px;
        }

        .form-control {
            display: block;
            width: 100%;
            min-height: 43px;
            padding: 11px 13px;
            background: #0D0718;
            color: #fff;
            border: 1px solid #49305d;
            border-radius: 7px;
            outline: none;
            font-family: inherit;
            font-size: 12px;
        }

        .form-control:focus {
            border-color: #D45CFF;
            box-shadow: 0 0 0 2px rgba(212, 92, 255, .1);
        }

        textarea.form-control {
            min-height: 105px;
            resize: vertical;
            line-height: 1.9;
        }

        .json-control {
            direction: ltr;
            text-align: left;
            font-family: monospace;
        }

        .form-control option {
            background: #140D23;
            color: #fff;
        }

        .setting-type {
            display: inline-block;
            margin-top: 9px;
            padding: 5px 9px;
            background: rgba(155, 32, 255, .12);
            border: 1px solid #49305d;
            border-radius: 20px;
            color: #dcb7ff;
            font-size: 10px;
        }

        .toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 43px;
        }

        .toggle-wrapper input {
            width: 19px;
            height: 19px;
            accent-color: #9b20ff;
            cursor: pointer;
        }

        .toggle-wrapper span {
            color: #e8dff0;
            font-size: 12px;
        }

        .empty-state {
            padding: 45px 20px;
            text-align: center;
            color: #a995b8;
            line-height: 2.2;
            font-size: 13px;
        }

        .empty-state strong {
            display: block;
            color: #D45CFF;
            font-size: 17px;
            margin-bottom: 12px;
        }

        .empty-state small {
            display: block;
            margin-top: 10px;
            color: #82738f;
            font-size: 11px;
        }

        .action-bar {
            position: sticky;
            bottom: 10px;
            z-index: 5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 25px;
            padding: 17px;
            background: rgba(20, 13, 35, .97);
            border: 1px solid #604374;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .3);
        }

        .action-bar p {
            margin: 0;
            color: #a995b8;
            font-size: 11px;
            line-height: 1.8;
        }

        .button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-height: 43px;
            padding: 0 21px;
            border: 1px solid transparent;
            border-radius: 7px;
            cursor: pointer;
            font-family: inherit;
            font-size: 12px;
            text-decoration: none;
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

        .button-secondary:hover {
            border-color: #D45CFF;
            color: #fff;
        }

        .footer {
            padding-top: 30px;
            text-align: center;
            color: #82738f;
            font-size: 11px;
        }

        @media (max-width: 700px) {
            .settings-wrapper {
                padding: 13px 11px 35px;
            }

            .settings-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-heading {
                padding: 21px 16px;
            }

            .page-heading h1 {
                font-size: 21px;
            }

            .setting-item {
                grid-template-columns: 1fr;
                gap: 13px;
            }

            .settings-list {
                padding: 5px 15px;
            }

            .group-heading {
                padding: 17px 15px;
            }

            .action-bar {
                align-items: stretch;
                flex-direction: column;
            }

            .action-bar .button {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="settings-wrapper">

    <header class="settings-header">

        <div class="brand">
            MRM Technology Team
            <small>مدیریت تنظیمات سیستم</small>
        </div>

        <nav class="header-nav">
            <a href="/admin/dashboard">داشبورد مدیریت</a>
            <a href="/">صفحه اصلی</a>
        </nav>

    </header>

    <main>

        <section class="page-heading">

            <h1>
                تنظیمات
                <span>سیستم</span>
            </h1>

            <p>
                از این بخش می‌توانید تنظیمات عمومی و پیکربندی‌های
                سامانه MRM Technology Team را مدیریت کنید.
                تغییرات فقط پس از ذخیره‌سازی ثبت خواهند شد.
            </p>

        </section>

        <?php if (is_string($flashMessage) && $flashMessage !== ''): ?>
            <div class="notice">
                <?= $escape($flashMessage) ?>
            </div>
        <?php endif; ?>

        <section class="summary-card">
            <span>تعداد تنظیمات تعریف‌شده در سیستم</span>
            <strong><?= number_format($totalSettings) ?></strong>
        </section>

        <?php if (empty($settings)): ?>

            <section class="settings-group">

                <div class="empty-state">

                    <strong>هنوز تنظیماتی تعریف نشده است.</strong>

                    در حال حاضر هیچ تنظیمی برای نمایش وجود ندارد.
                    پس از تعریف تنظیمات پیش‌فرض در مرحله بعد،
                    گزینه‌های قابل مدیریت در این صفحه نمایش داده خواهند شد.

                    <small>
                        این وضعیت در فاز اول طبیعی است؛
                        اجرای Migration و مقداردهی دیتابیس در فاز دوم انجام می‌شود.
                    </small>

                </div>

            </section>

        <?php else: ?>

            <form
                action="/admin/settings/update"
                method="POST"
                id="settingsForm"
            >

                <input
                    type="hidden"
                    name="_csrf_token"
                    value="<?= $escape($csrfToken) ?>"
                >

                <?php foreach ($groupedSettings as $groupKey => $groupSettings): ?>

                    <?php
                    $groupTitle = $groupLabels[$groupKey]
                        ?? $groupKey;
                    ?>

                    <section class="settings-group">

                        <div class="group-heading">

                            <h2>
                                <?= $escape($groupTitle) ?>
                            </h2>

                            <p>
                                تنظیمات مربوط به این بخش از سامانه.
                            </p>

                        </div>

                        <div class="settings-list">

                            <?php foreach ($groupSettings as $setting): ?>

                                <?php
                                $key = (string) ($setting['setting_key'] ?? '');
                                $type = (string) ($setting['setting_type'] ?? 'text');
                                $label = (string) ($setting['setting_label'] ?? $key);
                                $description = (string) ($setting['description'] ?? '');
                                $value = $setting['setting_value'] ?? '';

                                $fieldName = 'settings[' . $key . ']';

                                $typeLabel = $typeLabels[$type] ?? 'متن';
                                ?>

                                <div class="setting-item">

                                    <div class="setting-info">

                                        <label for="setting_<?= $escape($key) ?>">
                                            <?= $escape($label) ?>
                                        </label>

                                        <?php if ($description !== ''): ?>
                                            <small>
                                                <?= $escape($description) ?>
                                            </small>
                                        <?php endif; ?>

                                        <small class="setting-key">
                                            <?= $escape($key) ?>
                                        </small>

                                        <span class="setting-type">
                                            <?= $escape($typeLabel) ?>
                                        </span>

                                    </div>

                                    <div class="setting-input">

                                        <?php if ($type === 'boolean'): ?>

                                            <label class="toggle-wrapper">

                                                <input
                                                    type="checkbox"
                                                    id="setting_<?= $escape($key) ?>"
                                                    name="<?= $escape($fieldName) ?>"
                                                    value="1"
                                                    <?= in_array(
                                                        strtolower((string) $value),
                                                        ['1', 'true', 'yes', 'on'],
                                                        true
                                                    ) ? 'checked' : '' ?>
                                                >

                                                <span>
                                                    فعال باشد
                                                </span>

                                            </label>

                                        <?php elseif ($type === 'textarea'): ?>

                                            <textarea
                                                class="form-control"
                                                id="setting_<?= $escape($key) ?>"
                                                name="<?= $escape($fieldName) ?>"
                                            ><?= $escape($value) ?></textarea>

                                        <?php elseif ($type === 'json'): ?>

                                            <textarea
                                                class="form-control json-control"
                                                id="setting_<?= $escape($key) ?>"
                                                name="<?= $escape($fieldName) ?>"
                                                spellcheck="false"
                                            ><?= $escape($value) ?></textarea>

                                        <?php else: ?>

                                            <?php
                                            $inputType = match ($type) {
                                                'email' => 'email',
                                                'url' => 'url',
                                                'number' => 'number',
                                                default => 'text',
                                            };
                                            ?>

                                            <input
                                                type="<?= $inputType ?>"
                                                class="form-control"
                                                id="setting_<?= $escape($key) ?>"
                                                name="<?= $escape($fieldName) ?>"
                                                value="<?= $escape($value) ?>"
                                                <?= $type === 'number' ? 'step="any"' : '' ?>
                                            >

                                        <?php endif; ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </section>

                <?php endforeach; ?>

                <div class="action-bar">

                    <p>
                        پس از تغییر تنظیمات، برای ثبت آن‌ها
                        دکمه ذخیره تنظیمات را انتخاب کنید.
                    </p>

                    <button
                        type="submit"
                        class="button button-primary"
                    >
                        ذخیره تنظیمات
                    </button>

                </div>

            </form>

        <?php endif; ?>

    </main>

    <footer class="footer">
        © <?= date('Y') ?> MRM Technology Team — تمامی حقوق محفوظ است.
    </footer>

</div>

</body>
</html>
