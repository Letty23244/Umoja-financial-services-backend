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

# Clean out local configuration cache
RUN rm -f bootstrap/cache/*.php

# 👇 FIX: Added --no-scripts to prevent Laravel from booting up during build time
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs --no-scripts

# Install Node dependencies and build assets
RUN npm install && npm run build

# Fix permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# 👇 FIX: Added package:discover here so it runs safely when the container is live with its database variables
CMD php artisan package:discover --ansi && \
    php artisan migrate --force && \
    php artisan filament:upgrade && \
    php artisan vendor:publish --tag=filament-assets --force && \
    php artisan storage:link || true && \
    php artisan config:clear && \
    php artisan config:cache && \
    php artisan view:cache && \
    php artisan serve --host=0.0.0.0 --port=10000