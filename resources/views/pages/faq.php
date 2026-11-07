<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

function faqEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$faqGroups = [
    [
        'title' => 'درباره خدمات MRM',
        'icon' => '💡',
        'questions' => [
            [
                'question' => 'MRM Technology Team چه خدماتی ارائه می‌کند؟',
                'answer' => 'حوزه‌های اصلی فعالیت ما شامل توسعه اپلیکیشن، طراحی و توسعه پایگاه داده و طراحی و توسعه وب‌سایت است. جزئیات هر پروژه بر اساس نیازمندی‌های مشتری بررسی می‌شود.'
            ],
            [
                'question' => 'آیا پروژه‌های اختصاصی نیز پذیرفته می‌شوند؟',
                'answer' => 'بله. شما می‌توانید ایده یا نیازمندی اختصاصی خود را ثبت کنید. تیم ما درخواست را بررسی می‌کند و درباره امکان‌پذیری و مراحل اجرای آن تصمیم‌گیری خواهد کرد.'
            ],
            [
                'question' => 'آیا خدمات شما فقط برای شرکت‌ها است؟',
                'answer' => 'خیر. اشخاص، کسب‌وکارها، مؤسسات و سازمان‌ها می‌توانند متناسب با نیاز خود درخواست پروژه ثبت کنند.'
            ],
            [
                'question' => 'آیا امکان دریافت مشاوره قبل از ثبت پروژه وجود دارد؟',
                'answer' => 'بله. می‌توانید از طریق صفحه ارتباط با ما پرسش خود را ارسال کنید تا درخواست شما بررسی شود.'
            ],
        ],
    ],
    [
        'title' => 'ثبت و مدیریت پروژه',
        'icon' => '📋',
        'questions' => [
            [
                'question' => 'چگونه می‌توانم پروژه خود را ثبت کنم؟',
                'answer' => 'ابتدا در وب‌سایت حساب کاربری ایجاد کنید، سپس وارد حساب خود شوید و از بخش ثبت درخواست پروژه، اطلاعات و نیازمندی‌های پروژه را تکمیل و ارسال کنید.'
            ],
            [
                'question' => 'آیا برای ثبت درخواست باید حساب کاربری داشته باشم؟',
                'answer' => 'برای استفاده از امکانات ثبت و پیگیری پروژه، باید حساب کاربری ایجاد کرده و وارد سیستم شوید.'
            ],
            [
                'question' => 'پس از ثبت درخواست چه اتفاقی می‌افتد؟',
                'answer' => 'درخواست شما در سیستم ثبت می‌شود و پس از بررسی توسط تیم، وضعیت و اطلاعات مربوط به پروژه از طریق حساب کاربری قابل پیگیری خواهد بود.'
            ],
            [
                'question' => 'چگونه وضعیت پروژه خود را پیگیری کنم؟',
                'answer' => 'پس از ورود به حساب کاربری، می‌توانید از داشبورد مشتری به بخش پروژه‌های خود مراجعه کرده و وضعیت درخواست‌ها را مشاهده کنید.'
            ],
            [
                'question' => 'آیا می‌توانم اطلاعات پروژه ثبت‌شده را تغییر دهم؟',
                'answer' => 'اگر نیاز به اصلاح یا تکمیل اطلاعات پروژه دارید، از طریق راه ارتباطی با تیم تماس بگیرید. امکان تغییر اطلاعات به وضعیت و مرحله بررسی پروژه بستگی دارد.'
            ],
        ],
    ],
    [
        'title' => 'هزینه و زمان اجرای پروژه',
        'icon' => '⏱️',
        'questions' => [
            [
                'question' => 'هزینه اجرای پروژه چگونه مشخص می‌شود؟',
                'answer' => 'هزینه بر اساس نوع پروژه، امکانات مورد نیاز، پیچیدگی فنی، منابع و زمان مورد نیاز تعیین می‌شود. پس از بررسی جزئیات، شرایط مالی با مشتری هماهنگ خواهد شد.'
            ],
            [
                'question' => 'آیا قبل از شروع پروژه هزینه مشخص می‌شود؟',
                'answer' => 'بله. هدف این است که پیش از آغاز کار، محدوده پروژه، امکانات مورد توافق، هزینه و شرایط همکاری تا حد امکان روشن و مورد توافق طرفین باشد.'
            ],
            [
                'question' => 'مدت زمان تکمیل پروژه چقدر است؟',
                'answer' => 'زمان اجرا به حجم کار، پیچیدگی پروژه، امکانات درخواستی و همکاری در ارائه اطلاعات مورد نیاز بستگی دارد. زمان‌بندی پس از بررسی پروژه مشخص می‌شود.'
            ],
            [
                'question' => 'آیا امکان تغییر امکانات پروژه در جریان توسعه وجود دارد؟',
                'answer' => 'درخواست تغییرات قابل بررسی است؛ اما ممکن است بر هزینه، محدوده کار و زمان تحویل تأثیر بگذارد. تغییرات باید با هماهنگی و توافق طرفین انجام شوند.'
            ],
        ],
    ],
    [
        'title' => 'حساب کاربری و پشتیبانی',
        'icon' => '🔐',
        'questions' => [
            [
                'question' => 'اگر رمز عبور خود را فراموش کنم چه کنم؟',
                'answer' => 'از گزینه فراموشی رمز عبور در صفحه ورود استفاده کنید و مراحل بازیابی را طبق دستورالعمل ارسال‌شده به ایمیل خود دنبال کنید.'
            ],
            [
                'question' => 'چرا باید ایمیل خود را تأیید کنم؟',
                'answer' => 'تأیید ایمیل به اطمینان از صحت نشانی ایمیل و ارتباط بهتر درباره حساب کاربری و پروژه‌ها کمک می‌کند.'
            ],
            [
                'question' => 'آیا اطلاعات حساب و پروژه من خصوصی است؟',
                'answer' => 'اطلاعات حساب و پروژه‌ها برای ارائه خدمات و مدیریت درخواست‌ها استفاده می‌شوند و دسترسی به بخش‌های مدیریتی محدود به کاربران دارای مجوز است.'
            ],
            [
                'question' => 'چگونه با تیم پشتیبانی ارتباط بگیرم؟',
                'answer' => 'می‌توانید از صفحه ارتباط با ما پیام ارسال کنید و در صورت فعال بودن راه‌های ارتباطی دیگر، از آن‌ها نیز استفاده نمایید.'
            ],
        ],
    ],
];

$totalQuestions = 0;

foreach ($faqGroups as $group) {
    $totalQuestions += count($group['questions']);
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>پرسش‌های متداول | <?= faqEscape($appName) ?></title>

    <meta name="description"
          content="پاسخ پرسش‌های متداول درباره خدمات، ثبت پروژه، هزینه‌ها، زمان‌بندی و پشتیبانی MRM Technology Team.">

    <meta name="theme-color" content="#05000D">

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
        .faq-page {
            padding: 55px 8%;
        }

        .faq-hero {
            text-align: center;
            padding: 65px 20px;
            margin-bottom: 45px;
            border: 1px solid #352044;
            border-radius: 18px;
            background: linear-gradient(
                135deg,
                rgba(155, 32, 255, .18),
                rgba(212, 92, 255, .05)
            );
        }

        .faq-hero h1 {
            font-size: clamp(30px, 5vw, 46px);
            margin-bottom: 18px;
        }

        .faq-hero h1 span {
            color: #D45CFF;
        }

        .faq-hero p {
            max-width: 720px;
            margin: auto;
            color: #c7b8d5;
            line-height: 2;
            font-size: 16px;
        }

        .faq-summary {
            display: flex;
            justify-content: center;
            margin-top: 25px;
        }

        .faq-count {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 30px;
            background: rgba(155, 32, 255, .15);
            border: 1px solid #63318a;
            color: #E6C9FF;
            font-size: 14px;
        }

        .faq-content {
            max-width: 950px;
            margin: auto;
        }

        .faq-group {
            margin-bottom: 45px;
        }

        .faq-group-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #352044;
        }

        .faq-group-title .icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(155, 32, 255, .15);
            border: 1px solid #63318a;
            border-radius: 12px;
            font-size: 22px;
        }

        .faq-group-title h2 {
            font-size: 22px;
            color: #D45CFF;
        }

        .faq-item {
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 12px;
            margin-bottom: 13px;
            overflow: hidden;
            transition: border-color .3s ease;
        }

        .faq-item:hover {
            border-color: #9B20FF;
        }

        .faq-item summary {
            position: relative;
            list-style: none;
            cursor: pointer;
            padding: 21px 55px 21px 20px;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            line-height: 1.8;
        }

        .faq-item summary::-webkit-details-marker {
            display: none;
        }

        .faq-item summary::before {
            content: '+';
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 7px;
            background: rgba(155, 32, 255, .2);
            color: #D45CFF;
            font-size: 20px;
        }

        .faq-item[open] summary::before {
            content: '−';
        }

        .faq-answer {
            padding: 0 55px 22px 22px;
            color: #c7b8d5;
            line-height: 2;
            font-size: 15px;
        }

        .faq-contact {
            max-width: 950px;
            margin: 65px auto 0;
            padding: 50px 25px;
            text-align: center;
            border: 1px solid #63318a;
            border-radius: 18px;
            background: linear-gradient(
                135deg,
                rgba(155, 32, 255, .18),
                rgba(212, 92, 255, .06)
            );
        }

        .faq-contact h2 {
            font-size: 27px;
            margin-bottom: 15px;
        }

        .faq-contact p {
            color: #c7b8d5;
            line-height: 1.9;
        }

        .faq-contact a {
            display: inline-block;
            margin-top: 22px;
            padding: 13px 28px;
            border-radius: 8px;
            background: linear-gradient(90deg, #9B20FF, #D45CFF);
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .faq-contact a:hover {
            opacity: .85;
        }

        @media (max-width: 650px) {
            .faq-page {
                padding: 35px 5%;
            }

            .faq-hero {
                padding: 45px 18px;
            }

            .faq-hero p {
                font-size: 14px;
            }

            .faq-group-title h2 {
                font-size: 19px;
            }

            .faq-item summary {
                font-size: 14px;
                padding: 17px 50px 17px 15px;
            }

            .faq-answer {
                padding: 0 50px 18px 15px;
                font-size: 14px;
            }

            .faq-contact {
                padding: 40px 18px;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="logo">
        MRM Technology Team
    </div>

    <nav>
        <a href="/">خانه</a>
        <a href="/services">خدمات</a>
        <a href="/about">درباره ما</a>
        <a href="/faq">پرسش‌های متداول</a>
        <a href="/contact">ارتباط با ما</a>
    </nav>
</header>

<main class="faq-page">

    <section class="faq-hero">

        <h1>
            پرسش‌های
            <span>متداول</span>
        </h1>

        <p>
            پاسخ پرسش‌های رایج درباره خدمات، ثبت درخواست پروژه،
            هزینه‌ها، زمان‌بندی و نحوه ارتباط با تیم MRM را
            در این صفحه پیدا کنید.
        </p>

        <div class="faq-summary">
            <span class="faq-count">
                <?= $totalQuestions ?> پرسش و پاسخ
            </span>
        </div>

    </section>


    <section class="faq-content">

        <?php foreach ($faqGroups as $group): ?>

            <div class="faq-group">

                <div class="faq-group-title">

                    <span class="icon">
                        <?= faqEscape($group['icon']) ?>
                    </span>

                    <h2>
                        <?= faqEscape($group['title']) ?>
                    </h2>

                </div>


                <?php foreach ($group['questions'] as $index => $item): ?>

                    <details class="faq-item">

                        <summary>
                            <?= faqEscape($item['question']) ?>
                        </summary>

                        <div class="faq-answer">
                            <?= faqEscape($item['answer']) ?>
                        </div>

                    </details>

                <?php endforeach; ?>

            </div>

        <?php endforeach; ?>

    </section>


    <section class="faq-contact">

        <h2>پاسخ پرسش خود را پیدا نکردید؟</h2>

        <p>
            اگر سؤال دیگری دارید یا برای پروژه خود به اطلاعات
            بیشتری نیاز دارید، از طریق صفحه ارتباط با ما پیام بفرستید.
        </p>

        <a href="/contact">
            ارتباط با تیم MRM
        </a>

    </section>

</main>


<footer>

    <p>MRM Technology Team</p>

    <p>
        Your Idea. Our Technology. Your Future.
    </p>

    <p>
        تمامی حقوق محفوظ است.
    </p>

</footer>

<script src="/assets/js/app.js" defer></script>

</body>
</html>
