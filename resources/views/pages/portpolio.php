<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

function portfolioEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$portfolioCategories = [
    [
        'icon' => '📱',
        'title' => 'اپلیکیشن‌ها',
        'description' => 'اپلیکیشن‌های موبایل و سیستم‌های نرم‌افزاری اختصاصی.',
        'technology' => 'Mobile & Software Development',
    ],
    [
        'icon' => '🗄️',
        'title' => 'سیستم‌های دیتابیس',
        'description' => 'ساختارهای پایگاه داده و سیستم‌های مدیریت اطلاعات.',
        'technology' => 'Database Design & Management',
    ],
    [
        'icon' => '🌐',
        'title' => 'وب‌سایت‌ها',
        'description' => 'وب‌سایت‌های شرکتی، آموزشی و سیستم‌های تحت وب.',
        'technology' => 'Web Development',
    ],
];

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>نمونه‌کارها | <?= portfolioEscape($appName) ?></title>

    <meta name="description"
          content="آشنایی با حوزه‌های کاری و نمونه‌کارهای MRM Technology Team در زمینه توسعه اپلیکیشن، دیتابیس و وب‌سایت.">

    <meta name="theme-color" content="#05000D">

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
        .portfolio-page {
            padding: 55px 8%;
        }

        .portfolio-hero {
            text-align: center;
            padding: 70px 20px;
            margin-bottom: 55px;
            border: 1px solid #352044;
            border-radius: 18px;
            background: linear-gradient(
                135deg,
                rgba(155, 32, 255, .18),
                rgba(212, 92, 255, .05)
            );
        }

        .portfolio-hero h1 {
            font-size: clamp(30px, 5vw, 48px);
            margin-bottom: 20px;
        }

        .portfolio-hero h1 span {
            color: #D45CFF;
        }

        .portfolio-hero p {
            max-width: 750px;
            margin: auto;
            color: #c7b8d5;
            line-height: 2;
            font-size: 16px;
        }

        .portfolio-section {
            margin-bottom: 65px;
        }

        .portfolio-section-title {
            text-align: center;
            margin-bottom: 35px;
        }

        .portfolio-section-title h2 {
            font-size: 29px;
            margin-bottom: 12px;
        }

        .portfolio-section-title p {
            color: #b9a9c7;
            line-height: 1.9;
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 23px;
        }

        .portfolio-card {
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 15px;
            padding: 30px 25px;
            transition: .3s ease;
        }

        .portfolio-card:hover {
            transform: translateY(-6px);
            border-color: #9B20FF;
            box-shadow: 0 12px 30px rgba(155, 32, 255, .12);
        }

        .portfolio-icon {
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(155, 32, 255, .15);
            border: 1px solid #63318a;
            border-radius: 14px;
            font-size: 30px;
            margin-bottom: 22px;
        }

        .portfolio-card h3 {
            color: #D45CFF;
            font-size: 21px;
            margin-bottom: 13px;
        }

        .portfolio-card p {
            color: #c7b8d5;
            line-height: 1.9;
            margin-bottom: 18px;
        }

        .portfolio-technology {
            display: inline-block;
            padding: 8px 12px;
            background: rgba(155, 32, 255, .12);
            border: 1px solid #49305d;
            border-radius: 7px;
            color: #d9b5f5;
            font-size: 12px;
            direction: ltr;
        }

        .portfolio-empty {
            max-width: 850px;
            margin: auto;
            padding: 55px 25px;
            text-align: center;
            background: #140D23;
            border: 1px dashed #63318a;
            border-radius: 16px;
        }

        .portfolio-empty-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .portfolio-empty h3 {
            color: #D45CFF;
            font-size: 23px;
            margin-bottom: 15px;
        }

        .portfolio-empty p {
            color: #c7b8d5;
            line-height: 2;
            max-width: 650px;
            margin: auto;
        }

        .portfolio-cta {
            padding: 55px 22px;
            text-align: center;
            background: linear-gradient(
                135deg,
                rgba(155, 32, 255, .18),
                rgba(212, 92, 255, .06)
            );
            border: 1px solid #63318a;
            border-radius: 18px;
        }

        .portfolio-cta h2 {
            font-size: 27px;
            margin-bottom: 15px;
        }

        .portfolio-cta p {
            color: #c7b8d5;
            line-height: 1.9;
        }

        .portfolio-button {
            display: inline-block;
            margin-top: 22px;
            padding: 14px 30px;
            border-radius: 8px;
            background: linear-gradient(90deg, #9B20FF, #D45CFF);
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: .3s;
        }

        .portfolio-button:hover {
            opacity: .85;
            transform: translateY(-3px);
        }

        @media (max-width: 650px) {
            .portfolio-page {
                padding: 35px 5%;
            }

            .portfolio-hero {
                padding: 45px 18px;
            }

            .portfolio-hero p {
                font-size: 14px;
            }

            .portfolio-section-title h2 {
                font-size: 24px;
            }

            .portfolio-empty {
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
        <a href="/portfolio">نمونه‌کارها</a>
        <a href="/about">درباره ما</a>
        <a href="/faq">پرسش‌های متداول</a>
        <a href="/contact">ارتباط با ما</a>
    </nav>

</header>


<main class="portfolio-page">

    <!-- Hero -->

    <section class="portfolio-hero">

        <h1>
            دنیای پروژه‌های
            <span>MRM</span>
        </h1>

        <p>
            در این بخش با حوزه‌های تخصصی فعالیت تیم MRM آشنا شوید.
            این صفحه برای نمایش پروژه‌های واقعی، دستاوردها و
            راهکارهای دیجیتال توسعه‌یافته توسط تیم ما آماده شده است.
        </p>

    </section>


    <!-- Categories -->

    <section class="portfolio-section">

        <div class="portfolio-section-title">

            <h2>حوزه‌های تخصصی پروژه‌ها</h2>

            <p>
                زمینه‌هایی که تیم ما در آن‌ها راهکارهای دیجیتال ارائه می‌کند.
            </p>

        </div>


        <div class="portfolio-grid">

            <?php foreach ($portfolioCategories as $category): ?>

                <article class="portfolio-card">

                    <div class="portfolio-icon">
                        <?= portfolioEscape($category['icon']) ?>
                    </div>

                    <h3>
                        <?= portfolioEscape($category['title']) ?>
                    </h3>

                    <p>
                        <?= portfolioEscape($category['description']) ?>
                    </p>

                    <span class="portfolio-technology">
                        <?= portfolioEscape($category['technology']) ?>
                    </span>

                </article>

            <?php endforeach; ?>

        </div>

    </section>


    <!-- Real Projects -->

    <section class="portfolio-section">

        <div class="portfolio-section-title">

            <h2>پروژه‌های منتخب</h2>

            <p>
                پروژه‌های واقعی و قابل ارائه تیم در این بخش نمایش داده خواهند شد.
            </p>

        </div>


        <div class="portfolio-empty">

            <div class="portfolio-empty-icon">
                🚀
            </div>

            <h3>
                پروژه‌های منتخب به‌زودی
            </h3>

            <p>
                بخش نمایش نمونه‌کارها آماده است.
                با تکمیل و تأیید پروژه‌های قابل انتشار،
                اطلاعات، تصاویر و جزئیات هر پروژه در این قسمت قرار خواهد گرفت.
            </p>

        </div>

    </section>


    <!-- Call To Action -->

    <section class="portfolio-cta">

        <h2>
            پروژه بعدی می‌تواند متعلق به شما باشد!
        </h2>

        <p>
            ایده خود را با ما در میان بگذارید تا برای تبدیل آن
            به یک راهکار دیجیتال برنامه‌ریزی کنیم.
        </p>

        <a href="/projects/request" class="portfolio-button">
            ثبت درخواست پروژه
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
