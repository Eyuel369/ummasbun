# syntax=docker/dockerfile:1

FROM composer:2.7 AS vendor
WORKDIR /app
RUN set -eux; \
    if command -v apk >/dev/null; then \
        apk add --no-cache \
            libzip-dev zlib-dev \
            libpng-dev libjpeg-turbo-dev freetype-dev \
            libxml2-dev oniguruma-dev; \
    else \
        apt-get update; \
        apt-get install -y --no-install-recommends \
            libzip-dev zlib1g-dev \
            libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
            libxml2-dev libonig-dev; \
        rm -rf /var/lib/apt/lists/*; \
    fi; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install gd mbstring zip
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

FROM node:20-bullseye AS frontend
WORKDIR /app
COPY package.json package-lock.json vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ resources/
COPY public/ public/
COPY bootstrap/ bootstrap/
RUN npm ci
RUN npm run build

FROM php:8.2-apache AS app
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev zlib1g-dev \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        libxml2-dev libonig-dev libpq-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql gd mbstring zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PORT=10000
WORKDIR /var/www/html
COPY . .
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
RUN chmod +x /usr/local/bin/entrypoint.sh
COPY --from=vendor /app/vendor /var/www/html/vendor
COPY --from=frontend /app/public/build /var/www/html/public/build
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && sed -ri -e 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -ri -e 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 10000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
