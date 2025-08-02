import { SolarSystem } from "../solar-system/solar-system.interface";
import { User } from "../user/user.interface";

export interface Wallpaper {
  wallpaper_id: number;
  wallpaper_settings: string;
  wallpaper_created_at: Date;

  user_id: number;
  galaxy_id: number;
  solar_system_id: number;
  
  likes_count?: number;
  user?: User;
  solar_system?: SolarSystem;
}

export interface WallpaperSettings {
  // Camera settings
  camera: {
    position: { x: number, y: number, z: number },
    target: { x: number, y: number, z: number },
    fov: number
  },

  // Visual toggles
  visibility: {
    orbits: boolean,
    labels: boolean,
    background: boolean,
    animateOrbits: boolean
  },

  // Rendering effects
  effects: {
    brightness: number,
    contrast: number,
    saturation: number
  },

  // Scale settings
  scale: {
    star: number,
    planets: number,
    moons: number,
    orbits: number
  },

  // Orbital positions snapshot
  orbitalPositions: {
    planets: { [planetId: string]: number },
    moons: { [moonId: string]: number }
  },

  // Metadata
  metadata: {
    systemId: number,
    createdAt: string,
    resolution: { width: number, height: number }
  }
}