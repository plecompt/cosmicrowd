#!/bin/bash

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

echo -e "${PURPLE}#  Building CosmiCrowd for production...${NC}"

# Build frontend
echo -e "${BLUE}🎨 Building Angular...${NC}"
cd frontend/
ng build --configuration production || exit 1
cd ..

# Build backend
echo -e "${BLUE}⚙️  Optimizing Laravel...${NC}"
cd backend/
composer install --no-dev --optimize-autoloader || exit 1
php artisan config:cache
php artisan route:cache
php artisan view:cache
cd ..

# Create build folder
echo -e "${BLUE}📦 Creating build...${NC}"
rm -rf build/
mkdir -p build/
cp -r frontend/dist/* build/
cp -r backend/ build/backend/

echo -e "${GREEN}✅ Build completed in ./build/${NC}"
echo -e "${GREEN}🚀 Ready to deploy!${NC}"