import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class GalaxiesService {
  private apiUrl = 'http://localhost:8000/api/v1';

  constructor(private http: HttpClient) {}

  // Méthode principale pour l'accueil
  getGalaxies(): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxy`);
  }

  // Récupérer les statistiques
  getStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxy/stats`);
  }

  // Récupérer les étoiles pour l'animation
  getSolarSystemsForAnimation(galaxyId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/animation`);
  }

  // Get solarSystem for given userId && galaxyId
  getSolarSystemsForUser(userId: number, galaxyId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/systems?user_id=${userId}`);
  }

  // Get user for given solarSystem
  getSolarSystemOwner(userId: number, galaxyId: number, solarSystemId: number){
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/owner`);
  }

  // Get if the given solarSystem is claimable for current user
  isSolarSystemClaimable(userId: number, galaxyId: number, solarSystemId: number){
    return this.http.post(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/is-claimable`, { user_id: userId });
  }

  // Claim solarSystem for current user
  claimSolarSystem(userId: number, galaxyId: number, solarSystemId: number){
    return this.http.post(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/claim`, { user_id: userId });
  }

  // Unclaim solarSystem for current user
  unclaimSolarSystem(userId: number, galaxyId: number, solarSystemId: number){
    return this.http.post(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/unclaim`, { user_id: userId });
  }

  // Étoiles les plus likées
  getMostLikedSolarSystems(limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxy/5/most-liked`);
  }

  // Étoiles récentes
  getRecentSolarSystems(limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxy/5/recent`);
  }

  // Recherche
  searchStars(query: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/search?q=${encodeURIComponent(query)}`);
  }

}
