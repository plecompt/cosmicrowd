import { Injectable } from '@angular/core';
import { Planet } from '../../interfaces/solar-system/planet.interface';

export interface ValidationResult {
  isValid: boolean;
  errors: string[];
}

@Injectable({
  providedIn: 'root'
})
export class PlanetValidationService {

  validatePlanet(planet: Planet): ValidationResult {
    const errors: string[] = [];

    // Physical Properties
    if (planet.planet_gravity < 0 || planet.planet_gravity > 1000) {
      errors.push('Planet\'s gravity must be between 0 and 1000 m.s²');
    }

    if (planet.planet_surface_temp < 0 || planet.planet_surface_temp > 5000) {
      errors.push('Planet\'s surface temperature must be between 0 and 5000 K');
    }

    if (planet.planet_mass < 0 || planet.planet_mass > 100000) {
      errors.push('Planet\'s mass must be between 0.001 and 100000 x 10²⁴ kg');
    }

    if (planet.planet_diameter < 0 || planet.planet_diameter > 200000) {
      errors.push('Planet\'s diameter must be between 0 and 200000 km');
    }

    if (planet.planet_rings < 0 || planet.planet_rings > 10) {
      errors.push('Planet\'s number of rings must be between 0 and 10');
    }

    // Orbital Properties
    if (planet.planet_orbital_longitude < 0 || planet.planet_orbital_longitude > 360) {
      errors.push('Planet\'s orbital longitude must be between 0 and 360 °');
    }

    if (planet.planet_eccentricity < 0 || planet.planet_eccentricity > 1) {
      errors.push('Planet\'s eccentricity must be between 0 and 1');
    }

    if (planet.planet_perigee > planet.planet_apogee || planet.planet_perigee < 0 || planet.planet_apogee < 0 || planet.planet_perigee > 15000000000 || planet.planet_apogee > 15000000000) {
      errors.push('Planet\'s perigee must be less or equal to planet\'s apogee and both must be between 0 and 15000000000 km');
    }

    if (planet.planet_orbital_inclination < 0 || planet.planet_orbital_inclination > 360) {
      errors.push('Planet\'s orbital inclination must be between 0 and 360 °');
    }

    if (planet.planet_orbital_period < 0 || planet.planet_orbital_period > 365000) {
      errors.push('Planet\'s orbital period must be between 0 and 365000 days');
    }

    if (planet.planet_inclination_angle < 0 || planet.planet_inclination_angle > 360) {
      errors.push('Planet\'s inclination angle must be between 0 and 360 °');
    }

    if (planet.planet_rotation_period < 0 || planet.planet_rotation_period > 24000) {
      errors.push('Planet\'s rotation period must be between 0 and 24000 hours');
    }

    if (planet.planet_initial_x < -5000 || planet.planet_initial_x > 5000 || planet.planet_initial_y < -5000 || planet.planet_initial_y > 5000 || planet.planet_initial_z < -5000 || planet.planet_initial_z > 5000) {
      errors.push('Planet\'s coordinates must be between -5000 and 5000');
    }

    return {
      isValid: errors.length === 0,
      errors
    };
  }
}
