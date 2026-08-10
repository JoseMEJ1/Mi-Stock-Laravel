FROM php:8.2-fpm-alpine

# Instalar dependencias del sistema
RUN apk add --no-cache \
    git unzip zip curl \
    freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev \
    icu-dev libxml2-dev oniguruma-dev \
    libzip-dev

# Instalar extensiones (tokenizer e iconv ya vienen incluidas)
RUN docker-php-ext-install -j$(nproc) \
    bcmath ctype curl dom fileinfo filter \
    gd intl mbstring session simplexml \
    xml xmlwriter zip

# Instalar MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 10000
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000"]