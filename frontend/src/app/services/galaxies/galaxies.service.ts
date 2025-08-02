import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class GalaxiesService {
  private apiUrl = 'http://localhost:8000/api/v1';

  constructor(private http: HttpClient) {}

  // Get stars for animation
  getSolarSystemsForAnimation(galaxyId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/animation`);
  }
}