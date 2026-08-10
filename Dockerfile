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
    libcurl

# ============================================================
# PASO 2: INSTALAR EXTENSIONES PHP (GRUPOS)
# ============================================================

# Grupo 1: Extensiones básicas (siempre funcionan)
RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    ctype \
    curl \
    dom \
    fileinfo \
    filter

# Grupo 2: Extensiones con dependencias (GD)
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp
RUN docker-php-ext-install -j$(nproc) gd

# Grupo 3: Extensiones restantes
RUN docker-php-ext-install -j$(nproc) \
    iconv \
    intl \
    mbstring \
    session \
    simplexml \
    tokenizer \
    xml \
    xmlwriter \
    zip

# ============================================================
# PASO 3: INSTALAR EXTENSIÓN MONGODB (REQUERIDA)
# ============================================================

RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# ============================================================
# PASO 4: INSTALAR COMPOSER
# ============================================================

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ============================================================
# PASO 5: CONFIGURAR DIRECTORIO DE TRABAJO
# ============================================================

WORKDIR /var/www

# ============================================================
# PASO 6: COPIAR ARCHIVOS DEL PROYECTO
# ============================================================

COPY . .

# ============================================================
# PASO 7: INSTALAR DEPENDENCIAS DE LARAVEL
# ============================================================

RUN composer install --no-dev --optimize-autoloader --no-interaction

# ============================================================
# PASO 8: CONFIGURAR PERMISOS
# ============================================================

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# ============================================================
# PASO 9: EXPONER PUERTO
# ============================================================

EXPOSE 10000

# ============================================================
# PASO 10: COMANDO DE INICIO
# ============================================================

CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000"]