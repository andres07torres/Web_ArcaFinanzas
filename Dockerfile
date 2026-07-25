FROM php:8.2-fpm-alpine AS php_base

RUN apk add --no-cache \
        postgresql-dev \
        git \
        unzip \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        intl \
        opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

FROM php_base AS app_php

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

FROM node:22-alpine AS app_vite

WORKDIR /app

COPY package.json ./

RUN npm install

COPY . .
