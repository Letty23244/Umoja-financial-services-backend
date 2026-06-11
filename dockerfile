FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    zip \
    libzip-dev \
    libpq-dev \
    nodejs \
    npm

# Install PHP extensions
RUN apt-get install -y libicu-dev \
    && docker-php-ext-install pdo pdo_pgsql zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
COPY . .

# 👇 Clear out old build caches to prevent local configurations from leaking into Docker
RUN rm -f bootstrap/cache/*.php

# 👇 FIX: Pass a dummy APP_KEY and set env to testing so Filament boots safely without a DB during compilation
RUN APP_ENV=testing APP_KEY=base64:base64_dummy_key_for_building_only_abc123= \
    composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Install Node dependencies and build assets
RUN npm install && npm run build

# Fix permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000
CMD php artisan migrate --force && \
    php artisan filament:upgrade && \
    php artisan vendor:publish --tag=filament-assets --force && \
    php artisan storage:link || true && \
    php artisan config:cache && \
    php artisan view:cache && \
    php artisan serve --host=0.0.0.0 --port=10000