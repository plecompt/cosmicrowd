import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class LikesService {
  private apiUrl = 'http://localhost:8000/api/v1';

  constructor(private http: HttpClient) {}


  // Get solarSystem likes count
  getSolarSystemLikesCount(galaxyId: number, solarSystemId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/likes`);
  }

  // Get planet likes count
  getPlanetLikesCount(galaxyId: number, solarSystemId: number, planetId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/likes`);
  }

  // Get moon likes count
  getMoonLikesCount(galaxyId: number, solarSystemId: number, planetId: number, moonId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons/${moonId}/likes`);
  }


  // Méthode principale pour l'accueil
  getGalaxies(): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxy`);
  }

  // Récupérer les statistiques
  getStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxy/stats`);
  }

  // Unclaim solarSystem for current user
  unclaimSolarSystem(userId: number, galaxyId: number, solarSystemId: number){
    return this.http.post(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/unclaim`, { user_id: userId });
  }
}
