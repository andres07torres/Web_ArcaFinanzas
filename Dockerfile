# =============================================================================
# BASE — PHP-FPM (para desarrollo local con docker-compose)
# =============================================================================
FROM php:8.2-fpm-alpine AS fpm_base

RUN apk add --no-cache \
        postgresql-dev \
        git \
        unzip \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        intl \
        opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /app

# =============================================================================
# BASE — PHP CLI (para producción / tareas)
# =============================================================================
FROM php:8.2-cli-alpine AS cli_base

RUN apk add --no-cache \
        postgresql-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        intl \
        opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /app

# =============================================================================
# VENDOR — dependencias de Composer (solo prod)
# =============================================================================
FROM cli_base AS vendor

COPY composer.json symfony.lock ./

RUN composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction

# =============================================================================
# VITE — build de assets frontend
# =============================================================================
FROM node:22-alpine AS vite

WORKDIR /app

COPY package.json ./

RUN npm install

COPY . .

RUN npm run build

# =============================================================================
# PRODUCCIÓN — etapa por defecto (usada por Render)
# =============================================================================
FROM cli_base AS production

COPY --from=vendor /app/vendor/ /app/vendor/

COPY . .

COPY --from=vite /app/public/build/ /app/public/build/

RUN APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]
