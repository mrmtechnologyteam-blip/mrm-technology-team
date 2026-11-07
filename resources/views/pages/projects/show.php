<?php

declare(strict_types=1);

/**
 * Project Details View
 */

function projectDetailsEscape(mixed $value): string
{
    if (is_array($value)) {
        $value = implode(
            '، ',
            array_map(
                static fn ($item) => is_scalar($item)
                    ? (string) $item
                    : json_encode($item, JSON_UNESCAPED_UNICODE),
                $value
            )
        );
    }

    if ($value === null || $value === '') {
        return 'ثبت نشده';
    }

    if (is_bool($value)) {
        $value = $value ? 'بله' : 'خیر';
    }

    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

$statusLabels = [
    'pending' => 'در انتظار بررسی',
    'under_review' => 'در حال بررسی',
    'accepted' => 'پذیرفته شده',
    'rejected' => 'رد شده',
    'completed' => 'تکمیل شده',
];

$statusClasses = [
    'pending' => 'pending',
    'under_review' => 'review',
    'accepted' => 'accepted',
    'rejected' => 'rejected',
    'completed' => 'completed',
];

$status = $project['status'] ?? 'pending';

$statusLabel = $statusLabels[$status] ?? 'نامشخص';
$statusClass = $statusClasses[$status] ?? 'pending';

$backUrl = $isAdmin
    ? '/admin/dashboard'
    : '/dashboard';

$budget = $project['budget'] ?? null;
$currency = $project['budget_currency'] ?? '';

?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="جزئیات و پیگیری پروژه در MRM Technology Team"
    >

    <title>
        <?= projectDetailsEscape($project['project_title'] ?? 'جزئیات پروژه') ?>
        | MRM Technology Team
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Tahoma, Arial, sans-serif;
            background: #080410;
            color: #f5efff;
            line-height: 1.9;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page-wrapper {
            width: min(1150px, 94%);
            margin: 35px auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .brand {
            font-size: 21px;
            font-weight: bold;
            color: #c58aff;
        }

        .back-button {
            display: inline-block;
            padding: 9px 18px;
            background: #211332;
            border: 1px solid #54317c;
            border-radius: 9px;
            color: #e7d2ff;
        }

        .back-button:hover {
            background: #35204d;
        }

        .page-title {
            margin: 0 0 22px;
            font-size: 25px;
        }

        .card {
            background: #140d20;
            border: 1px solid #30203f;
            border-radius: 15px;
            padding: 23px;
            margin-bottom: 22px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .15);
        }

        .card h2 {
            margin: 0 0 20px;
            color: #d4aaff;
            font-size: 19px;
        }

        .project-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            flex-wrap: wrap;
        }

        .project-heading h2 {
            margin: 0;
            color: #fff;
            font-size: 22px;
        }

        .project-code {
            color: #b99bcf;
            font-size: 13px;
            direction: ltr;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 13px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: bold;
        }

        .pending {
            color: #ffd98b;
            background: #493716;
        }

        .review {
            color: #9bd8ff;
            background: #153b53;
        }

        .accepted {
            color: #9bffc1;
            background: #16442c;
        }

        .rejected {
            color: #ffaaaa;
            background: #501d27;
        }

        .completed {
            color: #d8b1ff;
            background: #43245e;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 23px;
        }

        .info-item {
            padding: 13px;
            background: #1c1229;
            border-radius: 9px;
            min-width: 0;
        }

        .info-item small {
            display: block;
            color: #a995bb;
            margin-bottom: 5px;
        }

        .info-item strong {
            overflow-wrap: anywhere;
        }

        .description {
            margin-top: 20px;
            padding: 15px;
            background: #1c1229;
            border-radius: 10px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .section-box {
            margin-bottom: 18px;
            border: 1px solid #39264d;
            border-radius: 11px;
            overflow: hidden;
        }

        .section-title {
            padding: 12px 16px;
            background: #251536;
            color: #d9baff;
            font-weight: bold;
        }

        .answer-item {
            padding: 13px 16px;
            border-top: 1px solid #30203f;
        }

        .question {
            display: block;
            color: #c5acd9;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .answer {
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        .empty-message {
            padding: 15px;
            color: #a995bb;
            text-align: center;
            background: #1c1229;
            border-radius: 9px;
        }

        .timeline {
            position: relative;
        }

        .timeline-item {
            position: relative;
            padding: 0 25px 24px 0;
            border-right: 2px solid #604083;
        }

        .timeline-item:last-child {
            border-right-color: transparent;
            padding-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            right: -7px;
            top: 7px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #c58aff;
            box-shadow: 0 0 10px #a65aff;
        }

        .timeline-item strong {
            display: block;
            color: #e5ceff;
        }

        .timeline-item small {
            display: block;
            color: #9d88b2;
            margin-top: 3px;
        }

        .timeline-note {
            margin-top: 7px;
            color: #c4b3d6;
            overflow-wrap: anywhere;
        }

        .footer {
            text-align: center;
            color: #887698;
            font-size: 13px;
            padding: 20px 0;
        }

        @media (max-width: 650px) {

            .page-wrapper {
                margin: 20px auto;
            }

            .card {
                padding: 16px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 21px;
            }

            .project-heading h2 {
                font-size: 19px;
            }

        }

    </style>

</head>

<body>

<div class="page-wrapper">

    <header class="header">

        <div class="brand">
            MRM Technology Team
        </div>

        <a
            href="<?= projectDetailsEscape($backUrl) ?>"
            class="back-button"
        >
            بازگشت به داشبورد
        </a>

    </header>

    <h1 class="page-title">
        جزئیات و پیگیری پروژه
    </h1>

    <!-- Project Information -->

    <section class="card">

        <div class="project-heading">

            <div>

                <h2>
                    <?= projectDetailsEscape($project['project_title'] ?? '-') ?>
                </h2>

                <div class="project-code">
                    <?= projectDetailsEscape($project['project_code'] ?? '-') ?>
                </div>

            </div>

            <span class="status-badge <?= projectDetailsEscape($statusClass) ?>">
                <?= projectDetailsEscape($statusLabel) ?>
            </span>

        </div>

        <div class="info-grid">

            <div class="info-item">
                <small>دسته‌بندی پروژه</small>
                <strong>
                    <?= projectDetailsEscape($project['category_name'] ?? '-') ?>
                </strong>
            </div>

            <div class="info-item">
                <small>نام مشتری</small>
                <strong>
                    <?= projectDetailsEscape($project['customer_name'] ?? '-') ?>
                </strong>
            </div>

            <div class="info-item">
                <small>ایمیل مشتری</small>
                <strong>
                    <?= projectDetailsEscape($project['customer_email'] ?? '-') ?>
                </strong>
            </div>

            <div class="info-item">
                <small>شماره واتساپ</small>
                <strong>
                    <?= projectDetailsEscape($project['customer_whatsapp'] ?? '-') ?>
                </strong>
            </div>

            <div class="info-item">
                <small>بودجه</small>
                <strong>
                    <?php if ($budget !== null && $budget !== ''): ?>
                        <?= projectDetailsEscape($budget) ?>
                        <?= projectDetailsEscape($currency) ?>
                    <?php else: ?>
                        مشخص نشده
                    <?php endif; ?>
                </strong>
            </div>

            <div class="info-item">
                <small>تاریخ تحویل پیشنهادی</small>
                <strong>
                    <?= projectDetailsEscape($project['expected_delivery_date'] ?? '-') ?>
                </strong>
            </div>

            <div class="info-item">
                <small>تاریخ ثبت پروژه</small>
                <strong>
                    <?= projectDetailsEscape($project['submitted_at'] ?? '-') ?>
                </strong>
            </div>

            <div class="info-item">
                <small>آخرین به‌روزرسانی</small>
                <strong>
                    <?= projectDetailsEscape($project['updated_at'] ?? '-') ?>
                </strong>
            </div>

        </div>

        <div class="description">

            <strong>توضیحات پروژه:</strong>

            <br>

            <?= projectDetailsEscape($project['project_description'] ?? 'توضیحاتی ثبت نشده است.') ?>

        </div>

    </section>

    <!-- Questionnaire Answers -->

    <section class="card">

        <h2>
            پاسخ‌های پرسش‌نامه پروژه
        </h2>

        <?php if (empty($sections)): ?>

            <div class="empty-message">
                پاسخی برای این پروژه ثبت نشده است.
            </div>

        <?php else: ?>

            <?php foreach ($sections as $sectionNumber => $sectionAnswers): ?>

                <div class="section-box">

                    <div class="section-title">
                        بخش <?= projectDetailsEscape($sectionNumber) ?>
                    </div>

                    <?php foreach ($sectionAnswers as $answer): ?>

                        <div class="answer-item">

                            <span class="question">
                                <?= projectDetailsEscape($answer['question_text'] ?? '-') ?>
                            </span>

                            <div class="answer">
                                <?= projectDetailsEscape($answer['display_value'] ?? null) ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

    <!-- Status History -->

    <section class="card">

        <h2>
            تاریخچه وضعیت پروژه
        </h2>

        <?php if (empty($history)): ?>

            <div class="empty-message">
                تاریخچه‌ای برای این پروژه ثبت نشده است.
            </div>

        <?php else: ?>

            <div class="timeline">

                <?php foreach ($history as $item): ?>

                    <?php
                    $newStatus = $item['new_status'] ?? '';
                    $oldStatus = $item['old_status'] ?? '';

                    $newLabel = $statusLabels[$newStatus] ?? $newStatus;
                    $oldLabel = $statusLabels[$oldStatus] ?? $oldStatus;
                    ?>

                    <div class="timeline-item">

                        <span class="timeline-dot"></span>

                        <strong>
                            <?= projectDetailsEscape($newLabel) ?>
                        </strong>

                        <?php if ($oldStatus !== ''): ?>

                            <div>
                                تغییر وضعیت از
                                <?= projectDetailsEscape($oldLabel) ?>
                                به
                                <?= projectDetailsEscape($newLabel) ?>
                            </div>

                        <?php else: ?>

                            <div>
                                وضعیت اولیه پروژه
                            </div>

                        <?php endif; ?>

                        <small>
                            <?= projectDetailsEscape($item['changed_at'] ?? '-') ?>
                        </small>

                        <small>
                            ثبت‌کننده:
                            <?= projectDetailsEscape($item['changed_by_name'] ?? '-') ?>
                        </small>

                        <?php if (!empty($item['note'])): ?>

                            <div class="timeline-note">
                                <?= projectDetailsEscape($item['note']) ?>
                            </div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <footer class="footer">

        © <?= date('Y') ?> MRM Technology Team

        — تمامی حقوق محفوظ است.

    </footer>

</div>

</body>
</html>
