import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

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
  private apiUrl = 'http://localhost:8000/api/v1';

  constructor(private http: HttpClient) { }

  like(type: LikeableType, galaxyId: number, solarSystemId?: number, planetId?: number, moonId?: number, wallpaperId?: number): Observable<any> {
    const url = this.buildLikeUrl(type, galaxyId, solarSystemId, planetId, moonId, wallpaperId);
    
    return this.http.post(url, {});
  }

  private buildLikeUrl(type: LikeableType, galaxyId?: number, solarSystemId?: number, planetId?: number, moonId?: number, wallpaperId?: number): string {
    switch (type) {
      case LikeableType.SOLAR_SYSTEM:
        return `${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/to-like`;

      case LikeableType.PLANET:
        return `${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/to-like`;

      case LikeableType.MOON:
        return `${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons/${moonId}/to-like`;

      case LikeableType.WALLPAPER:
        return `${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpapers/${wallpaperId}/to-like`;
    }
  }

  // Check likes on given objects for given user
  getUserLikes(ids: string, type: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/user-likes`, {
      params: { ids: ids, type: type }
    });
  }

  // Get 10 most liked solarSystems in given galaxy
  getMostLikedSolarSystems(galaxyId: number, limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/most-liked?limit=${limit}`);
  }

  // Get most liked wallpapers in a given galaxy
  getMostLikedWallpapers(galaxyId: number, limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/wallpapers/most-liked?limit=${limit}`);
  }
}