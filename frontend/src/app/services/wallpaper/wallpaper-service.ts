import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class WallpaperService {
  private apiUrl = 'http://localhost:8000/api/v1';

  constructor(private http: HttpClient) { }
  
  getWallpaper(galaxyId: number, solarSystemId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpapers`);
  }

  saveWallpaper(galaxyId: number, solarSystemId: number, wallpaper_settings: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpapers`, {wallpaper_settings: wallpaper_settings});
  }

  deleteWallpaper(galaxyId: number, solarSystemId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpapers`);
  }

  // Get if there is a wallpaper associated to given solarSystemId
  ifExistWallpaperForSystem(galaxyId: number, solarSystemId: number): Observable<any>{
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpapers/exists`);
  }

  // Get most recent wallpapers in a given galaxy
  getMostRecentWallpapers(galaxyId: number, limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/wallpapers/most-recent?limit=${limit}`);
  }
}
