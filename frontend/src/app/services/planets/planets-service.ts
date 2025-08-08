import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Planet } from '../../interfaces/solar-system/planet.interface';
import { Observable } from 'rxjs';
import { ApiConfigService } from '../api-config-service/api-config-service';

@Injectable({
  providedIn: 'root'
})
export class PlanetsService {

  constructor(
    private http: HttpClient,
    private apiConfigService: ApiConfigService
  ) { }

  // Add planet for given galaxyId && solarSystemId
  addPlanet(galaxyId: number, solarSystemId: number, planet: Planet): Observable<any> {
    return this.http.post(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets`, planet);
  }

  // Get planet for given galaxyId, solarSystemId && planetId
  getPlanet(galaxyId: number, solarSystemId: number, planetId: number): Observable<any> {
    return this.http.get(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}`);
  }

  // Updated planet with given planet
  updatePlanet(galaxyId: number, solarSystemId: number, planetId: number, planet: Planet): Observable<any> {
    return this.http.put(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}`, planet);
  }

  //delete planet
  deletePlanet(galaxyId: number, solarSystemId: number, planetId: number): Observable<any> {
    return this.http.delete(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}`);
  }
}
