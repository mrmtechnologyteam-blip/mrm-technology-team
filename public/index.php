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

    <link rel="stylesheet" href="assets/css/style.css">
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

<!-- Main JavaScript -->
<script src="assets/js/app.js"></script>

</body>
</html>
