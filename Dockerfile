# =============================================================================
# BASE — PHP-FPM (para desarrollo local con docker-compose)
# =============================================================================
FROM php:8.3-fpm-alpine AS fpm_base

RUN apk add --no-cache \
        postgresql-dev \
        git \
        unzip \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        intl \
        opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /app

# =============================================================================
# BASE — PHP CLI (para producción / tareas)
# =============================================================================
FROM php:8.3-cli-alpine AS cli_base

RUN apk add --no-cache \
        postgresql-dev \
        git \
        unzip \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
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
        --no-interaction \
        --no-scripts

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

RUN echo "APP_ENV=prod" > .env && \
    echo "APP_SECRET=ChangeMeInRenderEnv" >> .env && \
    echo "APP_DEBUG=0" >> .env && \
    echo "DEFAULT_URI=https://web-arcafinanzas.onrender.com" >> .env && \
    echo "DATABASE_URL=sqlite:////dev/shm/db.sqlite" >> .env && \
    echo "MAILER_DSN=null://null" >> .env && \
    echo "MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0" >> .env && \
    APP_ENV=prod \
        APP_DEBUG=0 \
        APP_SECRET=ChangeMeInRenderEnv \
        DATABASE_URL="sqlite:////dev/shm/db.sqlite" \
        php bin/console cache:clear --no-warmup

RUN mkdir -p /app/var && chmod -R 777 /app/var

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public public/index.php"]
