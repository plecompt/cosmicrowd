# CosmiCrowd - Collaborative Galaxy

## Description

CosmiCrowd is a revolutionary collaborative web application where users can create their own solar systems in a participatory galaxy!

Explore an animated galaxy where other users' stars orbit, create your own planets and moons, and discover the infinite universe of community creativity!

## Features

- **Custom Solar Systems** - Create planets and moons around pre-generated stars
- **Interactive 3D Galaxy** - Explore an immersive galaxy with Three.js
- **Advanced Search** - Find and explore other users' systems
- **Detailed Visualization** - Admire planets and moons orbiting around each star
- **Like System** - Appreciate community creations
- **Collaborative Experience** - Participate in building a common galaxy

## Upcoming Features

- **Star Creation** - Ability to create your own stars (size, type, mass, luminosity...)
- **Multiple Galaxies** - Expansion to multiple galaxies
- **Free Placement** - Custom positioning of stars in the galaxy

## Technologies Used

- **Backend**: Laravel (latest version)
- **Frontend**: Angular (latest version)
- **Database**: MariaDB
- **3D Animations**: Three.js for galactic exploration
- **Dev Mail Server**: MailDev

## Prerequisites

- PHP >= 8.1
- Composer
- Node.js >= 18
- npm or yarn
- MariaDB >= 10.6
- MailDev (optional, for email testing): `npm install -g maildev`

## Project Structure

```
cosmicrowd/
├── backend/             # Laravel application
├── frontend/            # Angular application
├── documentation/       # Project documentation
├── ressources/          # Project resources
├── start-dev.sh         # Automated development script
├── start-prod.sh        # Automated development script
└── README.md            # This file
```

## Installation

### Manual Installation

1. **Clone the repository**
```bash
git clone https://github.com/plecompt/cosmicrowd.git
cd cosmicrowd
```

2. **Backend dependencies installation**
```bash
cd backend
composer install
```

3. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database configuration**

Edit the .env file with your database parameters

5. **Migrations and seeders**
```bash
php artisan migrate --seed
```

5. **Frontend dependencies installation**
```bash
cd ../frontend
npm install
```

### Automated Installation with Development Script

The project includes an intelligent script that:

- Automatically detects missing packages and installs them
- Launches all necessary development services
- Starts MailDev, php artisan serve, and ng serve

```bash
# Automatic development environment launch
./start-dev.sh
```

### Automated Script for Production Build

The project includes a production build script that:

- Builds Angular frontend for production
- Optimizes Laravel backend (caches config, routes, views)
- Creates a ready-to-deploy build folder

```bash
# Build for production
./build-prod.sh
```

## Usage

### Development Mode using automated script

Start the development environment with all services:
```bash
./start-dev.sh
```
This automatically launches:

- Frontend: http://localhost:4200
- Backend API: http://localhost:8000
- MailDev: http://localhost:1080 (email testing)

### Manual Development

If you prefer to start services manually:

**Terminal 1 - Backend**
```bash
cd backend/
php artisan serve
```

**Terminal 2 - Frontend**
```bash
cd frontend/
ng serve
```

**Terminal 3 - Email testing (optional)**
```bash
maildev
```

- Access the application via http://localhost:4200
- Create an account or log in

By default, there is two users:

- email: user@cosmicrowd.com & password: user1234 
- email: admin@cosmicrowd.com & password: admin1234
                                             

### Database Structure

- Galaxy: Pre-generated through seeders or created by adminastrator
- Solar_System: Pre-generated through seeders (future: user-created stars)
- Planet: User-created celestial bodies orbiting stars
- Moon: User-created satellites orbiting planets
- User: Authentication and user management
- Wallpaper: User-created settings for wallpaper generation
- Like_Solar_System: User appreciation system for Solar_System
- Like_Planet: User appreciation system for Planet
- Like_Moon: User appreciation system for Moon
- Like_Wallpaper: User appreciation system for wallpapers
- Recovery_Token: Allow users to reset their password when lost

## Contribution ##

Feeback is welcomed !

## Developer ##

Created with passion as part of my web development training at Simplon school, Paris, France