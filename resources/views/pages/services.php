<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

function servicesEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>خدمات ما | <?= servicesEscape($appName) ?></title>

    <meta name="description"
          content="خدمات تخصصی MRM Technology Team در زمینه توسعه اپلیکیشن، طراحی دیتابیس و توسعه وب‌سایت.">

    <meta name="theme-color" content="#05000D">

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
        .services-page {
            padding: 60px 8%;
        }

        .services-hero {
            text-align: center;
            padding: 65px 20px;
            background: linear-gradient(
                135deg,
                rgba(155, 32, 255, .16),
                rgba(212, 92, 255, .05)
            );
            border: 1px solid #352044;
            border-radius: 18px;
            margin-bottom: 55px;
        }

        .services-hero h1 {
            font-size: clamp(30px, 5vw, 48px);
            margin-bottom: 20px;
        }

        .services-hero h1 span {
            color: #D45CFF;
        }

        .services-hero p {
            color: #c7b8d5;
            line-height: 2;
            max-width: 750px;
            margin: auto;
            font-size: 17px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 35px;
        }

        .section-title h2 {
            font-size: 30px;
            margin-bottom: 12px;
        }

        .section-title p {
            color: #b9a9c7;
            line-height: 1.8;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }

        .service-card {
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 15px;
            padding: 30px 25px;
            transition: .3s ease;
        }

        .service-card:hover {
            transform: translateY(-7px);
            border-color: #9B20FF;
            box-shadow: 0 12px 35px rgba(155, 32, 255, .12);
        }

        .service-icon {
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

        .service-card h3 {
            color: #D45CFF;
            font-size: 22px;
            margin-bottom: 15px;
        }

        .service-card p {
            color: #c7b8d5;
            line-height: 1.9;
            margin-bottom: 20px;
        }

        .service-card ul {
            padding-right: 20px;
            color: #e0d5e9;
            line-height: 2.2;
            font-size: 14px;
        }

        .process-section {
            margin-top: 75px;
        }

        .process-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .process-card {
            background: #140D23;
            border: 1px solid #352044;
            padding: 25px 20px;
            border-radius: 12px;
            text-align: center;
        }

        .process-number {
            width: 45px;
            height: 45px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #9B20FF, #D45CFF);
            border-radius: 50%;
            font-weight: bold;
            font-size: 20px;
        }

        .process-card h3 {
            color: #D45CFF;
            margin-bottom: 12px;
        }

        .process-card p {
            color: #c7b8d5;
            line-height: 1.8;
            font-size: 14px;
        }

        .services-cta {
            margin-top: 75px;
            padding: 55px 20px;
            text-align: center;
            background: linear-gradient(
                135deg,
                rgba(155, 32, 255, .18),
                rgba(212, 92, 255, .07)
            );
            border: 1px solid #63318a;
            border-radius: 18px;
        }

        .services-cta h2 {
            font-size: 28px;
            margin-bottom: 15px;
        }

        .services-cta p {
            color: #c7b8d5;
            line-height: 1.9;
            margin-bottom: 10px;
        }

        .service-button {
            display: inline-block;
            margin-top: 20px;
            padding: 14px 30px;
            background: linear-gradient(90deg, #9B20FF, #D45CFF);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: .3s;
        }

        .service-button:hover {
            opacity: .85;
            transform: translateY(-3px);
        }

        @media (max-width: 650px) {
            .services-page {
                padding: 35px 5%;
            }

            .services-hero {
                padding: 45px 18px;
            }

            .services-hero p {
                font-size: 15px;
            }

            .section-title h2 {
                font-size: 25px;
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
        <a href="/contact">ارتباط با ما</a>
    </nav>
</header>

<main class="services-page">

    <!-- Hero -->

    <section class="services-hero">

        <h1>
            خدمات فناوری
            <span>MRM</span>
        </h1>

        <p>
            ما با استفاده از فناوری‌های مدرن، ایده‌ها و نیازهای شما
            را به راهکارهای دیجیتال تبدیل می‌کنیم.
            هدف ما ساخت سیستم‌هایی کاربردی، قابل اعتماد و توسعه‌پذیر است.
        </p>

    </section>


    <!-- Main Services -->

    <section>

        <div class="section-title">
            <h2>خدمات تخصصی ما</h2>

            <p>
                سه حوزه اصلی فعالیت MRM Technology Team
            </p>
        </div>

        <div class="service-grid">

            <!-- Application Development -->

            <article class="service-card">

                <div class="service-icon">📱</div>

                <h3>توسعه اپلیکیشن</h3>

                <p>
                    طراحی و توسعه اپلیکیشن‌های موبایل و نرم‌افزارهای
                    تحت وب با توجه به نیازها و اهداف پروژه شما.
                </p>

                <ul>
                    <li>طراحی رابط کاربری</li>
                    <li>توسعه اپلیکیشن‌های موبایل</li>
                    <li>توسعه سیستم‌های اختصاصی</li>
                    <li>پیاده‌سازی قابلیت‌های مورد نیاز</li>
                    <li>بهبود عملکرد و تجربه کاربری</li>
                </ul>

            </article>


            <!-- Database Development -->

            <article class="service-card">

                <div class="service-icon">🗄️</div>

                <h3>طراحی و توسعه دیتابیس</h3>

                <p>
                    طراحی و پیاده‌سازی ساختارهای پایگاه داده برای
                    ذخیره‌سازی، مدیریت و بازیابی مؤثر اطلاعات.
                </p>

                <ul>
                    <li>تحلیل نیازمندی‌های اطلاعاتی</li>
                    <li>طراحی ساختار پایگاه داده</li>
                    <li>طراحی جداول و روابط</li>
                    <li>مدیریت و سازمان‌دهی اطلاعات</li>
                    <li>بهینه‌سازی پرس‌وجوها</li>
                </ul>

            </article>


            <!-- Website Development -->

            <article class="service-card">

                <div class="service-icon">🌐</div>

                <h3>توسعه وب‌سایت</h3>

                <p>
                    طراحی و توسعه وب‌سایت‌ها و سیستم‌های تحت وب
                    متناسب با نیازهای اشخاص، شرکت‌ها و سازمان‌ها.
                </p>

                <ul>
                    <li>طراحی وب‌سایت شرکتی</li>
                    <li>ساخت وب‌سایت‌های آموزشی</li>
                    <li>توسعه سیستم‌های مدیریتی</li>
                    <li>طراحی رابط کاربری واکنش‌گرا</li>
                    <li>توسعه امکانات اختصاصی وب</li>
                </ul>

            </article>

        </div>

    </section>


    <!-- Work Process -->

    <section class="process-section">

        <div class="section-title">
            <h2>روند انجام پروژه‌ها</h2>

            <p>
                پروژه‌های ما بر اساس یک روند مشخص و مرحله‌به‌مرحله پیش می‌روند.
            </p>
        </div>

        <div class="process-grid">

            <article class="process-card">

                <div class="process-number">۱</div>

                <h3>ثبت درخواست</h3>

                <p>
                    مشتری درخواست پروژه و اطلاعات اولیه مورد نیاز
                    را ثبت می‌کند.
                </p>

            </article>


            <article class="process-card">

                <div class="process-number">۲</div>

                <h3>بررسی نیازمندی‌ها</h3>

                <p>
                    نیازها، امکانات، اهداف و جزئیات پروژه
                    مورد بررسی قرار می‌گیرند.
                </p>

            </article>


            <article class="process-card">

                <div class="process-number">۳</div>

                <h3>برنامه‌ریزی و توسعه</h3>

                <p>
                    پس از مشخص‌شدن جزئیات، روند طراحی و
                    توسعه پروژه آغاز می‌شود.
                </p>

            </article>


            <article class="process-card">

                <div class="process-number">۴</div>

                <h3>تحویل پروژه</h3>

                <p>
                    نتیجه نهایی مطابق توافق و نیازمندی‌های
                    تأییدشده به مشتری ارائه می‌شود.
                </p>

            </article>

        </div>

    </section>


    <!-- Call To Action -->

    <section class="services-cta">

        <h2>ایده‌ای برای ساخت دارید؟</h2>

        <p>
            اگر به دنبال توسعه یک اپلیکیشن، طراحی دیتابیس
            یا ساخت یک وب‌سایت هستید، درخواست پروژه خود را ثبت کنید.
        </p>

        <p>
            تیم MRM آماده بررسی ایده و نیازمندی‌های شما است.
        </p>

        <a href="/projects/request" class="service-button">
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
