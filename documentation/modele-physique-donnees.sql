CREATE DATABASE `cosmicrowd`;
USE `cosmicrowd`;

CREATE TABLE galaxy(
    galaxy_id   Int  AUTO_INCREMENT  NOT NULL,
    galaxy_size Int NOT NULL,
    galaxy_name Varchar(50) NOT NULL,
    galaxy_desc Varchar(255) NOT NULL,
    galaxy_age Int NOT NULL,
    CONSTRAINT galaxy_PK PRIMARY KEY (galaxy_id)
) ENGINE=InnoDB;

CREATE TABLE user(
    user_id               Int  AUTO_INCREMENT  NOT NULL,
    user_login            Varchar(50) NOT NULL UNIQUE,
    user_password         Varchar(128) NOT NULL,
    user_email            Varchar(100) NOT NULL UNIQUE,
    user_active           Boolean NOT NULL DEFAULT TRUE,
    user_role             ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    user_last_login       Datetime,
    user_date_inscription Datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT user_PK PRIMARY KEY (user_id)
) ENGINE=InnoDB;

CREATE TABLE solar_system(
    solar_system_id           Int  AUTO_INCREMENT  NOT NULL,
    solar_system_name         Varchar(50) NOT NULL,
    solar_system_desc         Varchar(255),
    solar_system_type ENUM('brown_dwarf', 'red_dwarf', 'yellow_dwarf', 'white_dwarf', 'red_giant', 'blue_giant', 'red_supergiant', 'blue_supergiant', 'hypergiant', 'neutron_star', 'pulsar', 'variable', 'binary', 'ternary', 'black_hole') NOT NULL,
    solar_system_gravity      Float UNSIGNED NOT NULL CHECK (solar_system_gravity >= 0 AND solar_system_gravity <= 1000000000000),
    solar_system_surface_temp Float UNSIGNED NOT NULL CHECK (solar_system_surface_temp >= 0 AND solar_system_surface_temp <= 200000),
    solar_system_diameter     BIGINT UNSIGNED NOT NULL CHECK (solar_system_diameter >= 0 AND solar_system_diameter <= 600000000000),
    solar_system_mass         BIGINT UNSIGNED NOT NULL CHECK (solar_system_mass >= 0 AND solar_system_mass <= 25000000000),
    solar_system_luminosity   BIGINT UNSIGNED NOT NULL CHECK (solar_system_luminosity >= 0 AND solar_system_luminosity <= 10000000),
    solar_system_initial_x    Int NOT NULL,
    solar_system_initial_y    Int NOT NULL,
    solar_system_initial_z    Int NOT NULL,
    galaxy_id         Int NOT NULL,
    user_id           Int NULL,
    CONSTRAINT solar_system_PK PRIMARY KEY (solar_system_id),
    CONSTRAINT solar_system_galaxy_FK FOREIGN KEY (galaxy_id) REFERENCES galaxy(galaxy_id),
    CONSTRAINT solar_system_user_FK FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE planet(
    planet_id                  Int  AUTO_INCREMENT  NOT NULL,
    planet_desc                Varchar(255),
    planet_name                Varchar(50) NOT NULL,
    planet_type ENUM('terrestrial', 'gas', 'ice', 'super_earth', 'sub_neptune', 'dwarf', 'lava', 'carbon', 'ocean') NOT NULL,
    planet_gravity             Float UNSIGNED NOT NULL CHECK (planet_gravity >= 0 AND planet_gravity <= 1000),
    planet_surface_temp        Float UNSIGNED NOT NULL CHECK (planet_surface_temp >= 0 AND planet_surface_temp <= 5000),
    planet_orbital_longitude   Float UNSIGNED NOT NULL CHECK (planet_orbital_longitude >= 0 AND planet_orbital_longitude <= 360),
    planet_eccentricity        Float UNSIGNED NOT NULL CHECK (planet_eccentricity >= 0 AND planet_eccentricity <= 1),
    planet_apogee              BIGINT UNSIGNED NOT NULL CHECK (planet_apogee >= 0 AND planet_apogee <= 15000000000),
    planet_perigee             BIGINT UNSIGNED NOT NULL CHECK (planet_perigee >= 0 AND planet_perigee <= 15000000000),
    planet_orbital_inclination INT UNSIGNED NOT NULL CHECK (planet_orbital_inclination >= 0 AND planet_orbital_inclination <= 360),
    planet_average_distance    BIGINT UNSIGNED NOT NULL,
    planet_orbital_period      INT UNSIGNED NOT NULL CHECK (planet_orbital_period >= 0 AND planet_orbital_period <= 365000),
    planet_inclination_angle   INT UNSIGNED NOT NULL CHECK (planet_inclination_angle >= 0 AND planet_inclination_angle <= 360),
    planet_rotation_period     INT UNSIGNED NOT NULL CHECK (planet_rotation_period > 0 AND planet_rotation_period <= 24000),
    planet_mass                BIGINT UNSIGNED NOT NULL CHECK (planet_mass >= 0 AND planet_mass <= 100000),
    planet_diameter            BIGINT UNSIGNED NOT NULL CHECK (planet_diameter >= 0 AND planet_diameter <= 200000),
    planet_rings               INT UNSIGNED NOT NULL CHECK (planet_rings >= 0 AND planet_rings <= 10),
    planet_initial_x           Int NOT NULL,
    planet_initial_y           Int NOT NULL,
    planet_initial_z           Int NOT NULL,
    solar_system_id            Int NOT NULL,
    user_id                    Int NULL,
    CONSTRAINT planet_PK PRIMARY KEY (planet_id),
    CONSTRAINT planet_solar_system_FK FOREIGN KEY (solar_system_id) REFERENCES solar_system(solar_system_id),
    CONSTRAINT planet_user0_FK FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE SET NULL,
    CONSTRAINT planet_check_perigee_apogee CHECK (planet_perigee <= planet_apogee)
) ENGINE=InnoDB;

CREATE TABLE moon(
    moon_id                  Int  AUTO_INCREMENT  NOT NULL,
    moon_desc                Varchar(255),
    moon_name                Varchar(50) NOT NULL,
    moon_type ENUM('rocky', 'icy', 'mixed', 'primitive', 'regular', 'irregular', 'trojan', 'coorbital') NOT NULL,
    moon_gravity             Float UNSIGNED NOT NULL CHECK (moon_gravity >= 0 AND moon_gravity <= 25),
    moon_surface_temp        Float UNSIGNED NOT NULL CHECK (moon_surface_temp >= 0 AND moon_surface_temp <= 700),
    moon_orbital_longitude   Float UNSIGNED NOT NULL CHECK (moon_orbital_longitude >= 0 AND moon_orbital_longitude <= 360),
    moon_eccentricity        Float UNSIGNED NOT NULL CHECK (moon_eccentricity >= 0 AND moon_eccentricity <= 1),
    moon_apogee              BIGINT UNSIGNED NOT NULL CHECK (moon_apogee >= 100 AND moon_apogee <= 10000000),
    moon_perigee             BIGINT UNSIGNED NOT NULL CHECK (moon_perigee >= 100 AND moon_perigee <= 10000000),
    moon_orbital_inclination INT UNSIGNED NOT NULL CHECK (moon_orbital_inclination >= 0 AND moon_orbital_inclination <= 360),
    moon_average_distance    BIGINT UNSIGNED NOT NULL,
    moon_orbital_period      INT UNSIGNED NOT NULL CHECK (moon_orbital_period >= 1 AND moon_orbital_period <= 10000),
    moon_inclination_angle   INT UNSIGNED NOT NULL CHECK (moon_inclination_angle >= 0 AND moon_inclination_angle <= 360),
    moon_rotation_period     INT UNSIGNED NOT NULL CHECK (moon_rotation_period >= 1 AND moon_rotation_period <= 2000),
    moon_mass                BIGINT UNSIGNED NOT NULL CHECK (moon_mass >= 0 AND moon_mass <= 1000),
    moon_diameter            BIGINT UNSIGNED NOT NULL CHECK (moon_diameter >= 0 AND moon_diameter <= 10000),
    moon_rings               INT UNSIGNED NOT NULL CHECK (moon_rings >= 0 AND moon_rings <= 10),
    moon_initial_x           Int NOT NULL,
    moon_initial_y           Int NOT NULL,
    moon_initial_z           Int NOT NULL,
    planet_id                Int NOT NULL,
    user_id                  Int NULL,
    CONSTRAINT moon_PK PRIMARY KEY (moon_id),
    CONSTRAINT moon_planet_FK FOREIGN KEY (planet_id) REFERENCES planet(planet_id),
    CONSTRAINT moon_user0_FK FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE SET NULL,
    CONSTRAINT moon_check_perigee_apogee CHECK (moon_perigee <= moon_apogee)
) ENGINE=InnoDB;

CREATE TABLE wallpaper(
    wallpaper_id         Int AUTO_INCREMENT NOT NULL,
    user_id              Int NOT NULL,
    galaxy_id            Int NOT NULL,
    solar_system_id      Int NOT NULL,
    wallpaper_settings   TEXT NOT NULL,
    wallpaper_created_at Datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT wallpaper_PK PRIMARY KEY (wallpaper_id),
    CONSTRAINT wallpaper_user_FK FOREIGN KEY (user_id) REFERENCES user(user_id),
    CONSTRAINT wallpaper_galaxy_FK FOREIGN KEY (galaxy_id) REFERENCES galaxy(galaxy_id),
    CONSTRAINT wallpaper_solar_system_FK FOREIGN KEY (solar_system_id) REFERENCES solar_system(solar_system_id)
) ENGINE=InnoDB;

CREATE TABLE like_solar_system(
    solar_system_id         Int NOT NULL,
    user_id                 Int NOT NULL,
    like_solar_system_date  Datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT like_solar_system_PK PRIMARY KEY (solar_system_id, user_id),
    CONSTRAINT like_solar_system_system_FK FOREIGN KEY (solar_system_id) REFERENCES solar_system(solar_system_id),
    CONSTRAINT like_solar_system_user_FK FOREIGN KEY (user_id) REFERENCES user(user_id)
) ENGINE=InnoDB;

CREATE TABLE like_planet(
    planet_id         Int NOT NULL,
    user_id           Int NOT NULL,
    like_planet_date  Datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT like_planet_PK PRIMARY KEY (planet_id, user_id),
    CONSTRAINT like_planet_planet_FK FOREIGN KEY (planet_id) REFERENCES planet(planet_id),
    CONSTRAINT like_planet_user0_FK FOREIGN KEY (user_id) REFERENCES user(user_id)
) ENGINE=InnoDB;

CREATE TABLE like_moon(
    moon_id         Int NOT NULL,
    user_id         Int NOT NULL,
    like_moon_date  Datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT like_moon_PK PRIMARY KEY (moon_id, user_id),
    CONSTRAINT like_moon_moon_FK FOREIGN KEY (moon_id) REFERENCES moon(moon_id),
    CONSTRAINT like_moon_user0_FK FOREIGN KEY (user_id) REFERENCES user(user_id)
) ENGINE=InnoDB;

CREATE TABLE like_wallpaper(
    wallpaper_id         Int NOT NULL,
    user_id              Int NOT NULL,
    like_wallpaper_date  Datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT like_wallpaper_PK PRIMARY KEY (wallpaper_id, user_id),
    CONSTRAINT like_wallpaper_wallpaper_FK FOREIGN KEY (wallpaper_id) REFERENCES wallpaper(wallpaper_id),
    CONSTRAINT like_wallpaper_user_FK FOREIGN KEY (user_id) REFERENCES user(user_id)
) ENGINE=InnoDB;

CREATE TABLE recovery_token(
    recovery_token_id Int AUTO_INCREMENT NOT NULL,
    recovery_token_user_id Int NOT NULL,
    recovery_token_value VARCHAR(255) NOT NULL UNIQUE,
    recovery_token_expires_at DATETIME NOT NULL,
    recovery_token_used BOOLEAN NOT NULL DEFAULT FALSE,
    recovery_token_created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT recovery_token_PK PRIMARY KEY (recovery_token_id),
    CONSTRAINT recovery_token_user_FK FOREIGN KEY (recovery_token_user_id) REFERENCES user(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;
