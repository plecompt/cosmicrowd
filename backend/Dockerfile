FROM php:8.2-fpm

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy application code
COPY . .

# Expose port
EXPOSE ${PORT:-8000}

# Command to run when container starts
CMD php artisan config:clear && \
    php artisan cache:clear && \
    if [ -z "$APP_KEY" ]; then php artisan key:generate --no-interaction; fi && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
