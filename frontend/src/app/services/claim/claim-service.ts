import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ApiConfigService } from '../api-config-service/api-config-service';

@Injectable({
  providedIn: 'root'
})
export class ClaimService {

  constructor(
    private http: HttpClient,
    private apiConfigService: ApiConfigService
  ) {}

  // Get if the given solarSystem is claimable for current user
  isSolarSystemClaimable(userId: number, galaxyId: number, solarSystemId: number){
    return this.http.post(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/is-claimable`, { user_id: userId });
  }

  // Claim solarSystem for current user
  claimSolarSystem(userId: number, galaxyId: number, solarSystemId: number){
    return this.http.post(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/claim`, { user_id: userId });
  }

  // Unclaim solarSystem for current user
  unclaimSolarSystem(userId: number, galaxyId: number, solarSystemId: number){
    return this.http.post(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/unclaim`, { user_id: userId });
  }
}
