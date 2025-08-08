import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiConfigService } from '../api-config-service/api-config-service';

@Injectable({
  providedIn: 'root'
})
export class WallpaperService {

  constructor(
    private http: HttpClient,
    private apiConfigService: ApiConfigService
  ) { }
  
  getWallpaper(galaxyId: number, solarSystemId: number): Observable<any> {
    return this.http.get(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpapers`);
  }

  saveWallpaper(galaxyId: number, solarSystemId: number, wallpaper_settings: string): Observable<any> {
    return this.http.post(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpapers`, {wallpaper_settings: wallpaper_settings});
  }

  deleteWallpaper(galaxyId: number, solarSystemId: number): Observable<any> {
    return this.http.delete(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpapers`);
  }

  // Get if there is a wallpaper associated to given solarSystemId
  ifExistWallpaperForSystem(galaxyId: number, solarSystemId: number): Observable<any>{
    return this.http.get(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpapers/exists`);
  }

  // Get most recent wallpapers in a given galaxy
  getMostRecentWallpapers(galaxyId: number, limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/wallpapers/most-recent?limit=${limit}`);
  }
}
