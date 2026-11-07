<?php

declare(strict_types=1);

$appName = defined('APP_NAME') ? APP_NAME : 'MRM Technology Team';

$user = is_array($user ?? null) ? $user : [];
$projectStats = is_array($projectStats ?? null) ? $projectStats : [];
$recentProjects = is_array($recentProjects ?? null) ? $recentProjects : [];

function customerDashboardEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$statuses = [
    'pending' => 'در انتظار بررسی',
    'under_review' => 'در حال بررسی',
    'accepted' => 'پذیرفته‌شده',
    'completed' => 'تکمیل‌شده',
    'rejected' => 'ردشده',
];

$statusClasses = [
    'pending' => 'pending',
    'under_review' => 'review',
    'accepted' => 'accepted',
    'completed' => 'completed',
    'rejected' => 'rejected',
];

$total = (int) ($projectStats['total'] ?? 0);
$pending = (int) ($projectStats['pending'] ?? 0);
$underReview = (int) ($projectStats['under_review'] ?? 0);
$accepted = (int) ($projectStats['accepted'] ?? 0);
$completed = (int) ($projectStats['completed'] ?? 0);
$rejected = (int) ($projectStats['rejected'] ?? 0);

$csrfToken = function_exists('csrf_token') ? csrf_token() : '';

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05000D">

    <title>داشبورد مشتری | <?= customerDashboardEscape($appName) ?></title>

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #05000D;
            color: #fff;
            min-height: 100vh;
        }

        .dashboard-wrapper {
            width: 100%;
            max-width: 1250px;
            margin: auto;
            padding: 25px 20px 60px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            padding: 20px 0;
            border-bottom: 1px solid #352044;
        }

        .dashboard-brand {
            color: #D45CFF;
            font-size: 21px;
            font-weight: bold;
        }

        .dashboard-nav {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .dashboard-nav a {
            color: #e8dff0;
            text-decoration: none;
            font-size: 14px;
        }

        .dashboard-nav a:hover {
            color: #D45CFF;
        }

        .logout-form {
            display: inline;
            margin: 0;
        }

        .logout-button {
            background: transparent;
            color: #ff9b9b;
            border: 1px solid #703c4b;
            padding: 9px 15px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 13px;
        }

        .welcome-section {
            margin: 35px 0 25px;
            padding: 28px;
            background: linear-gradient(120deg, #1b1030, #140D23);
            border: 1px solid #352044;
            border-radius: 15px;
        }

        .welcome-section h1 {
            font-size: 25px;
            margin-bottom: 12px;
        }

        .welcome-section h1 span {
            color: #D45CFF;
        }

        .welcome-section p {
            color: #c7b8d5;
            line-height: 1.9;
        }

        .dashboard-message {
            padding: 13px 16px;
            background: rgba(155, 32, 255, .12);
            border: 1px solid #49305d;
            color: #e8b8ff;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 21px;
            margin: 30px 0 18px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
        }

        .stat-card {
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 12px;
            padding: 22px 18px;
        }

        .stat-label {
            color: #bda9ca;
            font-size: 13px;
            margin-bottom: 13px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #D45CFF;
        }

        .stat-card.total .stat-number {
            color: #fff;
        }

        .stat-card.completed .stat-number {
            color: #62dfaa;
        }

        .stat-card.rejected .stat-number {
            color: #ff8b9b;
        }

        .projects-section {
            margin-top: 35px;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 13px;
            overflow: hidden;
        }

        .projects-heading {
            padding: 20px;
            border-bottom: 1px solid #352044;
        }

        .projects-heading h2 {
            font-size: 19px;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .projects-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        .projects-table th,
        .projects-table td {
            padding: 16px;
            text-align: right;
            border-bottom: 1px solid #352044;
            font-size: 13px;
        }

        .projects-table th {
            color: #D45CFF;
            background: #1b1030;
        }

        .projects-table td {
            color: #e8dff0;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            white-space: nowrap;
        }

        .status-badge.pending {
            background: #493b18;
            color: #ffdc82;
        }

        .status-badge.review {
            background: #25385b;
            color: #9cc8ff;
        }

        .status-badge.accepted,
        .status-badge.completed {
            background: #164b39;
            color: #7ce5b7;
        }

        .status-badge.rejected {
            background: #542633;
            color: #ff9baa;
        }

        .details-button {
            display: inline-block;
            padding: 8px 13px;
            border-radius: 7px;
            background: linear-gradient(135deg, #9b20ff, #d45cff);
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .details-button:hover {
            opacity: .85;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #bda9ca;
            line-height: 2;
        }

        .new-project-button {
            display: inline-block;
            margin-top: 15px;
            padding: 11px 20px;
            background: linear-gradient(135deg, #9b20ff, #d45cff);
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        .profile-card {
            margin-top: 30px;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 12px;
            padding: 22px;
        }

        .profile-card h2 {
            font-size: 19px;
            margin-bottom: 18px;
            color: #D45CFF;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }

        .profile-item small {
            display: block;
            color: #9b8ca8;
            margin-bottom: 7px;
        }

        .profile-item strong {
            display: block;
            color: #eee;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .dashboard-footer {
            text-align: center;
            color: #82738f;
            font-size: 12px;
            padding-top: 35px;
        }

        @media (max-width: 600px) {
            .dashboard-wrapper {
                padding: 15px 12px 40px;
            }

            .dashboard-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .dashboard-nav {
                gap: 12px;
            }

            .welcome-section {
                padding: 22px 17px;
            }

            .welcome-section h1 {
                font-size: 21px;
            }

            .stat-card {
                padding: 17px 13px;
            }

            .stat-number {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<div class="dashboard-wrapper">

    <header class="dashboard-header">

        <div class="dashboard-brand">
            MRM Technology Team
        </div>

        <nav class="dashboard-nav">
            <a href="/dashboard">داشبورد</a>
            <a href="/">صفحه اصلی</a>
            <a href="/projects/create">ثبت پروژه جدید</a>

            <form action="/logout" method="POST" class="logout-form">

                <input
                    type="hidden"
                    name="_csrf_token"
                    value="<?= customerDashboardEscape($csrfToken) ?>"
                >

                <button type="submit" class="logout-button">
                    خروج از حساب
                </button>

            </form>
        </nav>

    </header>

    <main>

        <?php if (is_string($flashMessage) && $flashMessage !== ''): ?>
            <div class="dashboard-message">
                <?= customerDashboardEscape($flashMessage) ?>
            </div>
        <?php endif; ?>

        <section class="welcome-section">

            <h1>
                خوش آمدید،
                <span>
                    <?= customerDashboardEscape($user['full_name'] ?? 'مشتری') ?>
                </span>
            </h1>

            <p>
                از این بخش می‌توانید وضعیت درخواست‌ها و پروژه‌های خود
                را مشاهده کنید، جزئیات پروژه‌ها را بررسی نمایید
                و اطلاعات حساب کاربری‌تان را ببینید.
            </p>

            <a href="/projects/create" class="new-project-button">
                ثبت درخواست پروژه جدید
            </a>

        </section>

        <h2 class="section-title">
            خلاصه وضعیت پروژه‌ها
        </h2>

        <section class="stats-grid">

            <div class="stat-card total">
                <div class="stat-label">مجموع پروژه‌ها</div>
                <div class="stat-number"><?= $total ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">در انتظار بررسی</div>
                <div class="stat-number"><?= $pending ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">در حال بررسی</div>
                <div class="stat-number"><?= $underReview ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">پذیرفته‌شده</div>
                <div class="stat-number"><?= $accepted ?></div>
            </div>

            <div class="stat-card completed">
                <div class="stat-label">تکمیل‌شده</div>
                <div class="stat-number"><?= $completed ?></div>
            </div>

            <div class="stat-card rejected">
                <div class="stat-label">ردشده</div>
                <div class="stat-number"><?= $rejected ?></div>
            </div>

        </section>

        <section class="projects-section">

            <div class="projects-heading">
                <h2>آخرین پروژه‌های شما</h2>
            </div>

            <?php if (empty($recentProjects)): ?>

                <div class="empty-state">

                    <p>هنوز هیچ پروژه‌ای ثبت نکرده‌اید.</p>

                    <p>
                        پس از ثبت درخواست پروژه، اطلاعات آن در این قسمت نمایش داده می‌شود.
                    </p>

                    <a href="/projects/create" class="new-project-button">
                        ثبت اولین پروژه
                    </a>

                </div>

            <?php else: ?>

                <div class="table-wrapper">

                    <table class="projects-table">

                        <thead>
                            <tr>
                                <th>کد پروژه</th>
                                <th>عنوان پروژه</th>
                                <th>دسته‌بندی</th>
                                <th>وضعیت</th>
                                <th>تاریخ ثبت</th>
                                <th>جزئیات</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($recentProjects as $project): ?>

                                <?php
                                $status = (string) ($project['status'] ?? '');
                                $statusLabel = $statuses[$status] ?? 'نامشخص';
                                $statusClass = $statusClasses[$status] ?? '';
                                $projectId = (int) ($project['id'] ?? 0);
                                ?>

                                <tr>

                                    <td>
                                        <?= customerDashboardEscape($project['project_code'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= customerDashboardEscape($project['project_title'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= customerDashboardEscape($project['category_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <span class="status-badge <?= customerDashboardEscape($statusClass) ?>">
                                            <?= customerDashboardEscape($statusLabel) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= customerDashboardEscape($project['submitted_at'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?php if ($projectId > 0): ?>
                                            <a
                                                href="/projects/<?= $projectId ?>"
                                                class="details-button"
                                            >
                                                مشاهده جزئیات
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

            <?php endif; ?>

        </section>

        <section class="profile-card">

            <h2>اطلاعات حساب کاربری</h2>

            <div class="profile-grid">

                <div class="profile-item">
                    <small>نام کامل</small>
                    <strong>
                        <?= customerDashboardEscape($user['full_name'] ?? '-') ?>
                    </strong>
                </div>

                <div class="profile-item">
                    <small>ایمیل</small>
                    <strong>
                        <?= customerDashboardEscape($user['email'] ?? '-') ?>
                    </strong>
                </div>

                <div class="profile-item">
                    <small>شماره واتساپ</small>
                    <strong>
                        <?= customerDashboardEscape($user['whatsapp_number'] ?? '-') ?>
                    </strong>
                </div>

                <div class="profile-item">
                    <small>کشور</small>
                    <strong>
                        <?= customerDashboardEscape($user['country'] ?? '-') ?>
                    </strong>
                </div>

                <div class="profile-item">
                    <small>تاریخ ایجاد حساب</small>
                    <strong>
                        <?= customerDashboardEscape($user['created_at'] ?? '-') ?>
                    </strong>
                </div>

            </div>

        </section>

    </main>

    <footer class="dashboard-footer">
        © <?= date('Y') ?> MRM Technology Team — تمامی حقوق محفوظ است.
    </footer>

</div>

</body>
</html>
