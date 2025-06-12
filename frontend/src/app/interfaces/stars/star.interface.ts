export interface StarAnimation {
  star_id: number;
  star_name: string;
  star_type: string;
  star_gravity: number;
  star_surface_temp: number;
  star_diameter: number;
  star_mass: number;
  star_luminosity: number;
  star_initial_x: number;
  star_initial_y: number;
  star_initial_z: number;
  galaxy_id: number;
  user_id: number;
  user?: {
    id: number;
    name: string;
  };
}