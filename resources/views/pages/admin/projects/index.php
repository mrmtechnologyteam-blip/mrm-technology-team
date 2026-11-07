<?php

declare(strict_types=1);

$projects = is_array($projects ?? null) ? $projects : [];
$categories = is_array($categories ?? null) ? $categories : [];
$filters = is_array($filters ?? null) ? $filters : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];
$statusStatistics = is_array($statusStatistics ?? null) ? $statusStatistics : [];

function adminProjectsEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$search = (string) ($filters['search'] ?? '');
$selectedStatus = (string) ($filters['status'] ?? '');
$selectedCategory = (string) ($filters['category'] ?? '');

$currentPage = max(1, (int) ($pagination['current_page'] ?? 1));
$totalPages = max(1, (int) ($pagination['total_pages'] ?? 1));
$totalProjects = (int) ($pagination['total_projects'] ?? 0);

$statuses = [
    'pending' => ['label' => 'در انتظار بررسی', 'class' => 'pending'],
    'under_review' => ['label' => 'در حال بررسی', 'class' => 'review'],
    'accepted' => ['label' => 'پذیرفته‌شده', 'class' => 'accepted'],
    'rejected' => ['label' => 'ردشده', 'class' => 'rejected'],
    'completed' => ['label' => 'تکمیل‌شده', 'class' => 'completed'],
];

function adminProjectsPageUrl(
    int $page,
    string $search,
    string $status,
    string $category
): string {
    $parameters = [
        'page' => $page,
        'search' => $search,
        'status' => $status,
        'category' => $category,
    ];

    $parameters = array_filter(
        $parameters,
        static fn($value) => $value !== '' && $value !== null
    );

    return '/admin/projects?' . http_build_query($parameters);
}

$appName = defined('APP_NAME') ? APP_NAME : 'MRM Technology Team';

$csrfToken = function_exists('csrf_token') ? csrf_token() : '';

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05000D">

    <title>مدیریت پروژه‌ها | <?= adminProjectsEscape($appName) ?></title>

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #05000D;
            color: #fff;
            font-family: Arial, sans-serif;
            min-height: 100vh;
        }

        .projects-wrapper {
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
            text-decoration: none;
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
            margin-bottom: 12px;
        }

        .page-heading h1 span {
            color: #D45CFF;
        }

        .page-heading p {
            color: #c7b8d5;
            font-size: 13px;
            line-height: 2;
        }

        .summary-card {
            margin-bottom: 25px;
            padding: 20px;
            background: #140D23;
            border: 1px solid #49305d;
            border-radius: 12px;
        }

        .summary-card small {
            display: block;
            color: #bda9ca;
            margin-bottom: 12px;
        }

        .summary-card strong {
            font-size: 30px;
            color: #D45CFF;
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
            margin-bottom: 20px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto auto;
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
            text-decoration: none;
            display: inline-flex;
            justify-content: center;
            align-items: center;
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
            min-width: 1000px;
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
        .badge.completed {
            background: #164b39;
            color: #7ce5b7;
        }

        .badge.rejected {
            background: #542633;
            color: #ff9baa;
        }

        .details-button {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #8744b5;
            border-radius: 7px;
            background: rgba(155, 32, 255, .15);
            color: #e8b8ff;
            text-decoration: none;
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
            text-decoration: none;
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

        @media (max-width: 950px) {
            .filter-form {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 550px) {
            .projects-wrapper {
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

<div class="projects-wrapper">

    <header class="topbar">

        <div class="brand">
            MRM Technology Team
            <small>مدیریت پروژه‌ها</small>
        </div>

        <nav class="top-links">
            <a href="/admin/dashboard">داشبورد مدیریت</a>
            <a href="/">صفحه اصلی</a>
        </nav>

    </header>

    <main>

        <section class="page-heading">

            <h1>
                مدیریت
                <span>پروژه‌ها</span>
            </h1>

            <p>
                تمام پروژه‌های ثبت‌شده توسط مشتریان را مشاهده، جستجو و فیلتر کنید.
                برای مشاهده اطلاعات کامل هر پروژه وارد صفحه جزئیات آن شوید.
            </p>

        </section>

        <section class="summary-card">

            <small>تعداد کل پروژه‌های ثبت‌شده</small>

            <strong>
                <?= number_format($totalProjects) ?>
            </strong>

        </section>

        <section class="filter-section">

            <h2 class="section-title">جستجو و فیلتر پروژه‌ها</h2>

            <form
                action="/admin/projects"
                method="GET"
                class="filter-form"
            >

                <div class="form-group">

                    <label for="search">جستجو</label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        class="form-control"
                        value="<?= adminProjectsEscape($search) ?>"
                        placeholder="کد پروژه، عنوان، نام مشتری یا ایمیل"
                    >

                </div>

                <div class="form-group">

                    <label for="status">وضعیت پروژه</label>

                    <select
                        id="status"
                        name="status"
                        class="form-control"
                    >

                        <option value="">تمام وضعیت‌ها</option>

                        <?php foreach ($statuses as $key => $status): ?>

                            <option
                                value="<?= adminProjectsEscape($key) ?>"
                                <?= $selectedStatus === $key ? 'selected' : '' ?>
                            >
                                <?= adminProjectsEscape($status['label']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label for="category">دسته‌بندی</label>

                    <select
                        id="category"
                        name="category"
                        class="form-control"
                    >

                        <option value="">تمام دسته‌بندی‌ها</option>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?= (int) ($category['id'] ?? 0) ?>"
                                <?= (string) ($category['id'] ?? '') === $selectedCategory ? 'selected' : '' ?>
                            >
                                <?= adminProjectsEscape($category['name'] ?? '') ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <button type="submit" class="button button-primary">
                    جستجو
                </button>

                <a href="/admin/projects" class="button button-secondary">
                    پاک‌کردن فیلترها
                </a>

            </form>

        </section>

        <section class="table-section">

            <div class="table-heading">

                <h2>فهرست پروژه‌ها</h2>

                <span>
                    <?= number_format($totalProjects) ?> پروژه
                </span>

            </div>

            <?php if (empty($projects)): ?>

                <div class="empty-state">
                    هیچ پروژه‌ای با مشخصات انتخاب‌شده پیدا نشد.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table>

                        <thead>

                            <tr>
                                <th>کد پروژه</th>
                                <th>عنوان پروژه</th>
                                <th>نام مشتری</th>
                                <th>ایمیل مشتری</th>
                                <th>دسته‌بندی</th>
                                <th>بودجه</th>
                                <th>وضعیت</th>
                                <th>تاریخ ثبت</th>
                                <th>جزئیات</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($projects as $project): ?>

                                <?php
                                $projectId = (int) ($project['id'] ?? 0);
                                $statusKey = (string) ($project['status'] ?? '');

                                $statusInfo = $statuses[$statusKey] ?? [
                                    'label' => 'نامشخص',
                                    'class' => '',
                                ];

                                $budget = $project['budget'] ?? null;
                                $currency = $project['budget_currency'] ?? '';
                                ?>

                                <tr>

                                    <td>
                                        <?= adminProjectsEscape($project['project_code'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= adminProjectsEscape($project['project_title'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= adminProjectsEscape($project['customer_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= adminProjectsEscape($project['customer_email'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= adminProjectsEscape($project['category_name'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?php if ($budget !== null && $budget !== ''): ?>
                                            <?= adminProjectsEscape($budget) ?>
                                            <?= adminProjectsEscape($currency) ?>
                                        <?php else: ?>
                                            تعیین‌نشده
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge <?= adminProjectsEscape($statusInfo['class']) ?>">
                                            <?= adminProjectsEscape($statusInfo['label']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= adminProjectsEscape($project['submitted_at'] ?? '-') ?>
                                    </td>

                                    <td>

                                        <?php if ($projectId > 0): ?>

                                            <a
                                                href="/projects/<?= $projectId ?>"
                                                class="details-button"
                                            >
                                                مشاهده جزئیات
                                            </a>

                                        <?php
