export interface Moon {
  moon_id: number;
  moon_desc?: string;
  moon_name: string;
  moon_type: MoonType;
  moon_gravity: number;
  moon_surface_temp: number;
  moon_orbital_longitude: number;
  moon_eccentricity: number;
  moon_apogee: number;
  moon_perigee: number;
  moon_orbital_inclination: number;
  moon_average_distance: number;
  moon_orbital_period: number;
  moon_inclination_angle: number;
  moon_rotation_period: number;
  moon_mass: number;
  moon_diameter: number;
  moon_rings: number;
  moon_initial_x: number;
  moon_initial_y: number;
  moon_initial_z: number;
  planet_id: number;
  galaxy_id: number;
  user_id: number;
  likes_count?: number;
}

// Enum types de solar system
export type MoonType = 
  | 'rocky'
  | 'icy'
  | 'mixed'
  | 'primitive'
  | 'regular'
  | 'irregular'
  | 'trojan'
  | 'coorbital'