# Single-stage Dockerfile that runs Laravel via PHP's built-in server.
# Suitable for low-traffic MVP deployments on Render / Fly.io / Railway.
# For higher traffic, swap CMD for nginx + php-fpm + supervisord — the
# install layer below already has every extension that needs.

FROM php:8.4-cli-alpine

# System packages + PHP extensions Laravel + intervention/image need
RUN apk add --no-cache \
        git unzip postgresql-dev libpng-dev libjpeg-turbo-dev freetype-dev \
        oniguruma-dev libxml2-dev libzip-dev icu-dev autoconf gcc g++ make \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd pdo pdo_pgsql mbstring exif pcntl bcmath zip intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Composer cache layer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --no-interaction --no-progress

# App code
COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Render injects PORT at runtime; default for local docker runs.
ENV PORT=10000
EXPOSE 10000

# Run migrations + cache config/routes/views, then serve. Boot-time so
# the container picks up env vars from the host.
CMD set -e \
    && php artisan storage:link || true \
    && php artisan migrate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan serve --host=0.0.0.0 --port=$PORT
