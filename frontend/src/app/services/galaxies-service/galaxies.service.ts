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
  getStarsForAnimation(limit: number = 50, offset: number = 0): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxy/solar-systems/animation?limit=${limit}&offset=${offset}`);
  }

  // Étoiles les plus likées
  getMostLikedStars(limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxy/solar-systems/most-liked?limit=${limit}`);
  }

  // Étoiles récentes
  getRecentStars(limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxy/solar-systems/recent?limit=${limit}`);
  }

  // Recherche
  searchStars(query: string): Observable<any> {
    return this.http.get(`${this.apiUrl}/search?q=${encodeURIComponent(query)}`);
  }

}
