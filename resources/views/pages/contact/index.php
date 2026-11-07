<?php

declare(strict_types=1);

$user = is_array($user ?? null) ? $user : [];

$fullName = (string) ($user['full_name'] ?? '');
$email = (string) ($user['email'] ?? '');
$whatsapp = (string) ($user['whatsapp_number'] ?? '');
$csrfToken = (string) ($csrfToken ?? '');

function publicContactEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05000D">

    <title>تماس با ما | MRM Technology Team</title>

    <meta
        name="description"
        content="با تیم MRM Technology Team تماس بگیرید و ایده یا درخواست پروژه تکنولوژی خود را با ما در میان بگذارید."
    >

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #05000D;
            color: #fff;
            font-family: Tahoma, Arial, sans-serif;
            line-height: 1.8;
        }

        a {
            text-decoration: none;
        }

        .container {
            width: 100%;
            max-width: 1150px;
            margin: auto;
            padding: 0 20px;
        }

        .navbar {
            background: #0D0718;
            border-bottom: 1px solid #352044;
        }

        .nav-content {
            min-height: 75px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .logo {
            color: #D45CFF;
            font-size: 20px;
            font-weight: bold;
        }

        .logo small {
            display: block;
            color: #a995b8;
            font-size: 10px;
            font-weight: normal;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #d8cce2;
            font-size: 13px;
            transition: .2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #D45CFF;
        }

        .hero {
            text-align: center;
            padding: 65px 15px 45px;
        }

        .hero-badge {
            display: inline-block;
            padding: 7px 17px;
            border: 1px solid #68418a;
            border-radius: 30px;
            background: #190e29;
            color: #D45CFF;
            font-size: 12px;
            margin-bottom: 18px;
        }

        .hero h1 {
            margin: 0 0 15px;
            font-size: 34px;
        }

        .hero h1 span {
            color: #D45CFF;
        }

        .hero p {
            max-width: 650px;
            margin: auto;
            color: #b9a9c8;
            font-size: 14px;
            line-height: 2.2;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 25px;
            align-items: start;
            padding-bottom: 65px;
        }

        .card {
            background: #140D23;
            border: 1px solid #49305d;
            border-radius: 15px;
            padding: 27px;
        }

        .card h2 {
            margin: 0 0 10px;
            color: #D45CFF;
            font-size: 21px;
        }

        .card-description {
            color: #a995b8;
            font-size: 12px;
            margin-bottom: 25px;
            line-height: 2;
        }

        .form-group {
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: #e4d7ed;
            font-size: 13px;
        }

        .required {
            color: #ff7eae;
        }

        .form-control {
            width: 100%;
            min-height: 46px;
            padding: 12px 14px;
            background: #0D0718;
            border: 1px solid #49305d;
            border-radius: 8px;
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            outline: none;
            transition: .2s;
        }

        .form-control:focus {
            border-color: #D45CFF;
            box-shadow: 0 0 0 3px #9b20ff22;
        }

        .form-control::placeholder {
            color: #776585;
        }

        textarea.form-control {
            min-height: 175px;
            resize: vertical;
            line-height: 2;
        }

        .submit-button {
            width: 100%;
            min-height: 49px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(100deg, #9b20ff, #d45cff);
            color: white;
            font-family: inherit;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: .2s;
        }

        .submit-button:hover {
            opacity: .9;
            transform: translateY(-2px);
        }

        .submit-button:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        .side-card {
            margin-bottom: 20px;
        }

        .contact-item {
            display: flex;
            gap: 15px;
            align-items: flex-start;
            padding: 17px 0;
            border-bottom: 1px solid #352044;
        }

        .contact-item:last-child {
            border-bottom: none;
        }

        .contact-icon {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #28143d;
            border: 1px solid #68418a;
            border-radius: 10px;
            color: #D45CFF;
            font-size: 18px;
        }

        .contact-item h3 {
            margin: 0 0 5px;
            color: #fff;
            font-size: 14px;
        }

        .contact-item p {
            margin: 0;
            color: #a995b8;
            font-size: 12px;
            line-height: 2;
        }

        .notice {
            margin-top: 20px;
            padding: 15px;
            background: #1b1230;
            border: 1px solid #49305d;
            border-radius: 9px;
            color: #cdb9dc;
            font-size: 12px;
            line-height: 2;
        }

        .notice strong {
            color: #D45CFF;
        }

        .footer {
            padding: 30px 15px;
            text-align: center;
            background: #0D0718;
            border-top: 1px solid #352044;
            color: #9685a5;
            font-size: 12px;
        }

        .footer strong {
            color: #D45CFF;
        }

        @media (max-width: 800px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .hero {
                padding-top: 45px;
            }

            .hero h1 {
                font-size: 27px;
            }

            .nav-content {
                padding-top: 15px;
                padding-bottom: 15px;
            }

            .nav-links {
                gap: 14px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding-left: 12px;
                padding-right: 12px;
            }

            .card {
                padding: 20px 15px;
            }

            .hero h1 {
                font-size: 24px;
            }

            .nav-links a {
                font-size: 11px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">

    <div class="container nav-content">

        <a href="/" class="logo">
            MRM Technology Team
            <small>Your Idea. Our Technology. Your Future.</small>
        </a>

        <nav class="nav-links">
            <a href="/">خانه</a>
            <a href="/services">خدمات</a>
            <a href="/projects/request">درخواست پروژه</a>
            <a href="/about">درباره ما</a>
            <a href="/contact" class="active">تماس با ما</a>
        </nav>

    </div>

</header>

<main>

    <section class="hero">

        <div class="hero-badge">
            ارتباط با تیم MRM
        </div>

        <h1>
            با ما <span>در تماس باشید</span>
        </h1>

        <p>
            سؤال، پیشنهاد یا ایده‌ای برای ساخت یک پروژه تکنولوژیکی دارید؟
            پیام خود را برای ما ارسال کنید. تیم ما پیام شما را دریافت و بررسی خواهد کرد.
        </p>

    </section>

    <section class="container contact-grid">

        <div class="card">

            <h2>ارسال پیام</h2>

            <p class="card-description">
                لطفاً اطلاعات خود و موضوع درخواست‌تان را وارد کنید.
                تمام فیلدهای دارای علامت ستاره الزامی هستند.
            </p>

            <form
                action="/contact"
                method="POST"
                id="contactForm"
            >

                <input
                    type="hidden"
                    name="_csrf_token"
                    value="<?= publicContactEscape($csrfToken) ?>"
                >

                <div class="form-group">

                    <label for="full_name">
                        نام کامل <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        class="form-control"
                        maxlength="150"
                        minlength="2"
                        placeholder="نام و نام خانوادگی"
                        value="<?= publicContactEscape($fullName) ?>"
                        autocomplete="name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="email">
                        آدرس ایمیل <span class="required">*</span>
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        maxlength="190"
                        placeholder="example@email.com"
                        value="<?= publicContactEscape($email) ?>"
                        autocomplete="email"
                        required
                        dir="ltr"
                    >

                </div>

                <div class="form-group">

                    <label for="whatsapp_number">
                        شماره واتساپ
                    </label>

                    <input
                        type="tel"
                        id="whatsapp_number"
                        name="whatsapp_number"
                        class="form-control"
                        maxlength="30"
                        placeholder="+93XXXXXXXXX"
                        value="<?= publicContactEscape($whatsapp) ?>"
                        autocomplete="tel"
                        dir="ltr"
                    >

                </div>

                <div class="form-group">

                    <label for="subject">
                        موضوع پیام <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="subject"
                        name="subject"
                        class="form-control"
                        maxlength="200"
                        placeholder="موضوع پیام خود را بنویسید"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="message">
                        متن پیام <span class="required">*</span>
                    </label>

                    <textarea
                        id="message"
                        name="message"
                        class="form-control"
                        maxlength="20000"
                        minlength="5"
                        placeholder="پیام خود را اینجا بنویسید..."
                        required
                    ></textarea>

                </div>

                <button
                    type="submit"
                    class="submit-button"
                    id="submitButton"
                >
                    ارسال پیام
                </button>

            </form>

            <div class="notice">
                <strong>توجه:</strong>
                پس از ارسال موفق، پیام شما در سیستم ثبت می‌شود.
                لطفاً آدرس ایمیل خود را با دقت وارد کنید.
            </div>

        </div>

        <aside>

            <div class="card side-card">

                <h2>راه‌های ارتباطی</h2>

                <p class="card-description">
                    برای ارتباط با تیم MRM از فرم پیام استفاده کنید.
                </p>

                <div class="contact-item">

                    <div class="contact-icon">✉</div>

                    <div>
                        <h3>ارتباط از طریق ایمیل</h3>
                        <p>
                            آدرس ایمیل خود را در فرم وارد کنید تا امکان پاسخ‌گویی به شما فراهم باشد.
                        </p>
                    </div>

                </div>

                <div class="contact-item">

                    <div class="contact-icon">⌕</div>

                    <div>
                        <h3>درخواست پروژه</h3>
                        <p>
                            برای ثبت یک درخواست تخصصی، از بخش درخواست پروژه استفاده کنید.
                        </p>
                        <a
                            href="/projects/request"
                            style="color:#D45CFF;font-size:12px;"
                        >
                            ثبت درخواست پروژه ←
                        </a>
                    </div>

                </div>

                <div class="contact-item">

                    <div class="contact-icon">◈</div>

                    <div>
                        <h3>پشتیبانی و پیگیری</h3>
                        <p>
                            پیام شما در صندوق پیام‌های تیم ثبت می‌شود تا توسط مدیریت بررسی گردد.
                        </p>
                    </div>

                </div>

            </div>

            <div class="card">

                <h2>MRM Technology Team</h2>

                <p class="card-description" style="margin-bottom:10px;">
                    Your Idea. Our Technology. Your Future.
                </p>

                <p style="color:#c7b8d5;font-size:13px;line-height:2.2;margin:0;">
                    ما در تلاش هستیم ایده‌های شما را با استفاده از راهکارهای
                    تکنولوژیکی به محصولات و خدمات دیجیتال تبدیل کنیم.
                </p>

            </div>

        </aside>

    </section>

</main>

<footer class="footer">

    <div class="container">
        © <?= date('Y') ?> <strong>MRM Technology Team</strong>
        — تمامی حقوق محفوظ است.
    </div>

</footer>

<script>
    document.getElementById('contactForm').addEventListener('submit', function () {
        const button = document.getElementById('submitButton');

        if (this.checkValidity()) {
            button.disabled = true;
            button.textContent = 'در حال ارسال پیام...';
        }
    });
</script>

</body>
</html>
