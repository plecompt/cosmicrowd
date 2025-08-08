import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiConfigService } from '../api-config-service/api-config-service';

export enum LikeableType {
  SOLAR_SYSTEM = 'solar_system',
  PLANET = 'planet',
  MOON = 'moon',
  WALLPAPER = 'wallpaper'
}

@Injectable({
  providedIn: 'root'
})
export class LikesService {

  constructor(
    private http: HttpClient,
    private apiConfigService: ApiConfigService
  ) { }

  like(type: LikeableType, galaxyId: number, solarSystemId?: number, planetId?: number, moonId?: number, wallpaperId?: number): Observable<any> {
    const url = this.buildLikeUrl(type, galaxyId, solarSystemId, planetId, moonId, wallpaperId);
    
    return this.http.post(url, {});
  }

  private buildLikeUrl(type: LikeableType, galaxyId?: number, solarSystemId?: number, planetId?: number, moonId?: number, wallpaperId?: number): string {
    switch (type) {
      case LikeableType.SOLAR_SYSTEM:
        return `${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/to-like`;

      case LikeableType.PLANET:
        return `${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/to-like`;

      case LikeableType.MOON:
        return `${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons/${moonId}/to-like`;

      case LikeableType.WALLPAPER:
        return `${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpapers/${wallpaperId}/to-like`;
    }
  }

  // Check likes on given objects for given user
  getUserLikes(ids: string, type: string): Observable<any> {
    return this.http.get(`${this.apiConfigService.baseUrl}/user-likes`, {
      params: { ids: ids, type: type }
    });
  }

  // Get 10 most liked solarSystems in given galaxy
  getMostLikedSolarSystems(galaxyId: number, limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/most-liked?limit=${limit}`);
  }

  // Get most liked wallpapers in a given galaxy
  getMostLikedWallpapers(galaxyId: number, limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/wallpapers/most-liked?limit=${limit}`);
  }
}