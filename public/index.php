<?php

declare(strict_types=1);

session_start();

define('APP_NAME', 'MRM Technology Team');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MRM Technology Team | Digital Solutions</title>

    <meta name="description" content="Professional application, database and website development services.">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #05000D;
            color: white;
            min-height: 100vh;
        }

        header {
            padding: 25px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #241333;
        }

        .logo {
            color: #D45CFF;
            font-size: 23px;
            font-weight: bold;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 22px;
            font-size: 14px;
        }

        nav a:hover {
            color: #D45CFF;
        }

        .hero {
            min-height: 75vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px 20px;
        }

        .hero h1 {
            font-size: clamp(35px, 7vw, 65px);
            margin-bottom: 20px;
        }

        .hero h1 span {
            color: #9B20FF;
        }

        .hero p {
            color: #c7b8d5;
            font-size: 18px;
            line-height: 1.8;
            max-width: 650px;
        }

        .button {
            display: inline-block;
            margin-top: 30px;
            padding: 14px 28px;
            background: linear-gradient(90deg, #9B20FF, #D45CFF);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .services {
            padding: 50px 8%;
            text-align: center;
        }

        .services h2 {
            font-size: 30px;
            margin-bottom: 30px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: #140D23;
            padding: 30px 20px;
            border: 1px solid #352044;
            border-radius: 12px;
        }

        .card h3 {
            color: #D45CFF;
            margin-bottom: 15px;
        }

        .card p {
            color: #c7b8d5;
            line-height: 1.7;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #9b8ca8;
            border-top: 1px solid #241333;
            margin-top: 40px;
        }

        @media (max-width: 650px) {
            header {
                flex-direction: column;
                gap: 18px;
            }

            nav a {
                margin: 0 7px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="logo">MRM Technology Team</div>

    <nav>
        <a href="#home">Home</a>
        <a href="#services">Services</a>
        <a href="#contact">Contact</a>
    </nav>
</header>

<section class="hero" id="home">

    <h1>
        Your Idea.<br>
        <span>Our Technology.</span>
    </h1>

    <p>
        We transform your ideas into powerful digital solutions.
        From applications and databases to professional websites,
        we help build your digital future.
    </p>

    <a href="#services" class="button">
        Explore Our Services
    </a>

</section>

<section class="services" id="services">

    <h2>Our Main Services</h2>

    <div class="cards">

        <div class="card">
            <h3>Application Development</h3>
            <p>
                Android, iOS and Web application development
                based on your requirements.
            </p>
        </div>

        <div class="card">
            <h3>Database Development</h3>
            <p>
                Professional database design, development
                and management solutions.
            </p>
        </div>

        <div class="card">
            <h3>Website Development</h3>
            <p>
                Business, educational, e-commerce and
                custom website development.
            </p>
        </div>

    </div>

</section>

<footer id="contact">
    <p>MRM Technology Team</p>
    <p>Your Idea. Our Technology. Your Future.</p>
</footer>

</body>
</html>
