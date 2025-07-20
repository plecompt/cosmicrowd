import { Planet } from "./planet.interface";
import { Wallpaper } from "./wallpaper.interface";

export interface SolarSystem {
  solar_system_id: number;
  solar_system_name: string;
  solar_system_desc?: string;
  solar_system_type: SolarSystemType;
  solar_system_gravity: number;
  solar_system_surface_temp: number;
  solar_system_diameter: number;
  solar_system_mass: number;
  solar_system_luminosity: number;
  solar_system_initial_x: number;
  solar_system_initial_y: number;
  solar_system_initial_z: number;
  
  user_id?: number | null;
  wallpaper?: Wallpaper;
  planets: Planet[];
  galaxy_id: number;
  likes_count?: number;
  is_liked?: number;
  planetsCount?: number;
  moonsCount?: number;
}

// Enum types de solar system
export type SolarSystemType = 
  | 'brown_dwarf' 
  | 'red_dwarf' 
  | 'yellow_dwarf' 
  | 'white_dwarf' 
  | 'red_giant' 
  | 'blue_giant'
  | 'red_supergiant' 
  | 'blue_supergiant' 
  | 'hypergiant' 
  | 'neutron_star' 
  | 'pulsar' 
  | 'variable' 
  | 'binary' 
  | 'ternary' 
  | 'black_hole';