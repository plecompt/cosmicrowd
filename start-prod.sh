#!/bin/bash

# Colors for output formatting
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${PURPLE}Building CosmiCrowd for production environment${NC}"

# Environment validation
if [ ! -f "frontend/package.json" ] || [ ! -f "backend/composer.json" ]; then
    echo -e "${RED}Error: Script must be executed from project root directory${NC}"
    echo -e "${RED}Expected structure: frontend/ and backend/ directories${NC}"
    exit 1
fi

# Check Node.js and Angular CLI availability
if ! command -v ng &> /dev/null; then
    echo -e "${RED}Error: Angular CLI not found. Install with: npm install -g @angular/cli${NC}"
    exit 1
fi

# Check PHP and Composer availability
if ! command -v php &> /dev/null || ! command -v composer &> /dev/null; then
    echo -e "${RED}Error: PHP and Composer are required${NC}"
    exit 1
fi

# Frontend build process
echo -e "${BLUE}Building Angular frontend application${NC}"
cd frontend/

# Clean install dependencies for consistent builds
echo -e "${YELLOW}Installing frontend dependencies${NC}"
npm ci --silent || {
    echo -e "${RED}Failed to install frontend dependencies${NC}"
    exit 1
}

# Production build with optimizations
echo -e "${YELLOW}Compiling Angular for production${NC}"
ng build --configuration production --aot --build-optimizer || {
    echo -e "${RED}Frontend build failed${NC}"
    exit 1
}

cd ..

# Backend build process
echo -e "${BLUE}Building Laravel backend application${NC}"
cd backend/

# Install production dependencies only
echo -e "${YELLOW}Installing backend dependencies${NC}"
composer install --no-dev --optimize-autoloader --no-interaction || {
    echo -e "${RED}Failed to install backend dependencies${NC}"
    exit 1
}

# Laravel optimization commands
echo -e "${YELLOW}Optimizing Laravel application${NC}"

# Clear existing caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Generate optimized caches
php artisan config:cache || {
    echo -e "${RED}Config cache generation failed${NC}"
    exit 1
}

php artisan route:cache || {
    echo -e "${RED}Route cache generation failed${NC}"
    exit 1
}

php artisan view:cache || {
    echo -e "${RED}View cache generation failed${NC}"
    exit 1
}

# Create storage symbolic link if it doesn't exist
php artisan storage:link 2>/dev/null || echo -e "${YELLOW}Storage link already exists or not needed${NC}"

cd ..

# Build directory preparation
echo -e "${BLUE}Preparing build directory structure${NC}"
rm -rf build/
mkdir -p build/

# Copy frontend build output
echo -e "${YELLOW}Copying frontend build artifacts${NC}"
if [ -d "frontend/dist/cosmicrowd" ]; then
    cp -r frontend/dist/cosmicrowd/* build/
else
    # Fallback for different Angular build output structures
    cp -r frontend/dist/* build/
fi

# Copy backend with exclusions for security and performance
echo -e "${YELLOW}Copying backend application${NC}"
rsync -av \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='tests' \
    --exclude='storage/logs/*' \
    --exclude='.env' \
    --exclude='.env.example' \
    --exclude='phpunit.xml' \
    --exclude='webpack.mix.js' \
    backend/ build/api/

# Create necessary directories with proper structure
mkdir -p build/api/storage/logs
mkdir -p build/api/storage/framework/cache
mkdir -p build/api/storage/framework/sessions
mkdir -p build/api/storage/framework/views
mkdir -p build/api/bootstrap/cache

# Copy environment template
cp backend/.env.example build/api/.env.example

# Create deployment instructions
cat > build/DEPLOYMENT.md << EOF
# CosmiCrowd Deployment Instructions

## Server Requirements
- PHP 8.2+
- MySQL 5.7+
- Apache 2.4+ with mod_rewrite
- Composer

## Deployment Steps
1. Upload contents to server
2. Configure .env file in api/ directory
3. Set proper file permissions
4. Configure Apache virtual host
5. Run database migrations
6. Test application functionality

## Security Checklist
- Ensure .env file is not publicly accessible
- Verify storage and cache directories are writable
- Configure HTTPS in production
- Set APP_DEBUG=false in .env
EOF

echo -e "${GREEN}Build process completed successfully${NC}"
echo -e "${GREEN}Build artifacts available in ./build/ directory${NC}"
echo -e "${YELLOW}Next steps for deployment:${NC}"
echo -e "  1. Upload build contents to production server"
echo -e "  2. Configure environment variables in api/.env"
echo -e "  3. Set appropriate file permissions"
echo -e "  4. Configure Apache virtual host"
echo -e "  5. Execute database migrations"
echo -e "  6. Verify application functionality"