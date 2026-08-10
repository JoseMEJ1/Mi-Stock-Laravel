FROM php:8.2-fpm-alpine

# ============================================================
# INSTALAR DEPENDENCIAS DEL SISTEMA (INCLUYENDO LIBZIP)
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
# INSTALAR EXTENSIONES
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
# HABILITAR TOKENIZER E ICONV (YA VIENEN INSTALADOS)
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

# ============================================================
# CONFIGURAR DIRECTORIO
# ============================================================

WORKDIR /var/www

# ============================================================
# COPIAR ARCHIVOS DEL PROYECTO
# ============================================================

COPY . .

# Verificar que composer.json existe
RUN test -f composer.json || (echo "ERROR: composer.json not found" && exit 1)

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ============================================================
# CONFIGURAR PERMISOS
# ============================================================

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# ============================================================
# EXPONER PUERTO
# ============================================================

EXPOSE 10000

# ============================================================
# COMANDO DE INICIO
# ============================================================

CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000"]