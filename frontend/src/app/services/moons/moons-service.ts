import { Injectable } from '@angular/core';
import { Moon } from '../../interfaces/solar-system/moon.interface';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class MoonsService {
  private apiUrl = 'http://localhost:8000/api/v1';

  constructor(private http: HttpClient) { }

  // Add moon for given moonId
  addMoon(galaxyId: number, solarSystemId: number, planetId: number, moon: Moon): Observable<any> {
    return this.http.post(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons`, moon);
  }

  // Get moon for given moonId
  getMoon(galaxyId: number, solarSystemId: number, planetId: number, moonId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons/${moonId}`);
  }

  // Update moon for given moon && moonId
  updateMoon(galaxyId: number, solarSystemId: number, planetId: number, moonId: number, moon: Moon): Observable<any> {
    return this.http.put(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons/${moonId}`, moon);
  }

  // Deletemoon for given moonId
  deleteMoon(galaxyId: number, solarSystemId: number, planetId: number, moonId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons/${moonId}`);
  }

}
