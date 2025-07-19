import { Injectable } from '@angular/core';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';

export interface ValidationResult {
  isValid: boolean;
  errors: string[];
}

@Injectable({
  providedIn: 'root'
})
export class SystemValidationService {

  validateSystem(solarSystem: SolarSystem): ValidationResult {
    const errors: string[] = [];

    // Physical Properties
    if (solarSystem.solar_system_gravity < 0 || solarSystem.solar_system_gravity > 1000000000000) {
      errors.push('Star\'s gravity must be between 0 and 1000000000000 m.s²');
    }

    if (solarSystem.solar_system_surface_temp < 0 || solarSystem.solar_system_surface_temp > 200000) {
      errors.push('Star\'s surface temperature must be between 0 and 200000 K');
    }

    if (solarSystem.solar_system_mass < 0.001 || solarSystem.solar_system_mass > 25000000000) {
      errors.push('Star\'s mass must be between 0.001 and 100000 x 10²⁴ kg');
    }

    if (solarSystem.solar_system_diameter < 0 || solarSystem.solar_system_diameter > 600000000000) {
      errors.push('Star\'s diameter must be between 0 and 600000000000 km');
    }

    if (solarSystem.solar_system_luminosity < 0 || solarSystem.solar_system_luminosity > 10000000) {
      errors.push('Star\'s luminosity must be between 0 and 10000000 L');
    }

    return {
      isValid: errors.length === 0,
      errors
    };
  }
}
