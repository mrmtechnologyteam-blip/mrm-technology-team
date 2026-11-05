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

    <title><?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?> | راهکارهای دیجیتال</title>

    <meta name="description" content="تیم فناوری MRM؛ ارائه‌دهنده خدمات توسعه اپلیکیشن، طراحی دیتابیس و طراحی وب‌سایت.">

    <meta name="theme-color" content="#05000D">

    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>

<header>
    <div class="logo">
        MRM Technology Team
    </div>

    <nav>
        <a href="#home">خانه</a>
        <a href="#services">خدمات</a>
        <a href="#about">درباره ما</a>
        <a href="#contact">ارتباط با ما</a>
    </nav>
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

        <a href="#services" class="button">
            مشاهده خدمات ما
        </a>

    </section>

    <section class="services" id="services">

        <h2>خدمات اصلی ما</h2>

        <div class="cards">

            <div class="card">
                <h3>توسعه اپلیکیشن</h3>

                <p>
                    طراحی و توسعه اپلیکیشن‌های موبایل و وب
                    متناسب با نیازها و اهداف کسب‌وکار شما.
                </p>
            </div>

            <div class="card">
                <h3>توسعه دیتابیس</h3>

                <p>
                    طراحی، پیاده‌سازی و مدیریت پایگاه‌های داده
                    امن، منظم و قابل توسعه.
                </p>
            </div>

            <div class="card">
                <h3>توسعه وب‌سایت</h3>

                <p>
                    طراحی وب‌سایت‌های شرکتی، آموزشی، فروشگاهی
                    و سیستم‌های اختصاصی تحت وب.
                </p>
            </div>

        </div>

    </section>

    <section class="services" id="about">

        <h2>درباره MRM Technology Team</h2>

        <p>
            هدف ما ارائه راهکارهای فناوری کاربردی، قابل اعتماد
            و متناسب با نیاز مشتریان است. ما تلاش می‌کنیم
            ایده‌ها را به محصولات دیجیتال واقعی تبدیل کنیم.
        </p>

    </section>

</main>

<footer id="contact">

    <p>MRM Technology Team</p>

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
