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
    curl-dev \
    gettext-dev

# ============================================================
# INSTALAR EXTENSIONES (EXCLUYENDO ICONV)
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
    tokenizer \
    xml \
    xmlwriter \
    zip

# ============================================================
# INSTALAR ICONV CON CONFIGURACIÓN ESPECIAL PARA MUSL
# ============================================================

# Configurar iconv para usar la implementación de musl
RUN set -ex \
    && rm -f /usr/local/etc/php/conf.d/*iconv* \
    && docker-php-ext-configure iconv \
    && docker-php-ext-install iconv

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