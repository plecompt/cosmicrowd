export interface Wallpaper {
  wallpaper_id: number;
  wallpaper_settings: string;
  wallpaper_created_at: Date;

  user_id: number;
  galaxy_id: number;
  solar_system_id: number;
  likes_count?: number;
}
