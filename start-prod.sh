#!/bin/bash

set -e  # Exit on any error

echo "Building CosmiCrowd..."

# Check path
[ -f "frontend/package.json" ] && [ -f "backend/composer.json" ] || {
    echo "Error: Run from project root with frontend/ and backend/ directories"
    exit 1
}

command -v ng >/dev/null || { echo "Error: Angular CLI not found"; exit 1; }
command -v php >/dev/null || { echo "Error: PHP not found"; exit 1; }
command -v composer >/dev/null || { echo "Error: Composer not found"; exit 1; }

# Build front
echo "Building frontend..."
cd frontend/
npm ci --silent
ng build --configuration production
cd ..

# Build back
echo "Building backend..."
cd backend/
composer install --no-dev --optimize-autoloader --no-interaction

# Laravel optimizations
php artisan config:clear
php artisan route:clear  
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true
cd ..

# Create build directory
echo "Creating build..."
rm -rf build/
mkdir -p build/

# Copy files
cp -r frontend/dist/*/* build/ 2>/dev/null || cp -r frontend/dist/* build/
cp -r backend/ build/api/
rm -rf build/api/node_modules build/api/.git build/api/tests build/api/storage/logs/* build/api/.env*

# Create necessary directories
mkdir -p build/api/storage/{logs,framework/{cache,sessions,views}}
mkdir -p build/api/bootstrap/cache

echo "Build completed in ./build/"