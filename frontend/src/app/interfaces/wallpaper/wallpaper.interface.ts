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