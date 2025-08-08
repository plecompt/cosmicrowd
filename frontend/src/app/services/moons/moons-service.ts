import { Injectable } from '@angular/core';
import { Moon } from '../../interfaces/solar-system/moon.interface';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { ApiConfigService } from '../api-config-service/api-config-service';

@Injectable({
  providedIn: 'root'
})
export class MoonsService {

  constructor(
    private http: HttpClient,
    private apiConfigService: ApiConfigService
  ) { }

  // Add moon for given moonId
  addMoon(galaxyId: number, solarSystemId: number, planetId: number, moon: Moon): Observable<any> {
    return this.http.post(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons`, moon);
  }

  // Get moon for given moonId
  getMoon(galaxyId: number, solarSystemId: number, planetId: number, moonId: number): Observable<any> {
    return this.http.get(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons/${moonId}`);
  }

  // Update moon for given moon && moonId
  updateMoon(galaxyId: number, solarSystemId: number, planetId: number, moonId: number, moon: Moon): Observable<any> {
    return this.http.put(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons/${moonId}`, moon);
  }

  // Deletemoon for given moonId
  deleteMoon(galaxyId: number, solarSystemId: number, planetId: number, moonId: number): Observable<any> {
    return this.http.delete(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}/moons/${moonId}`);
  }

}
