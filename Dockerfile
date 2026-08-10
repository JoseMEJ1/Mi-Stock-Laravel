FROM php:8.2-fpm-alpine

# ============================================================
# PASO 1: INSTALAR DEPENDENCIAS DEL SISTEMA
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
    libzip-dev \
    # Instalar libcurl explícitamente
    libcurl

# ============================================================
# PASO 2: CONFIGURAR CURL ANTES DE INSTALAR
# ============================================================

RUN docker-php-ext-configure curl --with-curl

# ============================================================
# PASO 3: INSTALAR EXTENSIONES PHP
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
# PASO 4: INSTALAR MONGODB
# ============================================================

RUN pecl install mongodb && docker-php-ext-enable mongodb

# ============================================================
# PASO 5: INSTALAR COMPOSER
# ============================================================

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ============================================================
# PASO 6: CONFIGURAR DIRECTORIO Y COPIAR ARCHIVOS
# ============================================================

WORKDIR /var/www
COPY . .

# ============================================================
# PASO 7: INSTALAR DEPENDENCIAS DE LARAVEL
# ============================================================

RUN composer install --no-dev --optimize-autoloader --no-interaction

# ============================================================
# PASO 8: CONFIGURAR PERMISOS
# ============================================================

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# ============================================================
# PASO 9: EXPONER PUERTO Y COMANDO DE INICIO
# ============================================================

EXPOSE 10000
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000"]