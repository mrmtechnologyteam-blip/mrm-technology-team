<?php

declare(strict_types=1);

$appName = defined('APP_NAME') ? APP_NAME : 'MRM Technology Team';

$categories = is_array($categories ?? null) ? $categories : [];
$questions = is_array($questions ?? null) ? $questions : [];
$csrfToken = (string) ($csrfToken ?? '');

function projectCreateEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$sectionTitles = [
    1 => 'اطلاعات اولیه پروژه',
    2 => 'اهداف و نیازمندی‌ها',
    3 => 'ویژگی‌ها و امکانات',
    4 => 'کاربران و دسترسی‌ها',
    5 => 'طراحی و رابط کاربری',
    6 => 'فناوری و زیرساخت',
    7 => 'اطلاعات و محتوا',
    8 => 'زمان‌بندی و بودجه',
    9 => 'امنیت و نگهداری',
    10 => 'توضیحات تکمیلی',
];

$allQuestions = [];

foreach ($questions as $categoryQuestions) {
    foreach ($categoryQuestions as $sectionQuestions) {
        foreach ($sectionQuestions as $question) {
            $allQuestions[(int) $question['id']] = $question;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05000D">

    <title>ثبت درخواست پروژه | <?= projectCreateEscape($appName) ?></title>

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
            font-family: inherit;
        }

        .project-wrapper {
            width: 100%;
            max-width: 1050px;
            margin: auto;
            padding: 25px 16px 60px;
        }

        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            padding: 15px 0 25px;
            border-bottom: 1px solid #352044;
        }

        .brand {
            color: #D45CFF;
            font-size: 20px;
            font-weight: bold;
        }

        .back-link {
            color: #d9c7e8;
            text-decoration: none;
            font-size: 14px;
        }

        .page-heading {
            margin: 30px 0 25px;
        }

        .page-heading h1 {
            font-size: 27px;
            margin: 0 0 12px;
        }

        .page-heading p {
            color: #bda9ca;
            line-height: 2;
            margin: 0;
        }

        .project-card {
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 22px;
        }

        .card-title {
            color: #D45CFF;
            font-size: 19px;
            margin: 0 0 22px;
            padding-bottom: 14px;
            border-bottom: 1px solid #352044;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            color: #eee;
            font-size: 14px;
            margin-bottom: 9px;
        }

        .required {
            color: #ff8585;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 13px 14px;
            background: #0b0613;
            color: #fff;
            border: 1px solid #49305d;
            border-radius: 8px;
            outline: none;
            font-family: inherit;
            font-size: 14px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #D45CFF;
            box-shadow: 0 0 0 2px rgba(212, 92, 255, .12);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.9;
        }

        select option {
            background: #140D23;
            color: #fff;
        }

        .question-section {
            margin-bottom: 24px;
            padding: 20px;
            background: #0e0819;
            border: 1px solid #352044;
            border-radius: 11px;
        }

        .question-section h3 {
            color: #D45CFF;
            font-size: 16px;
            margin: 0 0 20px;
        }

        .question-item {
            margin-bottom: 22px;
        }

        .question-item:last-child {
            margin-bottom: 0;
        }

        .question-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .option-label {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 8px;
            cursor: pointer;
            margin: 0;
        }

        .option-label input {
            width: auto;
            accent-color: #D45CFF;
        }

        .question-help {
            display: block;
            color: #9b8ca8;
            font-size: 12px;
            margin-top: 7px;
        }

        .conditional-question[hidden],
        .category-questions[hidden] {
            display: none !important;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .submit-button {
            border: 0;
            background: linear-gradient(100deg, #9b20ff, #d45cff);
            color: #fff;
            padding: 14px 28px;
            border-radius: 9px;
            font-family: inherit;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .submit-button:hover {
            opacity: .9;
        }

        .cancel-link {
            color: #c7b8d5;
            text-decoration: none;
            font-size: 14px;
        }

        .notice {
            padding: 14px;
            background: rgba(155, 32, 255, .10);
            border: 1px solid #49305d;
            color: #e8b8ff;
            border-radius: 9px;
            margin-bottom: 20px;
            line-height: 1.9;
        }

        .empty-questions {
            color: #bda9ca;
            line-height: 2;
            padding: 15px;
            background: #0e0819;
            border-radius: 8px;
        }

        .progress-text {
            color: #bda9ca;
            font-size: 13px;
            margin-top: 10px;
        }

        @media (max-width: 650px) {
            .project-card {
                padding: 18px 14px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .page-heading h1 {
                font-size: 22px;
            }

            .question-section {
                padding: 15px 12px;
            }

            .form-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .submit-button {
                width: 100%;
            }

            .cancel-link {
                text-align: center;
            }
        }
    </style>
</head>

<body>

<div class="project-wrapper">

    <header class="project-header">
        <div class="brand">MRM Technology Team</div>
        <a href="/dashboard" class="back-link">بازگشت به داشبورد</a>
    </header>

    <div class="page-heading">
        <h1>ثبت درخواست پروژه</h1>
        <p>
            اطلاعات پروژه خود را وارد کنید تا تیم ما بتواند نیازمندی‌های
            شما را بررسی و پیشنهاد مناسبی ارائه کند.
        </p>
    </div>

    <div class="notice">
        لطفاً اطلاعات را با دقت تکمیل کنید.
        پرسش‌های دارای علامت <span class="required">*</span> الزامی هستند.
    </div>

    <form action="/projects" method="POST" id="projectForm">

        <input
            type="hidden"
            name="_csrf_token"
            value="<?= projectCreateEscape($csrfToken) ?>"
        >

        <section class="project-card">

            <h2 class="card-title">اطلاعات اصلی پروژه</h2>

            <div class="form-grid">

                <div class="form-group full">
                    <label for="category_id">
                        نوع پروژه <span class="required">*</span>
                    </label>

                    <select
                        name="category_id"
                        id="category_id"
                        required
                    >
                        <option value="">نوع پروژه را انتخاب کنید</option>

                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>">
                                <?= projectCreateEscape($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group full">
                    <label for="project_title">
                        عنوان پروژه <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="project_title"
                        id="project_title"
                        maxlength="200"
                        placeholder="مثلاً سیستم مدیریت فروشگاه"
                        required
                    >
                </div>

                <div class="form-group full">
                    <label for="project_description">
                        توضیحات پروژه
                    </label>

                    <textarea
                        name="project_description"
                        id="project_description"
                        maxlength="10000"
                        placeholder="هدف و توضیحات کلی پروژه خود را بنویسید..."
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="budget">بودجه تقریبی</label>

                    <input
                        type="number"
                        name="budget"
                        id="budget"
                        min="0"
                        step="0.01"
                        placeholder="مبلغ مورد نظر"
                    >
                </div>

                <div class="form-group">
                    <label for="budget_currency">واحد پول</label>

                    <select name="budget_currency" id="budget_currency">
                        <option value="USD">دالر آمریکا (USD)</option>
                        <option value="AFN">افغانی (AFN)</option>
                        <option value="EUR">یورو (EUR)</option>
                    </select>
                </div>

                <div class="form-group full">
                    <label for="expected_delivery_date">
                        تاریخ مورد انتظار تحویل
                    </label>

                    <input
                        type="date"
                        name="expected_delivery_date"
                        id="expected_delivery_date"
                    >
                </div>

            </div>

        </section>

        <section class="project-card">

            <h2 class="card-title">پرسش‌نامه تخصصی پروژه</h2>

            <p class="progress-text">
                پرسش‌ها بر اساس نوع پروژه انتخاب‌شده نمایش داده می‌شوند.
            </p>

            <div id="questionnaire">

                <?php if (empty($allQuestions)): ?>

                    <div class="empty-questions">
                        هنوز پرسشی برای پرسش‌نامه ثبت نشده است.
                        ابتدا باید پرسش‌ها در پایگاه داده درج شوند.
                    </div>

                <?php else: ?>

                    <?php foreach ($questions as $categoryKey => $sections): ?>

                        <div
                            class="category-questions"
                            data-category="<?= projectCreateEscape($categoryKey) ?>"
                            <?= $categoryKey !== 'general' ? 'hidden' : '' ?>
                        >

                            <?php foreach ($sections as $sectionNumber => $sectionQuestions): ?>

                                <div class="question-section">

                                    <h3>
                                        <?= projectCreateEscape(
                                            $sectionTitles[(int) $sectionNumber]
                                            ?? 'بخش ' . (int) $sectionNumber
                                        ) ?>
                                    </h3>

                                    <?php foreach ($sectionQuestions as $question): ?>

                                        <?php
                                        $questionId = (int) $question['id'];
                                        $type = (string) $question['question_type'];
                                        $options = is_array($question['options'] ?? null)
                                            ? $question['options']
                                            : [];

                                        $parentId = $question['parent_question_id'] ?? null;
                                        ?>

                                        <div
                                            class="question-item <?= $parentId ? 'conditional-question' : '' ?>"
                                            data-question-id="<?= $questionId ?>"
                                            data-parent-id="<?= $parentId ? (int) $parentId : '' ?>"
                                            data-condition="<?= projectCreateEscape($question['condition_value'] ?? '') ?>"
                                            <?= $parentId ? 'hidden' : '' ?>
                                        >

                                            <label>
                                                <?= projectCreateEscape($question['question_text']) ?>

                                                <?php if (!empty($question['is_required'])): ?>
                                                    <span class="required">*</span>
                                                <?php endif; ?>
                                            </label>

                                            <?php if ($type === 'textarea'): ?>

                                                <textarea
                                                    name="answers[<?= $questionId ?>]"
                                                    <?= !empty($question['is_required']) ? 'required' : '' ?>
                                                ></textarea>

                                            <?php elseif ($type === 'select'): ?>

                                                <select
                                                    name="answers[<?= $questionId ?>]"
                                                    <?= !empty($question['is_required']) ? 'required' : '' ?>
                                                >
                                                    <option value="">انتخاب کنید</option>

                                                    <?php foreach ($options as $option): ?>
                                                        <option value="<?= projectCreateEscape($option) ?>">
                                                            <?= projectCreateEscape($option) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                            <?php elseif ($type === 'radio' || $type === 'checkbox'): ?>

                                                <div class="question-options">

                                                    <?php foreach ($options as $optionIndex => $option): ?>

                                                        <label class="option-label">

                                                            <input
                                                                type="<?= $type ?>"
                                                                name="answers[<?= $questionId ?>]<?= $type === 'checkbox' ? '[]' : '' ?>"
                                                                value="<?= projectCreateEscape($option) ?>"
                                                                <?= $type === 'radio' && !empty($question['is_required']) && $optionIndex === 0 ? 'required' : '' ?>
                                                            >

                                                            <span>
                                                                <?= projectCreateEscape($option) ?>
                                                            </span>

                                                        </label>

                                                    <?php endforeach; ?>

                                                </div>

                                            <?php elseif ($type === 'number'): ?>

                                                <input
                                                    type="number"
                                                    name="answers[<?= $questionId ?>]"
                                                    <?= !empty($question['is_required']) ? 'required' : '' ?>
                                                >

                                            <?php elseif ($type === 'date'): ?>

                                                <input
                                                    type="date"
                                                    name="answers[<?= $questionId ?>]"
                                                    <?= !empty($question['is_required']) ? 'required' : '' ?>
                                                >

                                            <?php else: ?>

                                                <input
                                                    type="text"
                                                    name="answers[<?= $questionId ?>]"
                                                    maxlength="10000"
                                                    <?= !empty($question['is_required']) ? 'required' : '' ?>
                                                >

                                            <?php endif; ?>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </section>

        <div class="form-actions">

            <a href="/dashboard" class="cancel-link">
                انصراف
            </a>

            <button type="submit" class="submit-button">
                ثبت درخواست پروژه
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const categorySelect = document.getElementById('category_id');
    const categoryGroups = document.querySelectorAll('.category-questions');
    const form = document.getElementById('projectForm');

    /*
     * ذخیره وضعیت اولیه required برای تمام فیلدها.
     * این مقدار در طول تغییر دسته‌ها و پرسش‌های شرطی حفظ می‌شود.
     */
    form.querySelectorAll('input, select, textarea').forEach(function (field) {
        field.dataset.initialRequired = field.required ? 'true' : 'false';
    });

    function setFieldState(field, disabled) {
        field.disabled = disabled;

        field.required =
            !disabled &&
            field.dataset.initialRequired === 'true';
    }

    function updateCategories() {
        const selectedCategory = categorySelect.value;

        categoryGroups.forEach(function (group) {
            const category = group.dataset.category;

            const visible =
                category === 'general' ||
                category === selectedCategory;

            group.hidden = !visible;

            group.querySelectorAll('input, select, textarea').forEach(function (field) {
                setFieldState(field, !visible);
            });
        });

        updateConditionalQuestions();
    }

    function getQuestionValue(questionElement) {
        const fields = questionElement.querySelectorAll(
            'input, select, textarea'
        );

        const selected = [];

        fields.forEach(function (field) {
            if (field.disabled) {
                return;
            }

            if (
                (field.type === 'radio' || field.type === 'checkbox') &&
                field.checked
            ) {
                selected.push(field.value);
            } else if (
                field.type !== 'radio' &&
                field.type !== 'checkbox' &&
                field.value !== ''
            ) {
                selected.push(field.value);
            }
        });

        return selected;
    }

    function updateConditionalQuestions() {
        const questions = document.querySelectorAll(
            '.question-item[data-parent-id]'
        );

        questions.forEach(function (question) {
            const parentId = question.dataset.parentId;

            const parent = document.querySelector(
                '.question-item[data-question-id="' + parentId + '"]'
            );

            if (!parent || parent.closest('[hidden]')) {
                question.hidden = true;
                setQuestionDisabled(question, true);
                return;
            }

            const values = getQuestionValue(parent);
            const condition = question.dataset.condition;

            const visible = values.includes(condition);

            question.hidden = !visible;
            setQuestionDisabled(question, !visible);
        });
    }

    function setQuestionDisabled(question, disabled) {
        question.querySelectorAll('input, select, textarea').forEach(function (field) {
            setFieldState(field, disabled);

            if (disabled) {
                field.checked = false;

                if (field.type !== 'radio' && field.type !== 'checkbox') {
                    field.value = '';
                }
            }
        });
    }

    categorySelect.addEventListener('change', updateCategories);

    form.addEventListener('change', updateConditionalQuestions);
    form.addEventListener('input', updateConditionalQuestions);

    form.addEventListener('submit', function (event) {
        updateConditionalQuestions();

        if (!categorySelect.value) {
            event.preventDefault();
            categorySelect.focus();
            alert('لطفاً نوع پروژه را انتخاب کنید.');
        }
    });

    updateCategories();
});
</script>

</body>
</html>
