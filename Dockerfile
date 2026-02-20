# syntax=docker/dockerfile:1
# Laravel on Render — PHP 8.3, production image with Vite assets built

# -----------------------------------------------------------------------------
# Stage: base — PHP 8.3 and extensions
# -----------------------------------------------------------------------------
FROM php:8.3-cli-alpine AS base

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    linux-headers \
    sqlite-dev \
    postgresql-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        zip \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV PATH="${PATH}:/app/vendor/bin"

# -----------------------------------------------------------------------------
# Stage: builder — install deps and build frontend
# -----------------------------------------------------------------------------
FROM base AS builder

WORKDIR /app

# Composer: install production deps only
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# App source (needed for autoload and for npm)
COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative

# Node: build Vite/Tailwind assets
RUN apk add --no-cache nodejs npm \
    && npm ci \
    && npm run build \
    && rm -rf node_modules

# -----------------------------------------------------------------------------
# Stage: runtime — minimal image to run the app
# -----------------------------------------------------------------------------
FROM base AS runtime

WORKDIR /app

RUN addgroup -g 1000 app && adduser -u 1000 -G app -D app

# Copy app (no dev deps, no node_modules; public/build from Vite is included)
COPY --from=builder --chown=app:app /app .

# Ensure storage and cache are writable
RUN chown -R app:app storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER app

# Render assigns PORT; default for local runs
ENV PORT=8000
EXPOSE 8000

# Single-threaded server; for higher traffic consider PHP-FPM + nginx
# Use shell so Render's PORT is expanded
CMD sh -c 'php artisan serve --host=0.0.0.0 --port=${PORT:-8000}'
