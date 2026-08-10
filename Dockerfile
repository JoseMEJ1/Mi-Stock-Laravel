FROM php:8.2-fpm-alpine

# ============================================================
# INSTALAR DEPENDENCIAS DEL SISTEMA (CON LIBZIP-DEV)
# ============================================================

RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    zip \
    autoconf \
    build-base \
    g++ \
    gcc \
    libc-dev \
    make \
    openssl-dev \
    pkgconfig \
    libssl3 \
    openssl \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    curl-dev \
    libzip-dev

# ============================================================
# INSTALAR EXTENSIONES (INCLUYENDO ZIP COMPILADO)
# ============================================================

RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    ctype \
    curl \
    dom \
    fileinfo \
    filter \
    gd \
    intl \
    mbstring \
    session \
    simplexml \
    xml \
    xmlwriter \
    zip

# ============================================================
# HABILITAR TOKENIZER E ICONV
# ============================================================

RUN docker-php-ext-enable tokenizer iconv

# ============================================================
# INSTALAR MONGODB
# ============================================================

RUN pecl install mongodb && docker-php-ext-enable mongodb

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