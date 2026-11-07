<?php

declare(strict_types=1);

$appName = defined('APP_NAME') ? APP_NAME : 'MRM Technology Team';

$admin = is_array($admin ?? null) ? $admin : [];
$userStats = is_array($userStats ?? null) ? $userStats : [];
$projectStats = is_array($projectStats ?? null) ? $projectStats : [];
$categoryStats = is_array($categoryStats ?? null) ? $categoryStats : [];
$recentProjects = is_array($recentProjects ?? null) ? $recentProjects : [];
$recentUsers = is_array($recentUsers ?? null) ? $recentUsers : [];

function adminDashboardEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$userTotal = (int) ($userStats['total'] ?? 0);
$customers = (int) ($userStats['customers'] ?? 0);
$admins = (int) ($userStats['admins'] ?? 0);
$activeUsers = (int) ($userStats['active'] ?? 0);
$pendingUsers = (int) ($userStats['pending'] ?? 0);
$suspendedUsers = (int) ($userStats['suspended'] ?? 0);

$projectTotal = (int) ($projectStats['total'] ?? 0);
$pendingProjects = (int) ($projectStats['pending'] ?? 0);
$reviewProjects = (int) ($projectStats['under_review'] ?? 0);
$acceptedProjects = (int) ($projectStats['accepted'] ?? 0);
$completedProjects = (int) ($projectStats['completed'] ?? 0);
$rejectedProjects = (int) ($projectStats['rejected'] ?? 0);

$categoryTotal = (int) ($categoryStats['total'] ?? 0);
$activeCategories = (int) ($categoryStats['active'] ?? 0);

$csrfToken = function_exists('csrf_token') ? csrf_token() : '';

$flashMessage = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

$projectStatuses = [
    'pending' => ['label' => 'در انتظار بررسی', 'class' => 'pending'],
    'under_review' => ['label' => 'در حال بررسی', 'class' => 'review'],
    'accepted' => ['label' => 'پذیرفته‌شده', 'class' => 'accepted'],
    'completed' => ['label' => 'تکمیل‌شده', 'class' => 'completed'],
    'rejected' => ['label' => 'ردشده', 'class' => 'rejected'],
];

$userStatuses = [
    'active' => ['label' => 'فعال', 'class' => 'active'],
    'pending' => ['label' => 'در انتظار تأیید', 'class' => 'pending'],
    'suspended' => ['label' => 'تعلیق‌شده', 'class' => 'suspended'],
];

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05000D">

    <title>مدیریت | <?= adminDashboardEscape($appName) ?></title>

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #05000D;
            color: #fff;
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }

        .admin-wrapper {
            width: 100%;
            max-width: 1450px;
            margin: auto;
            padding: 22px 20px 55px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            padding: 18px 0 22px;
            border-bottom: 1px solid #352044;
        }

        .admin-brand {
            color: #D45CFF;
            font-size: 21px;
            font-weight: bold;
        }

        .admin-brand small {
            display: block;
            color: #a995b8;
            font-size: 11px;
            font-weight: normal;
            margin-top: 7px;
        }

        .admin-nav {
            display: flex;
            align-items: center;
            gap: 17px;
            flex-wrap: wrap;
        }

        .admin-nav a {
            color: #e8dff0;
            text-decoration: none;
            font-size: 13px;
        }

        .admin-nav a:hover {
            color: #D45CFF;
        }

        .admin-nav .projects-link {
            background: rgba(155, 32, 255, .15);
            border: 1px solid #8744b5;
            color: #e8b8ff;
            padding: 9px 13px;
            border-radius: 7px;
        }

        .admin-nav .projects-link:hover {
            background: #9b20ff;
            color: #fff;
        }

        .logout-form {
            margin: 0;
        }

        .logout-button {
            background: transparent;
            color: #ff9b9b;
            border: 1px solid #703c4b;
            padding: 9px 14px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 12px;
        }

        .welcome-panel {
            margin: 28px 0;
            padding: 27px;
            border: 1px solid #49305d;
            border-radius: 15px;
            background: linear-gradient(120deg, #25103b, #140D23);
        }

        .welcome-panel h1 {
            font-size: 25px;
            margin-bottom: 12px;
        }

        .welcome-panel h1 span {
            color: #D45CFF;
        }

        .welcome-panel p {
            color: #c7b8d5;
            font-size: 14px;
            line-height: 2;
        }

        .admin-message {
            padding: 14px 17px;
            margin-bottom: 20px;
            background: rgba(155, 32, 255, .12);
            border: 1px solid #49305d;
            color: #e8b8ff;
            border-radius: 9px;
        }

        .section-title {
            font-size: 20px;
            margin: 32px 0 17px;
            color: #fff;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
            gap: 15px;
        }

        .stat-card {
            padding: 21px 18px;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 12px;
            min-width: 0;
        }

        .stat-card:hover {
            border-color: #8b45bd;
        }

        .stat-label {
            color: #bda9ca;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .stat-number {
            color: #D45CFF;
            font-size: 29px;
            font-weight: bold;
        }

        .stat-card.total .stat-number {
            color: #fff;
        }

        .stat-card.success .stat-number {
            color: #62dfaa;
        }

        .stat-card.warning .stat-number {
            color: #ffdc82;
        }

        .stat-card.danger .stat-number {
            color: #ff8b9b;
        }

        .stat-description {
            color: #82738f;
            font-size: 11px;
            margin-top: 10px;
        }

        .data-section {
            margin-top: 28px;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 13px;
            overflow: hidden;
        }

        .data-heading {
            padding: 20px;
            border-bottom: 1px solid #352044;
        }

        .data-heading h2 {
            font-size: 18px;
            color: #D45CFF;
        }

        .data-heading p {
            color: #9b8ca8;
            font-size: 12px;
            margin-top: 8px;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 780px;
        }

        .admin-table th,
        .admin-table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #352044;
            font-size: 12px;
            white-space: nowrap;
        }

        .admin-table th {
            background: #1b1030;
            color: #D45CFF;
            font-weight: bold;
        }

        .admin-table td {
            color: #e8dff0;
        }

        .admin-table tbody tr:hover {
            background: rgba(155, 32, 255, .06);
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
        }

        .badge.pending {
            background: #493b18;
            color: #ffdc82;
        }

        .badge.review {
            background: #25385b;
            color: #9cc8ff;
        }

        .badge.accepted,
        .badge.completed,
        .badge.active {
            background: #164b39;
            color: #7ce5b7;
        }

        .badge.rejected,
        .badge.suspended {
            background: #542633;
            color: #ff9baa;
        }

        .details-button {
            display: inline-block;
            padding: 8px 13px;
            border-radius: 7px;
            background: rgba(155, 32, 255, .15);
            border: 1px solid #8744b5;
            color: #e8b8ff;
            text-decoration: none;
            font-size: 11px;
            transition: .2s;
        }

        .details-button:hover {
            background: #9b20ff;
            border-color: #d45cff;
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 35px 20px;
            color: #a995b8;
            line-height: 2;
            font-size: 13px;
        }

        .admin-profile {
            margin-top: 28px;
            padding: 22px;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 12px;
        }

        .admin-profile h2 {
            color: #D45CFF;
            font-size: 18px;
            margin-bottom: 18px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }

        .profile-item small {
            display: block;
            color: #9b8ca8;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .profile-item strong {
            color: #eee;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .admin-footer {
            text-align: center;
            color: #82738f;
            font-size: 12px;
            padding-top: 35px;
        }

        @media (max-width: 650px) {
            .admin-wrapper {
                padding: 13px 11px 35px;
            }

            .admin-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .admin-nav {
                gap: 12px;
            }

            .welcome-panel {
                padding: 21px 17px;
            }

            .welcome-panel h1 {
                font-size: 21px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .stat-card {
                padding: 16px 12px;
            }

            .stat-number {
                font-size: 24px;
            }

            .section-title {
                font-size: 18px;
            }
        }

        @media (max-width: 350px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="admin-wrapper">

    <header class="admin-header">

        <div class="admin-brand">
            MRM Technology Team
            <small>پنل مدیریت سامانه</small>
        </div>

        <nav class="admin-nav">
            <a href="/admin/dashboard">داشبورد مدیریت</a>

            <a href="/admin/projects" class="projects-link">
                مدیریت پروژه‌ها
            </a>

            <a href="/">صفحه اصلی</a>

            <form action="/logout" method="POST" class="logout-form">
                <input
                    type="hidden"
                    name="_csrf_token"
                    value="<?= adminDashboardEscape($csrfToken) ?>"
                >

                <button type="submit" class="logout-button">
                    خروج از حساب
                </button>
            </form>
        </nav>

    </header>

    <main>

        <?php if (is_string($flashMessage) && $flashMessage !== ''): ?>
            <div class="admin-message">
                <?= adminDashboardEscape($flashMessage) ?>
            </div>
        <?php endif; ?>

        <section class="welcome-panel">

            <h1>
                خوش آمدید،
                <span><?= adminDashboardEscape($admin['full_name'] ?? 'مدیر سیستم') ?></span>
            </h1>

            <p>
                به پنل مدیریت MRM Technology Team خوش آمدید.
                از این بخش می‌توانید آمار کاربران، وضعیت پروژه‌ها،
                دسته‌بندی‌ها و فعالیت‌های اخیر سامانه را مشاهده کنید.
            </p>

        </section>

        <h2 class="section-title">آمار کلی سامانه</h2>

        <section class="stats-grid">

            <div class="stat-card total">
                <div class="stat-label">مجموع کاربران</div>
                <div class="stat-number"><?= $userTotal ?></div>
                <div class="stat-description">تمام حساب‌های ثبت‌شده</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">مشتریان</div>
                <div class="stat-number"><?= $customers ?></div>
                <div class="stat-description">حساب‌های مشتریان</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">مدیران</div>
                <div class="stat-number"><?= $admins ?></div>
                <div class="stat-description">حساب‌های مدیریتی</div>
            </div>

            <div class="stat-card success">
                <div class="stat-label">کاربران فعال</div>
                <div class="stat-number"><?= $activeUsers ?></div>
                <div class="stat-description">حساب‌های فعال سامانه</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-label">کاربران در انتظار</div>
                <div class="stat-number"><?= $pendingUsers ?></div>
                <div class="stat-description">حساب‌های نیازمند بررسی</div>
            </div>

            <div class="stat-card danger">
                <div class="stat-label">کاربران تعلیق‌شده</div>
                <div class="stat-number"><?= $suspendedUsers ?></div>
                <div class="stat-description">حساب‌های غیرفعال‌شده</div>
            </div>

        </section>

        <h2 class="section-title">وضعیت پروژه‌ها</h2>

        <section class="stats-grid">

            <div class="stat-card total">
                <div class="stat-label">مجموع پروژه‌ها</div>
                <div class="stat-number"><?= $projectTotal ?></div>
            </div>

            <div class="stat-card warning">
                <div class="stat-label">در انتظار بررسی</div>
                <div class="stat-number"><?= $pendingProjects ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">در حال بررسی</div>
                <div class="stat-number"><?= $reviewProjects ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-label">پذیرفته‌شده</div>
                <div class="stat-number"><?= $acceptedProjects ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-label">تکمیل‌شده</div>
                <div class="stat-number"><?= $completedProjects ?></div>
            </div>

            <div class="stat-card danger">
                <div class="stat-label">ردشده</div>
                <div class="stat-number"><?= $rejectedProjects ?></div>
            </div>

        </section>

        <h2 class="section-title">دسته‌بندی‌های پروژه</h2>

        <section class="stats-grid">

            <div class="stat-card total">
                <div class="stat-label">مجموع دسته‌بندی‌ها</div>
                <div class="stat-number"><?= $categoryTotal ?></div>
            </div>

            <div class="stat-card success">
                <div class="stat-label">دسته‌بندی‌های فعال</div>
                <div class="stat-number"><?= $activeCategories ?></div>
            </div>

        </section>

        <section class="data-section">

            <div class="data-heading">
                <h2>آخرین پروژه‌های ثبت‌شده</h2>
                <p>
                    پنج پروژه اخیر ثبت‌شده در سامانه؛ برای مشاهده جزئیات هر پروژه روی دکمه مربوطه کلیک کنید.
                </p>
            </div>

            <?php if (empty($recentProjects)): ?>

                <div class="empty-state">
                    هنوز هیچ پروژه‌ای در سامانه ثبت نشده است.
                </div>

            <?php else: ?>

                <div class="table-wrapper">

                    <table class="admin-table">

                        <thead>
                            <tr>
                                <th>کد پروژه</th>
                                <th>عنوان پروژه</th>
                                <th>مشتری</th>
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
                                $statusInfo = $projectStatuses[$status] ?? [
                                    'label' => 'نامشخص',
                                    'class' => '',
                                ];

                                $projectId = (int) ($project['id'] ?? 0);
                                ?>

                                <tr>

                                    <td>
                                        <?= adminDashboardEscape($project['project_code'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= adminDashboardEscape($project['project_title'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= adminDashboardEscape($project['customer_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= adminDashboardEscape($project['category_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <span class="badge <?= adminDashboardEscape($statusInfo['class']) ?>">
                                            <?= adminDashboardEscape($statusInfo['label']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= adminDashboardEscape($project['submitted_at'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?php if ($projectId > 0): ?>
                                            <a
                                                class="details-button"
                                                href="/projects/<?= $projectId ?>"
                                            >
                                                مشاهده جزئیات
                                            </a>
                                        <?php else: ?>
                                            <span>—</span>
                                        <?php endif; ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </section>

        <section class="data-section">

            <div class="data-heading">
                <h2>آخرین کاربران ثبت‌نام‌شده</h2>
                <p>پنج مشتری جدید سامانه</p>
            </div>

            <?php if (empty($recentUsers)): ?>

                <div class="empty-state">
                    هنوز هیچ مشتری در سامانه ثبت‌نام نکرده است.
                </div>

            <?php else: ?>

                <div class="table-wrapper">

                    <table class="admin-table">

                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>نام کامل</th>
                                <th>ایمیل</th>
                                <th>وضعیت حساب</th>
                                <th>تاریخ ثبت‌نام</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($recentUsers as $recentUser): ?>

                                <?php
                                $userStatus = (string) ($recentUser['account_status'] ?? '');
                                $userStatusInfo = $userStatuses[$userStatus] ?? [
                                    'label' => 'نامشخص',
                                    'class' => '',
                                ];
                                ?>

                                <tr>

                                    <td>
                                        <?= (int) ($recentUser['id'] ?? 0) ?>
                                    </td>

                                    <td>
                                        <?= adminDashboardEscape($recentUser['full_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= adminDashboardEscape($recentUser['email'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <span class="badge <?= adminDashboardEscape($userStatusInfo['class']) ?>">
                                            <?= adminDashboardEscape($userStatusInfo['label']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= adminDashboardEscape($recentUser['created_at'] ?? '-') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </section>

        <section class="admin-profile">

            <h2>اطلاعات حساب مدیر</h2>

            <div class="profile-grid">

                <div class="profile-item">
                    <small>نام مدیر</small>
                    <strong>
                        <?= adminDashboardEscape($admin['full_name'] ?? '-') ?>
                    </strong>
                </div>

                <div class="profile-item">
                    <small>ایمیل مدیر</small>
                    <strong>
                        <?= adminDashboardEscape($admin['email'] ?? '-') ?>
                    </strong>
                </div>

                <div class="profile-item">
                    <small>تاریخ ایجاد حساب</small>
                    <strong>
                        <?= adminDashboardEscape($admin['created_at'] ?? '-') ?>
                    </strong>
                </div>

            </div>

        </section>

    </main>

    <footer class="admin-footer">
        © <?= date('Y') ?> <?= adminDashboardEscape($appName) ?> — تمامی حقوق محفوظ است.
    </footer>

</div>

</body>
</html>
