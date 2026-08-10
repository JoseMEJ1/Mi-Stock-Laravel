FROM php:8.2-fpm-alpine

# ============================================================
# INSTALAR PHP Y EXTENSIONES DESDE APK (MÁS RÁPIDO)
# ============================================================

RUN apk add --no-cache \
    php8.2 \
    php8.2-bcmath \
    php8.2-ctype \
    php8.2-curl \
    php8.2-dom \
    php8.2-fileinfo \
    php8.2-filter \
    php8.2-gd \
    php8.2-iconv \
    php8.2-intl \
    php8.2-mbstring \
    php8.2-session \
    php8.2-simplexml \
    php8.2-tokenizer \
    php8.2-xml \
    php8.2-xmlwriter \
    php8.2-pecl-mongodb \
    php8.2-phar \
    php8.2-openssl

# NOTA: zip ya viene incluido en php8.2 base

# ============================================================
# INSTALAR COMPOSER
# ============================================================

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 10000
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000"]