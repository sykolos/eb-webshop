FROM php:8.2-apache

# Node.js + Composer + Redis build előkészítés
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    build-essential ca-certificates nodejs autoconf pkg-config

# Redis extension manuális telepítése GitHub-ról (PECL fallback)
RUN git clone https://github.com/phpredis/phpredis.git /usr/src/phpredis && \
    cd /usr/src/phpredis && \
    phpize && \
    ./configure && \
    make && make install && \
    docker-php-ext-enable redis

# MySQL PDO driver telepítése
RUN docker-php-ext-install pdo pdo_mysql
# GD library telepítése
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libwebp-dev && \
    docker-php-ext-configure gd --with-jpeg --with-webp && \
    docker-php-ext-install gd


# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Laravel app másolása
COPY ./ /var/www
WORKDIR /var/www

RUN chown -R www-data:www-data /var/www && a2enmod rewrite
COPY apache.conf /etc/apache2/sites-available/000-default.conf
