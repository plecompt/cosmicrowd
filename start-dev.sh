#!/bin/bash

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
NC='\033[0m' # No Color

echo -e "${PURPLE}#  Starting CosmiCrowd...${NC}"

# Check dependencies because we're fancy
check_dependencies() {
    local error=0
    
    echo -e "${CYAN}Checking dependencies...${NC}"
    
    if [ ! -d "frontend" ]; then
        echo -e "${RED}Frontend directory not found!${NC}"
        error=1
    fi
    
    if [ ! -d "backend" ]; then
        echo -e "${RED}Backend directory not found!${NC}"
        error=1
    fi
    
    if ! command -v ng &> /dev/null; then
        echo -e "${RED}❌ Angular CLI not installed!${NC}"
        echo -e "${YELLOW}Run: npm install -g @angular/cli${NC}"
        error=1
    fi
    
    if ! command -v php &> /dev/null; then
        echo -e "${RED}PHP not installed!${NC}"
        error=1
    fi
    
    if ! command -v maildev &> /dev/null; then
        echo -e "${YELLOW}MailDev not installed, skipping...${NC}"
        echo -e "${YELLOW}Run: npm install -g maildev${NC}"
        SKIP_MAILDEV=true
    fi
    
    if [ $error -eq 1 ]; then
        echo -e "${RED}Please fix the errors above before continuing.${NC}"
        exit 1
    fi
    
    echo -e "${GREEN}All dependencies checked!${NC}"
}

# Kill them all
cleanup() {
    local message="$1"

    # if we gave our cleanup method an argument, print it
    if [ -n "$message" ]; then
        echo -e "${RED}#  $message${NC}"
    fi

    echo -e "${YELLOW}#  Killing all processes...${NC}"
    kill $FRONT_PID 2>/dev/null
    kill $BACK_PID 2>/dev/null
    if [ -n "$MAIL_PID" ]; then
        kill $MAIL_PID 2>/dev/null
    fi
    echo -e "${RED}All processes terminated${NC}"
    exit 0
}

# Let me quit
trap cleanup SIGINT SIGTERM

# Run dependency check
check_dependencies

echo ""

# Starting maildev
if [ "$SKIP_MAILDEV" != true ]; then
    echo -e "${BLUE}#  Starting MailDev...${NC}"
    maildev --silent &
    MAIL_PID=$!
    
    if ps -p $MAIL_PID > /dev/null; then
        echo -e "${GREEN}MailDev started successfully${NC}"
    else
        echo -e "${RED}Failed to start MailDev${NC}"
    fi
else
    echo -e "${YELLOW}⏭Skipping MailDev...${NC}"
fi

# Waiting
sleep 2

# Starting frontend
echo -e "${BLUE}Starting Angular frontend...${NC}"
cd frontend/
ng serve &
FRONT_PID=$!
cd ..

# Check if frontend started
sleep 3
if ps -p $FRONT_PID > /dev/null; then
    echo -e "${GREEN}Frontend started successfully${NC}"
else
    cleanup "${RED}Error while starting Angular${NC}"
fi

# Starting backend
echo -e "${BLUE}Starting Laravel backend...${NC}"
cd backend/
php artisan serve --quiet &
BACK_PID=$!
cd ..

# Check if backend started
sleep 2
if ps -p $BACK_PID > /dev/null; then
    echo -e "${GREEN}Backend started successfully${NC}"
else
    cleanup "${RED}Error while starting Laravel${NC}"
fi

# Success message
echo ""
echo -e "${GREEN}# CosmiCrowd is alive!${NC}"
echo -e "${WHITE}┌────────────────────────────────────┐${NC}"
echo -e "${WHITE}│${NC} ${CYAN} Frontend: ${WHITE}http://localhost:4200${NC}   ${WHITE}│${NC}"
echo -e "${WHITE}│${NC} ${CYAN} Backend:  ${WHITE}http://localhost:8000${NC}   ${WHITE}│${NC}"
if [ "$SKIP_MAILDEV" != true ]; then
    echo -e "${WHITE}│${NC} ${CYAN} MailDev:  ${WHITE}http://localhost:1080${NC}   ${WHITE}│${NC}"
fi
echo -e "${WHITE}└────────────────────────────────────┘${NC}"
echo ""
echo -e "${YELLOW}Press Ctrl+C to kill everything${NC}"

# Staying alive
wait