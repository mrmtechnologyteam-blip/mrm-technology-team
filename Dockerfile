FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/mrm

COPY composer.json ./

RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

COPY . .

RUN a2enmod rewrite headers \
    && sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && printf '%s\n' \
       '<VirtualHost *:10000>' \
       '    ServerName localhost' \
       '    DocumentRoot /var/www/mrm/public' \
       '    <Directory /var/www/mrm/public>' \
       '        Options -Indexes +FollowSymLinks' \
       '        AllowOverride All' \
       '        Require all granted' \
       '    </Directory>' \
       '</VirtualHost>' \
       > /etc/apache2/sites-available/000-default.conf \
    && mkdir -p storage/cache storage/logs storage/uploads \
    && chown -R www-data:www-data storage \
    && chmod -R 775 storage

EXPOSE 10000

CMD ["apache2-foreground"]
