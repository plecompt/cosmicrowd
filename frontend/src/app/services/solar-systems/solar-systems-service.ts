import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';

@Injectable({
  providedIn: 'root'
})
export class SolarSystemsService {
  private apiUrl = 'http://localhost:8000/api/v1';

  constructor(private http: HttpClient) {}

  // Get solarSystem for given galaxyId && solarSystemId
  getSolarSystem(galaxyId: number, solarSystemId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}`);
  }

  // Update solarSystem for given galaxyId && solarSystemId
  updateSolarSystem(galaxyId: number, solarSystemId: number, solarSystem: SolarSystem): Observable<any> {
    return this.http.put(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}`, solarSystem);
  }

  // Get solarSystem for given userId && galaxyId
  getSolarSystemsForUser(userId: number, galaxyId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/systems?user_id=${userId}`);
  }

  // Get user for given solarSystem
  getSolarSystemOwner(galaxyId: number, solarSystemId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/owner`);
  }

  // Get 10 most recent solarSystems in given galaxy
  getMostRecentSolarSystems(galaxyId: number, limit: number = 10): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/most-recent?limit=${limit}`);
  }
}
