export interface Galaxy {
  galaxy_id: number;
  galaxy_size: number;
  galaxy_name: string;
  galaxy_desc: string;
  galaxy_age: number;
}

export interface GalaxyStats {
  total_galaxies: number;
  total_solar_systems: number; 
  total_users: number;
  total_planets: number;
  total_moons: number;
  total_objects: number;
  active_users: number;
}