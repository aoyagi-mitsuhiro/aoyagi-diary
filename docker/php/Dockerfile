FROM php:8.5-apache

COPY --from=composer:2.9.8 /usr/bin/composer /usr/bin/composer
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y \
    unzip \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip

COPY docker/php/aoyagi.conf /etc/apache2/sites-available/aoyagi.conf
RUN a2ensite aoyagi.conf
RUN a2dissite 000-default.conf
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY html/composer.* ./
RUN composer install -d /var/www/html