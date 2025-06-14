import { Galaxy } from "../galaxy/galaxy.interface";
import { UserSimple } from "../user/user.interface";

export interface SolarSystem {
  solar_system_id: number;
  solar_system_name: string;
  solar_system_desc?: string;
  solar_system_type: SolarSystemType;
  solar_system_gravity: number;
  solar_system_surface_temp: number;
  solar_system_diameter: number;
  solar_system_mass: number; // BigInt en DB, number en TS
  solar_system_luminosity: number;
  solar_system_initial_x: number;
  solar_system_initial_y: number;
  solar_system_initial_z: number;
  galaxy_id: number;
}

// Interface pour l'animation Three.js
export interface SolarSystemAnimation {
  solar_system_id: number;
  solar_system_name: string;
  solar_system_type: SolarSystemType;
  solar_system_gravity: number;
  solar_system_surface_temp: number;
  solar_system_diameter: number;
  solar_system_mass: number;
  solar_system_luminosity: number;
  solar_system_initial_x: number;
  solar_system_initial_y: number;
  solar_system_initial_z: number;
  galaxy_id: number;
  // Données optionnelles du propriétaire
  owner?: UserSimple;
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

// Interface pour les détails complets avec relations
export interface SolarSystemWithDetails extends SolarSystem {
  galaxy?: Galaxy;
  owner?: UserSimple;
  planets_count?: number;
  moons_count?: number;
  likes_count?: number;
  is_liked_by_user?: boolean;
}