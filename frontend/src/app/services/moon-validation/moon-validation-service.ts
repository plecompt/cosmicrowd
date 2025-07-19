import { Injectable } from '@angular/core';
import { Moon } from '../../interfaces/solar-system/moon.interface';

export interface ValidationResult {
  isValid: boolean;
  errors: string[];
}

@Injectable({
  providedIn: 'root'
})
export class MoonValidationService {

  validateMoon(moon: Moon): ValidationResult {
    const errors: string[] = [];

    // Physical Properties
    if (moon.moon_gravity < 0 || moon.moon_gravity > 25) {
      errors.push('Moon\'s gravity must be between 0.01 and 25 m.s²');
    }

    if (moon.moon_surface_temp < 0 || moon.moon_surface_temp > 700) {
      errors.push('Moon\'s surface temperature must be between 0 and 700 K');
    }

    if (moon.moon_mass < 0 || moon.moon_mass > 1000) {
      errors.push('Moon\'s mass must be between 0 and 1000 x 10²⁴ kg');
    }

    if (moon.moon_diameter < 0 || moon.moon_diameter > 10000) {
      errors.push('Moon\'s diameter must be between 0 and 10000 km');
    }

    if (moon.moon_rings < 0 || moon.moon_rings > 10) {
      errors.push('Moon\'s number of rings must be between 0 and 10');
    }

    // Orbital Properties
    if (moon.moon_orbital_longitude < 0 || moon.moon_orbital_longitude > 360) {
      errors.push('Moon\'s orbital longitude must be between 0 and 360 °');
    }

    if (moon.moon_eccentricity < 0 || moon.moon_eccentricity > 1) {
      errors.push('Moon\'s eccentricity must be between 0 and 1');
    }

    if (moon.moon_perigee > moon.moon_apogee || moon.moon_perigee < 0 || moon.moon_apogee < 0 || moon.moon_perigee > 10000000 || moon.moon_apogee > 10000000) {
      errors.push('Moon\'s perigee must be less or equal to planet\'s apogee and both must be between 0 and 10000000 km');
    }

    if (moon.moon_orbital_inclination < 0 || moon.moon_orbital_inclination > 360) {
      errors.push('Moon\'s orbital inclination must be between 0 and 360 °');
    }

    if (moon.moon_orbital_period < 0 || moon.moon_orbital_period > 10000) {
      errors.push('Moon\'s orbital period must be between 0 and 10000 days');
    }

    if (moon.moon_inclination_angle < 0 || moon.moon_inclination_angle > 360) {
      errors.push('Moon\'s inclination angle must be between 0 and 360 °');
    }

    if (moon.moon_rotation_period < 0 || moon.moon_rotation_period > 2000) {
      errors.push('Moon\'s rotation period must be between 0 and 24000 hours');
    }

    if (moon.moon_initial_x < -100 || moon.moon_initial_x > 100 || moon.moon_initial_y < -100 || moon.moon_initial_y > 100 || moon.moon_initial_z < -100 || moon.moon_initial_z > 100) {
      errors.push('Moon\'s coordinates must be between -100 and 100 from planet');
    }

    return {
      isValid: errors.length === 0,
      errors
    };
  }
}
