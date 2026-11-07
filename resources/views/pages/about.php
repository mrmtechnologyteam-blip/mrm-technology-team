<?php

declare(strict_types=1);

$appName = defined('APP_NAME')
    ? APP_NAME
    : 'MRM Technology Team';

?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#05000D">

    <title>
        درباره ما | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?>
    </title>

    <meta
        name="description"
        content="با MRM Technology Team، مأموریت، چشم‌انداز، ارزش‌ها و حوزه‌های تخصصی ما آشنا شوید."
    >

    <link rel="stylesheet" href="/assets/css/style.css">

    <style>
        body {
            line-height: 1.9;
        }

        header {
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo small {
            display: block;
            color: #a995b8;
            font-size: 10px;
            font-weight: normal;
            margin-top: 5px;
        }

        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }

        nav a {
            margin: 0;
        }

        .about-hero {
            padding: 85px 8% 70px;
            text-align: center;
            background:
                radial-gradient(circle at 50% 30%, #351052 0%, #140D23 35%, #05000D 75%);
        }

        .badge {
            display: inline-block;
            padding: 7px 18px;
            border: 1px solid #68418a;
            border-radius: 30px;
            color: #D45CFF;
            background: #190e29;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .about-hero h1 {
            font-size: clamp(30px, 6vw, 52px);
            margin-bottom: 20px;
        }

        .about-hero h1 span {
            color: #D45CFF;
        }

        .about-hero p {
            max-width: 780px;
            margin: auto;
            color: #c7b8d5;
            font-size: 16px;
            line-height: 2.2;
        }

        .section {
            padding: 65px 8%;
            text-align: center;
        }

        .section-title {
            font-size: 29px;
            margin-bottom: 15px;
        }

        .section-title span {
            color: #D45CFF;
        }

        .section-description {
            max-width: 760px;
            margin: 0 auto 35px;
            color: #b9a9c8;
            font-size: 14px;
            line-height: 2.2;
        }

        .story {
            max-width: 900px;
            margin: auto;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 14px;
            padding: 35px;
            text-align: right;
        }

        .story p {
            color: #c7b8d5;
            font-size: 14px;
            line-height: 2.3;
            margin-bottom: 15px;
        }

        .story p:last-child {
            margin-bottom: 0;
        }

        .cards {
            max-width: 1100px;
            margin: auto;
        }

        .card {
            text-align: right;
            transition: transform .2s, border-color .2s;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: #9B20FF;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #28143d;
            border: 1px solid #68418a;
            border-radius: 11px;
            color: #D45CFF;
            font-size: 22px;
            margin-bottom: 18px;
        }

        .card h3 {
            font-size: 17px;
        }

        .card p {
            font-size: 13px;
        }

        .dark-section {
            background: #0D0718;
            border-top: 1px solid #241333;
            border-bottom: 1px solid #241333;
        }

        .principles {
            max-width: 1000px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 18px;
        }

        .principle {
            padding: 22px;
            background: #140D23;
            border: 1px solid #352044;
            border-radius: 12px;
        }

        .principle h3 {
            color: #D45CFF;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .principle p {
            color: #b9a9c8;
            font-size: 13px;
            line-height: 2;
        }

        .cta {
            padding: 65px 20px;
            text-align: center;
            background: linear-gradient(120deg, #25103b, #140D23);
            border-top: 1px solid #49305d;
        }

        .cta h2 {
            font-size: 27px;
            margin-bottom: 15px;
        }

        .cta p {
            color: #c7b8d5;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .cta .button {
            margin-top: 15px;
        }

        footer {
            line-height: 2;
        }

        footer strong {
            color: #D45CFF;
        }

        @media (max-width: 650px) {
            .about-hero {
                padding: 60px 20px;
            }

            .about-hero p {
                font-size: 14px;
            }

            .section {
                padding: 45px 18px;
            }

            .story {
                padding: 22px;
            }

            .section-title {
                font-size: 24px;
            }

            nav {
                justify-content: center;
                gap: 12px;
            }
        }
    </style>

</head>

<body>

<header>

    <a href="/" class="logo" style="text-decoration:none;">
        MRM Technology Team
        <small>Your Idea. Our Technology. Your Future.</small>
    </a>

    <nav>
        <a href="/">خانه</a>
        <a href="/services">خدمات</a>
        <a href="/projects/request">درخواست پروژه</a>
        <a href="/about" style="color:#D45CFF;">درباره ما</a>
        <a href="/contact">تماس با ما</a>
    </nav>

</header>

<main>

    <!-- Hero Section -->

    <section class="about-hero">

        <div class="badge">
            درباره MRM Technology Team
        </div>

        <h1>
            ما ایده‌ها را به
            <span>راهکارهای دیجیتال</span>
            تبدیل می‌کنیم
        </h1>

        <p>
            MRM Technology Team با هدف ارائه راهکارهای تکنولوژیکی
            و توسعه محصولات دیجیتال شکل گرفته است.
            تمرکز ما بر طراحی و توسعه سیستم‌هایی است که
            نیازهای واقعی افراد و کسب‌وکارها را برطرف کنند.
        </p>

    </section>


    <!-- Introduction -->

    <section class="section">

        <h2 class="section-title">
            ما <span>که هستیم؟</span>
        </h2>

        <p class="section-description">
            آشنایی با هویت، فعالیت‌ها و هدف اصلی تیم فناوری MRM
        </p>

        <div class="story">

            <p>
                MRM Technology Team یک مجموعه فعال در حوزه فناوری
                اطلاعات و راهکارهای دیجیتال است که بر طراحی،
                توسعه و پیاده‌سازی محصولات نرم‌افزاری تمرکز دارد.
            </p>

            <p>
                ما به این باور داریم که فناوری باید مشکلات واقعی
                را حل کند. به همین دلیل، در هر پروژه ابتدا نیازها
                و اهداف بررسی می‌شوند و سپس راهکار مناسب طراحی
                و توسعه داده می‌شود.
            </p>

            <p>
                حوزه‌های اصلی فعالیت ما شامل توسعه اپلیکیشن،
                طراحی و مدیریت پایگاه داده و توسعه وب‌سایت‌ها
                و سیستم‌های تحت وب است.
            </p>

            <p>
                هدف ما ایجاد راهکارهایی کاربردی، قابل توسعه،
                امن و متناسب با نیاز مشتریان است.
            </p>

        </div>

    </section>


    <!-- Mission and Vision -->

    <section class="section dark-section">

        <h2 class="section-title">
            مأموریت و <span>چشم‌انداز</span>
        </h2>

        <p class="section-description">
            مسیری که برای ساخت آینده دیجیتال دنبال می‌کنیم.
        </p>

        <div class="cards">

            <div class="card">

                <div class="card-icon">◎</div>

                <h3>مأموریت ما</h3>

                <p>
                    ارائه خدمات و محصولات نرم‌افزاری باکیفیت،
                    طراحی راهکارهای متناسب با نیاز مشتریان
                    و تبدیل ایده‌ها به پروژه‌های دیجیتال قابل استفاده.
                </p>

            </div>

            <div class="card">

                <div class="card-icon">✦</div>

                <h3>چشم‌انداز ما</h3>

                <p>
                    تبدیل شدن به یک تیم فناوری قابل اعتماد
                    و توسعه‌دهنده راهکارهای نوآورانه که
                    در رشد دیجیتال افراد و کسب‌وکارها نقش مؤثر داشته باشد.
                </p>

            </div>

            <div class="card">

                <div class="card-icon">◇</div>

                <h3>هدف ما</h3>

                <p>
                    ایجاد تجربه‌ای حرفه‌ای در همکاری با مشتریان
                    و ساخت محصولات دیجیتال کاربردی، پایدار
                    و قابل توسعه.
                </p>

            </div>

        </div>

    </section>


    <!-- Services -->

    <section class="section">

        <h2 class="section-title">
            حوزه‌های <span>تخصصی ما</span>
        </h2>

        <p class="section-description">
            تمرکز اصلی فعالیت‌های MRM Technology Team
            بر سه حوزه مهم توسعه نرم‌افزار است.
        </p>

        <div class="cards">

            <div class="card">

                <div class="card-icon">▣</div>

                <h3>توسعه اپلیکیشن</h3>

                <p>
                    طراحی و توسعه اپلیکیشن‌های کاربردی
                    برای پاسخ‌گویی به نیازهای مختلف کاربران
                    و کسب‌وکارها.
                </p>

            </div>

            <div class="card">

                <div class="card-icon">▤</div>

                <h3>طراحی دیتابیس</h3>

                <p>
                    طراحی ساختارهای منظم پایگاه داده،
                    مدیریت اطلاعات و ایجاد سیستم‌های
                    ذخیره‌سازی قابل اعتماد.
                </p>

            </div>

            <div class="card">

                <div class="card-icon">⌘</div>

                <h3>توسعه وب‌سایت</h3>

                <p>
                    طراحی و توسعه وب‌سایت‌های حرفه‌ای،
                    سیستم‌های مدیریتی و پلتفرم‌های
                    اختصاصی تحت وب.
                </p>

            </div>

        </div>

    </section>


    <!-- Values -->

    <section class="section dark-section">

        <h2 class="section-title">
            ارزش‌های <span>ما</span>
        </h2>

        <p class="section-description">
            اصولی که در طراحی، توسعه و همکاری‌های خود دنبال می‌کنیم.
        </p>

        <div class="principles">

            <div class="principle">

                <h3>کیفیت</h3>

                <p>
                    تلاش برای ارائه محصولات و خدمات باکیفیت
                    و توجه به جزئیات در روند توسعه.
                </p>

            </div>

            <div class="principle">

                <h3>نوآوری</h3>

                <p>
                    استفاده از ایده‌ها و روش‌های جدید
                    برای ساخت راهکارهای بهتر.
                </p>

            </div>

            <div class="principle">

                <h3>مسئولیت‌پذیری</h3>

                <p>
                    توجه به نیازها و انتظارات مشتریان
                    و تعهد به انجام مسئولانه پروژه‌ها.
                </p>

            </div>

            <div class="principle">

                <h3>یادگیری مستمر</h3>

                <p>
                    توسعه دانش و مهارت‌ها و همراهی با
                    پیشرفت‌های دنیای فناوری.
                </p>

            </div>

            <div class="principle">

                <h3>همکاری</h3>

                <p>
                    ایجاد ارتباط مؤثر و همکاری سازنده
                    برای رسیدن به اهداف مشترک.
                </p>

            </div>

            <div class="principle">

                <h3>اعتماد</h3>

                <p>
                    اهمیت دادن به شفافیت، احترام و
                    حفظ اعتماد در روابط کاری.
                </p>

            </div>

        </div>

    </section>


    <!-- CTA -->

    <section class="cta">

        <h2>
            ایده‌ای برای ساختن دارید؟
        </h2>

        <p>
            ما آماده‌ایم درباره ایده و پروژه شما بیشتر بدانیم.
        </p>

        <a href="/projects/request" class="button">
            درخواست پروژه
        </a>

        <a
            href="/contact"
            class="button"
            style="background:transparent;border:1px solid #D45CFF;margin-right:10px;"
        >
            تماس با ما
        </a>

    </section>

</main>


<footer>

    <p>
        <strong>MRM Technology Team</strong>
    </p>

    <p>
        Your Idea. Our Technology. Your Future.
    </p>

    <p>
        © <?= date('Y') ?> تمامی حقوق محفوظ است.
    </p>

</footer>

<script src="/assets/js/app.js" defer></script>

</body>
</html>
