import { Moon } from "./moon.interface";

export interface Planet {
  planet_id: number;
  planet_desc?: string;
  planet_name: string;
  planet_type: PlanetType;
  planet_gravity: number;
  planet_surface_temp: number;
  planet_orbital_longitude: number;
  planet_eccentricity: number;
  planet_apogee: number;
  planet_perigee: number;
  planet_orbital_inclination: number;
  planet_average_distance: number;
  planet_orbital_period: number;
  planet_inclination_angle: number;
  planet_rotation_period: number;
  planet_mass: number;
  planet_diameter: number;
  planet_rings: number;
  planet_initial_x: number;
  planet_initial_y: number;
  planet_initial_z: number;
  
  moons: Moon[];
  solar_system_id: number;
  galaxy_id: number;
  user_id: number;
  likes_count?: number;
  expanded: boolean;
}

// Enum types de planet
export type PlanetType = 
  | 'terrestrial'
  | 'gas'
  | 'ice'
  | 'super_earth'
  | 'sub_neptune'
  | 'dwarf'
  | 'lava'
  | 'carbon'
  | 'ocean'