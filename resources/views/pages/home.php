<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

$isLoggedIn = !empty($_SESSION['logged_in']);

$userRole = (string) ($_SESSION['user_role'] ?? '');

$dashboardUrl = $userRole === 'admin'
    ? '/admin/dashboard'
    : '/dashboard';

function homeEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

$csrfToken = function_exists('csrf_token')
    ? csrf_token()
    : '';

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= homeEscape($appName) ?> | راهکارهای دیجیتال
    </title>

    <meta
        name="description"
        content="MRM Technology Team؛ توسعه اپلیکیشن، طراحی دیتابیس و طراحی وب‌سایت."
    >

    <meta name="theme-color" content="#05000D">

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: #05000D;
            color: #ffffff;
            font-family: Tahoma, Arial, sans-serif;
            line-height: 1.8;
        }

        a {
            text-decoration: none;
        }

        .site-header {
            width: 100%;
            padding: 18px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            background: #0D0718;
            border-bottom: 1px solid #29183A;
        }

        .logo {
            color: #D45CFF;
            font-size: 22px;
            font-weight: bold;
        }

        .navigation {
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
        }

        .navigation a {
            color: #e6d9ef;
            font-size: 14px;
            transition: .2s;
        }

        .navigation a:hover {
            color: #D45CFF;
        }

        .account-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .account-button {
            display: inline-block;
            padding: 9px 17px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            transition: .2s;
        }

        .register-button {
            color: #ffffff;
            background: linear-gradient(
                135deg,
                #9b20ff,
                #d45cff
            );
        }

        .login-button {
            color: #D45CFF;
            border: 1px solid #9b20ff;
        }

        .dashboard-button {
            color: white;
            background: #6f4cff;
        }

        .logout-button {
            border: 1px solid #814b91;
            background: transparent;
            color: #e8c9ff;
            cursor: pointer;
            font-family: inherit;
        }

        .account-button:hover {
            opacity: .8;
            transform: translateY(-2px);
        }

        .hero {
            min-height: 540px;
            padding: 100px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background:
                radial-gradient(
                    circle at center,
                    #241039 0%,
                    #0D0718 45%,
                    #05000D 100%
                );
        }

        .hero h1 {
            font-size: clamp(32px, 6vw, 55px);
            margin: 0 0 20px;
        }

        .hero h1 span {
            display: block;
            color: #D45CFF;
        }

        .hero p {
            max-width: 700px;
            color: #c9b8d5;
            font-size: 17px;
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .main-button {
            display: inline-block;
            padding: 13px 25px;
            border-radius: 9px;
            color: white;
            background: linear-gradient(
                135deg,
                #9b20ff,
                #d45cff
            );
            font-weight: bold;
        }

        .secondary-button {
            display: inline-block;
            padding: 13px 25px;
            border: 1px solid #9b20ff;
            border-radius: 9px;
            color: #e8c9ff;
        }

        .section {
            padding: 75px 7%;
            text-align: center;
        }

        .section-title {
            font-size: 30px;
            margin-bottom: 12px;
        }

        .section-description {
            color: #bda9ca;
            max-width: 650px;
            margin: 0 auto 40px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(220px, 1fr)
            );
            gap: 22px;
            max-width: 1100px;
            margin: auto;
        }

        .card {
            background: #140D23;
            border: 1px solid #352044;
            padding: 30px 22px;
            border-radius: 14px;
            transition: .25s;
        }

        .card:hover {
            transform: translateY(-6px);
            border-color: #9b20ff;
        }

        .card-icon {
            font-size: 35px;
            margin-bottom: 12px;
        }

        .card h3 {
            color: #D45CFF;
            margin-bottom: 12px;
        }

        .card p {
            color: #c7b8d5;
            font-size: 14px;
        }

        .about-section {
            background: #0D0718;
        }

        .about-content {
            max-width: 800px;
            margin: auto;
            color: #c7b8d5;
        }

        .contact-section {
            padding: 65px 20px;
            text-align: center;
        }

        .contact-section p {
            color: #c7b8d5;
        }

        .site-footer {
            padding: 30px 15px;
            text-align: center;
            background: #0D0718;
            border-top: 1px solid #29183A;
            color: #bda9ca;
            font-size: 13px;
        }

        .site-footer strong {
            color: #D45CFF;
        }

        .mobile-account {
            display: none;
        }

        @media (max-width: 850px) {

            .site-header {
                justify-content: center;
                text-align: center;
            }

            .navigation {
                justify-content: center;
            }

            .account-actions {
                justify-content: center;
            }

        }

        @media (max-width: 600px) {

            .site-header {
                padding: 18px 12px;
            }

            .logo {
                width: 100%;
                font-size: 19px;
            }

            .navigation {
                gap: 14px;
            }

            .navigation a {
                font-size: 12px;
            }

            .hero {
                min-height: 450px;
                padding: 70px 18px;
            }

            .hero p {
                font-size: 14px;
            }

            .section {
                padding: 55px 18px;
            }

            .section-title {
                font-size: 25px;
            }

            .account-button {
                padding: 8px 12px;
            }

        }

    </style>

</head>

<body>

<header class="site-header">

    <div class="logo">
        MRM Technology Team
    </div>

    <nav class="navigation">

        <a href="#home">خانه</a>

        <a href="#services">خدمات</a>

        <a href="#about">درباره ما</a>

        <a href="#contact">ارتباط با ما</a>

    </nav>

    <div class="account-actions">

        <?php if ($isLoggedIn): ?>

            <a
                href="<?= homeEscape($dashboardUrl) ?>"
                class="account-button dashboard-button"
            >
                پنل کاربری
            </a>

            <form action="/logout" method="POST">

                <input
                    type="hidden"
                    name="_csrf_token"
                    value="<?= homeEscape($csrfToken) ?>"
                >

                <button
                    type="submit"
                    class="account-button logout-button"
                >
                    خروج
                </button>

            </form>

        <?php else: ?>

            <a
                href="/register"
                class="account-button register-button"
            >
                ایجاد حساب کاربری
            </a>

            <a
                href="/login"
                class="account-button login-button"
            >
                ورود
            </a>

        <?php endif; ?>

    </div>

</header>


<main>

    <section class="hero" id="home">

        <h1>
            ایده شما،
            <span>فناوری ما</span>
        </h1>

        <p>
            ما ایده‌های شما را به راهکارهای دیجیتال تبدیل می‌کنیم.
            از توسعه اپلیکیشن و طراحی دیتابیس تا ساخت وب‌سایت‌های
            حرفه‌ای، همراه شما در مسیر تحول دیجیتال هستیم.
        </p>

        <div class="hero-buttons">

            <a
                href="#services"
                class="main-button"
            >
                مشاهده خدمات
            </a>

            <?php if (!$isLoggedIn): ?>

                <a
                    href="/register"
                    class="secondary-button"
                >
                    شروع همکاری با ما
                </a>

            <?php else: ?>

                <a
                    href="<?= homeEscape($dashboardUrl) ?>"
                    class="secondary-button"
                >
                    ورود به پنل کاربری
                </a>

            <?php endif; ?>

        </div>

    </section>


    <section class="section" id="services">

        <h2 class="section-title">
            خدمات اصلی ما
        </h2>

        <p class="section-description">
            راهکارهای تخصصی فناوری اطلاعات برای توسعه کسب‌وکارها
            و تبدیل ایده‌ها به محصولات دیجیتال.
        </p>

        <div class="cards">

            <div class="card">

                <div class="card-icon">📱</div>

                <h3>
                    توسعه اپلیکیشن
                </h3>

                <p>
                    طراحی و توسعه اپلیکیشن‌های موبایل و وب
                    متناسب با نیازها و اهداف کسب‌وکار شما.
                </p>

            </div>


            <div class="card">

                <div class="card-icon">🗄️</div>

                <h3>
                    طراحی دیتابیس
                </h3>

                <p>
                    طراحی، پیاده‌سازی و مدیریت پایگاه‌های داده
                    امن، منظم و قابل توسعه.
                </p>

            </div>


            <div class="card">

                <div class="card-icon">🌐</div>

                <h3>
                    توسعه وب‌سایت
                </h3>

                <p>
                    طراحی وب‌سایت‌های شرکتی، آموزشی، فروشگاهی
                    و سیستم‌های اختصاصی تحت وب.
                </p>

            </div>

        </div>

    </section>


    <section class="section about-section" id="about">

        <h2 class="section-title">
            درباره MRM Technology Team
        </h2>

        <div class="about-content">

            <p>
                MRM Technology Team یک تیم فناوری است که با هدف
                ارائه راهکارهای دیجیتال و توسعه سیستم‌های نرم‌افزاری
                فعالیت می‌کند.
            </p>

            <p>
                هدف ما ایجاد محصولات کاربردی، قابل اعتماد و متناسب
                با نیاز مشتریان است. ما تلاش می‌کنیم ایده‌های شما
                را به محصولات دیجیتال واقعی تبدیل کنیم.
            </p>

            <h3>
                Your Idea. Our Technology. Your Future.
            </h3>

        </div>

    </section>


    <section class="contact-section" id="contact">

        <h2 class="section-title">
            آماده شروع یک پروژه هستید؟
        </h2>

        <p>
            حساب کاربری بسازید و درخواست پروژه خود را برای ما ارسال کنید.
        </p>

        <?php if ($isLoggedIn): ?>

            <a
                href="/projects/create"
                class="main-button"
            >
                ثبت درخواست پروژه
            </a>

        <?php else: ?>

            <a
                href="/register"
                class="main-button"
            >
                ایجاد حساب و شروع همکاری
            </a>

        <?php endif; ?>

    </section>

</main>


<footer class="site-footer">

    <p>
        <strong>MRM Technology Team</strong>
    </p>

    <p>
        ایده شما، فناوری ما، آینده شما.
    </p>

    <p>
        تمامی حقوق محفوظ است.
    </p>

</footer>


<script src="/assets/js/app.js" defer></script>

</body>

</html>
