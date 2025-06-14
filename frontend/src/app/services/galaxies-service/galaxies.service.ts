// src/app/services/galaxy.service.ts
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
