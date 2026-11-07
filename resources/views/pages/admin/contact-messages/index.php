<?php

declare(strict_types=1);

$messages = is_array($messages ?? null) ? $messages : [];
$filters = is_array($filters ?? null) ? $filters : [];
$statistics = is_array($statistics ?? null) ? $statistics : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];

function adminContactInboxEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$search = (string) ($filters['search'] ?? '');
$selectedStatus = (string) ($filters['status'] ?? '');

$currentPage = max(1, (int) ($pagination['current_page'] ?? 1));
$totalPages = max(1, (int) ($pagination['total_pages'] ?? 1));
$totalMessages = (int) ($pagination['total_messages'] ?? 0);

$statuses = [
    'new' => [
        'label' => 'جدید',
        'class' => 'new',
    ],
    'in_progress' => [
        'label' => 'در حال بررسی',
        'class' => 'progress',
    ],
    'replied' => [
        'label' => 'پاسخ داده‌شده',
        'class' => 'replied',
    ],
    'closed' => [
        'label' => 'بسته‌شده',
        'class' => 'closed',
    ],
];

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

function adminContactInboxPageUrl(
    int $page,
    string $search,
    string $status
): string {
    $parameters = [
        'page' => $page,
        'search' => $search,
        'status' => $status,
    ];

    $parameters = array_filter(
        $parameters,
        static fn ($value) => $value !== '' && $value !== null
    );

    return '/admin/contact-messages?' . http_build_query($parameters);
}

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
        صندوق پیام‌ها | <?= adminContactInboxEscape($appName) ?>
    </title>

    <link rel="stylesheet" href="/assets/css/style.css">

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

        .inbox-wrapper {
            max-width: 1450px;
            width: 100%;
            margin: auto;
            padding: 22px 20px 50px;
        }

        .topbar {
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

        .top-links {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
        }

        .top-links a {
            color: #e8dff0;
            font-size: 13px;
        }

        .top-links a:hover {
            color: #D45CFF;
        }

        .page-heading {
            margin: 28px 0;
            padding: 25px;
            background: linear-gradient(120deg, #25103b, #140D23);
            border: 1px solid #49305d;
            border-radius: 14px;
        }

        .page-heading h1 {
            font-size: 24px;
            margin: 0 0 12px;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #140D23;
            border: 1px solid #49305d;
            border-radius: 12px;
            padding: 19px;
        }

        .stat-card small {
            display: block;
            color: #bda9ca;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .stat-card strong {
            font-size: 27px;
            color: #D45CFF;
        }

        .stat-card.unread strong {
            color: #ffb86b;
        }

        .filter-section {
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 13px;
            padding: 22px;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 18px;
            color: #D45CFF;
            margin: 0 0 20px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
            gap: 12px;
            align-items: end;
        }

        .form-group label {
            display: block;
            color: #c7b8d5;
            font-size: 12px;
            margin-bottom: 9px;
        }

        .form-control {
            width: 100%;
            height: 43px;
            padding: 0 12px;
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
        }

        .form-control option {
            background: #140D23;
            color: #fff;
        }

        .button {
            height: 43px;
            padding: 0 18px;
            border-radius: 7px;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-family: inherit;
            font-size: 12px;
            white-space: nowrap;
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

        .table-section {
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 13px;
            overflow: hidden;
        }

        .table-heading {
            padding: 20px;
            border-bottom: 1px solid #352044;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .table-heading h2 {
            font-size: 17px;
            color: #D45CFF;
            margin: 0;
        }

        .table-heading span {
            color: #a995b8;
            font-size: 12px;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1050px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #352044;
            font-size: 12px;
            white-space: nowrap;
        }

        th {
            background: #1b1030;
            color: #D45CFF;
        }

        td {
            color: #e8dff0;
        }

        tbody tr:hover {
            background: rgba(155, 32, 255, .06);
        }

        .message-subject {
            color: #fff;
            font-weight: bold;
        }

        .message-unread {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #ffad58;
            margin-left: 7px;
        }

        .badge {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 11px;
        }

        .badge.new {
            background: #493b18;
            color: #ffdc82;
        }

        .badge.progress {
            background: #25385b;
            color: #9cc8ff;
        }

        .badge.replied {
            background: #164b39;
            color: #7ce5b7;
        }

        .badge.closed {
            background: #49305d;
            color: #dcb7ff;
        }

        .details-button {
            display: inline-block;
            padding: 8px 13px;
            border: 1px solid #8744b5;
            border-radius: 7px;
            background: rgba(155, 32, 255, .15);
            color: #e8b8ff;
            font-size: 11px;
        }

        .details-button:hover {
            background: #9b20ff;
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 45px 20px;
            color: #a995b8;
            font-size: 13px;
            line-height: 2;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            padding: 22px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            min-width: 37px;
            height: 37px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 0 10px;
            border-radius: 7px;
            border: 1px solid #49305d;
            color: #e8dff0;
            font-size: 12px;
        }

        .pagination a:hover,
        .pagination .current {
            background: #9b20ff;
            border-color: #D45CFF;
            color: #fff;
        }

        .pagination .disabled {
            opacity: .4;
            pointer-events: none;
        }

        .footer {
            text-align: center;
            color: #82738f;
            font-size: 12px;
            padding-top: 30px;
        }

        @media (max-width: 1000px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .filter-form {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 600px) {
            .inbox-wrapper {
                padding: 13px 11px 35px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-heading {
                padding: 20px 15px;
            }

            .page-heading h1 {
                font-size: 20px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 9px;
            }

            .stat-card {
                padding: 14px;
            }

            .stat-card strong {
                font-size: 23px;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .button {
                width: 100%;
            }
        }

    </style>

</head>

<body>

<div class="inbox-wrapper">

    <header class="topbar">

        <div class="brand">
            MRM Technology Team
            <small>صندوق پیام‌های مدیریت</small>
        </div>

        <nav class="top-links">
            <a href="/admin/dashboard">داشبورد مدیریت</a>
            <a href="/">صفحه اصلی</a>
        </nav>

    </header>

    <main>

        <section class="page-heading">

            <h1>
                صندوق
                <span>پیام‌ها</span>
            </h1>

            <p>
                پیام‌های ارسال‌شده از طریق صفحه تماس با ما را مشاهده،
                جست‌وجو و مدیریت کنید.
            </p>

        </section>

        <section class="stats-grid">

            <div class="stat-card">
                <small>تمام پیام‌ها</small>
                <strong><?= number_format((int) ($statistics['total'] ?? 0)) ?></strong>
            </div>

            <div class="stat-card unread">
                <small>خوانده‌نشده</small>
                <strong><?= number_format((int) ($statistics['unread'] ?? 0)) ?></strong>
            </div>

            <div class="stat-card">
                <small>پیام‌های جدید</small>
                <strong><?= number_format((int) ($statistics['new'] ?? 0)) ?></strong>
            </div>

            <div class="stat-card">
                <small>در حال بررسی</small>
                <strong><?= number_format((int) ($statistics['in_progress'] ?? 0)) ?></strong>
            </div>

            <div class="stat-card">
                <small>پاسخ داده‌شده</small>
                <strong><?= number_format((int) ($statistics['replied'] ?? 0)) ?></strong>
            </div>

        </section>

        <section class="filter-section">

            <h2 class="section-title">
                جست‌وجو و فیلتر پیام‌ها
            </h2>

            <form
                action="/admin/contact-messages"
                method="GET"
                class="filter-form"
            >

                <div class="form-group">

                    <label for="search">جست‌وجو</label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        class="form-control"
                        value="<?= adminContactInboxEscape($search) ?>"
                        placeholder="نام، ایمیل، موضوع یا متن پیام"
                    >

                </div>

                <div class="form-group">

                    <label for="status">وضعیت پیام</label>

                    <select
                        id="status"
                        name="status"
                        class="form-control"
                    >

                        <option value="">تمام وضعیت‌ها</option>

                        <?php foreach ($statuses as $key => $status): ?>

                            <option
                                value="<?= adminContactInboxEscape($key) ?>"
                                <?= $selectedStatus === $key ? 'selected' : '' ?>
                            >
                                <?= adminContactInboxEscape($status['label']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <button
                    type="submit"
                    class="button button-primary"
                >
                    جست‌وجو
                </button>

                <a
                    href="/admin/contact-messages"
                    class="button button-secondary"
                >
                    پاک‌کردن فیلترها
                </a>

            </form>

        </section>

        <section class="table-section">

            <div class="table-heading">

                <h2>فهرست پیام‌های دریافتی</h2>

                <span>
                    <?= number_format($totalMessages) ?> پیام
                </span>

            </div>

            <?php if (empty($messages)): ?>

                <div class="empty-state">
                    هنوز پیامی ثبت نشده است یا پیامی با مشخصات انتخاب‌شده پیدا نشد.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table>

                        <thead>

                            <tr>
                                <th>وضعیت خواندن</th>
                                <th>نام فرستنده</th>
                                <th>ایمیل</th>
                                <th>موضوع</th>
                                <th>وضعیت پیام</th>
                                <th>مسئول رسیدگی</th>
                                <th>تاریخ دریافت</th>
                                <th>جزئیات</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($messages as $message): ?>

                                <?php
                                $messageId = (int) ($message['id'] ?? 0);
                                $statusKey = (string) ($message['status'] ?? 'new');

                                $statusInfo = $statuses[$statusKey] ?? [
                                    'label' => 'نامشخص',
                                    'class' => '',
                                ];

                                $isUnread = empty($message['read_at']);
                                ?>

                                <tr>

                                    <td>
                                        <?php if ($isUnread): ?>
                                            <span class="message-unread"></span>
                                            خوانده‌نشده
                                        <?php else: ?>
                                            خوانده‌شده
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= adminContactInboxEscape($message['full_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= adminContactInboxEscape($message['email'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <span class="message-subject">
                                            <?= adminContactInboxEscape($message['subject'] ?? '-') ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge <?= adminContactInboxEscape($statusInfo['class']) ?>">
                                            <?= adminContactInboxEscape($statusInfo['label']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= adminContactInboxEscape($message['admin_name'] ?? 'تعیین نشده') ?>
                                    </td>

                                    <td>
                                        <?= adminContactInboxEscape($message['created_at'] ?? '-') ?>
                                    </td>

                                    <td>

                                        <?php if ($messageId > 0): ?>

                                            <a
                                                href="/admin/contact-messages/<?= $messageId ?>"
                                                class="details-button"
                                            >
                                                مشاهده پیام
                                            </a>

                                        <?php else: ?>

                                            -

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <?php if ($totalPages > 1): ?>

                    <nav class="pagination" aria-label="صفحه‌بندی">

                        <?php if ($currentPage > 1): ?>

                            <a href="<?= adminContactInboxEscape(
                                adminContactInboxPageUrl(
                                    $currentPage - 1,
                                    $search,
                                    $selectedStatus
                                )
                            ) ?>">
                                قبلی
                            </a>

                        <?php else: ?>

                            <span class="disabled">قبلی</span>

                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);
                        ?>

                        <?php for ($page = $startPage; $page <= $endPage; $page++): ?>

                            <?php if ($page === $currentPage): ?>

                                <span class="current">
                                    <?= $page ?>
                                </span>

                            <?php else: ?>

                                <a href="<?= adminContactInboxEscape(
                                    adminContactInboxPageUrl(
                                        $page,
                                        $search,
                                        $selectedStatus
                                    )
                                ) ?>">
                                    <?= $page ?>
                                </a>

                            <?php endif; ?>

                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>

                            <a href="<?= adminContactInboxEscape(
                                adminContactInboxPageUrl(
                                    $currentPage + 1,
                                    $search,
                                    $selectedStatus
                                )
                            ) ?>">
                                بعدی
                            </a>

                        <?php else: ?>

                            <span class="disabled">بعدی</span>

                        <?php endif; ?>

                    </nav>

                <?php endif; ?>

            <?php endif; ?>

        </section>

    </main>

    <footer class="footer">
        © <?= date('Y') ?> MRM Technology Team — تمامی حقوق محفوظ است.
    </footer>

</div>

</body>
</html>
