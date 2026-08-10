FROM php:8.2-fpm-alpine

# ============================================================
# INSTALAR DEPENDENCIAS DEL SISTEMA
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
    curl-dev

# ============================================================
# INSTALAR EXTENSIONES PHP (UNA POR UNA CON LOGS)
# ============================================================

RUN set -ex \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install ctype \
    && docker-php-ext-install curl \
    && docker-php-ext-install dom \
    && docker-php-ext-install fileinfo \
    && docker-php-ext-install filter \
    && docker-php-ext-install iconv \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install session \
    && docker-php-ext-install simplexml \
    && docker-php-ext-install tokenizer \
    && docker-php-ext-install xml \
    && docker-php-ext-install xmlwriter \
    && docker-php-ext-install zip

# ============================================================
# INSTALAR GD CON DEPENDENCIAS
# ============================================================

RUN set -ex \
    && apk add --no-cache freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd

# ============================================================
# INSTALAR INTL
# ============================================================

RUN set -ex \
    && apk add --no-cache icu-dev \
    && docker-php-ext-install intl

# ============================================================
# INSTALAR MONGODB
# ============================================================

RUN set -ex \
    && apk add --no-cache openssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# ============================================================
# INSTALAR COMPOSER
# ============================================================

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ============================================================
# CONFIGURAR DIRECTORIO
# ============================================================

WORKDIR /var/www

# ============================================================
# COPIAR ARCHIVOS Y VERIFICAR COMPOSER.JSON
# ============================================================

COPY . .

# Verificar que composer.json existe
RUN test -f composer.json || (echo "ERROR: composer.json not found" && exit 1)

# Mostrar contenido del directorio para debug
RUN ls -la

# Instalar dependencias con verbose
RUN composer install --no-dev --optimize-autoloader --no-interaction --verbose

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