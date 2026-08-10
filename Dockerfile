FROM php:8.2-fpm-alpine

# Instalar dependencias del sistema
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
    oniguruma-dev

# Instalar extensiones PHP
RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    ctype \
    curl \
    dom \
    fileinfo \
    filter \
    gd \
    iconv \
    intl \
    mbstring \
    session \
    simplexml \
    tokenizer \
    xml \
    xmlwriter \
    zip

# Instalar extensión MongoDB (REQUERIDA)
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Exponer puerto
EXPOSE 10000

# Comando de inicio
CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000"]
