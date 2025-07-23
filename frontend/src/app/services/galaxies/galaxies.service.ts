import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { Planet } from '../../interfaces/solar-system/planet.interface';
import { Moon } from '../../interfaces/solar-system/moon.interface';

@Injectable({
  providedIn: 'root'
})
export class GalaxiesService {
  private apiUrl = 'http://localhost:8000/api/v1';

  constructor(private http: HttpClient) {}

  //Galaxy
  // Get stars for animation
  getSolarSystemsForAnimation(galaxyId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/animation`);
  }


  //Solar Systems
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


  //Planets
  // Add planet for given galaxyId && solarSystemId
  addPlanet(galaxyId: number, solarSystemId: number, planet: Planet): Observable<any> {
    return this.http.post(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets`, planet);
  }

  // Get planet for given galaxyId, solarSystemId && planetId
  getPlanet(galaxyId: number, solarSystemId: number, planetId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}`);
  }

  // Updated planet with given planet
  updatePlanet(galaxyId: number, solarSystemId: number, planetId: number, planet: Planet): Observable<any> {
    return this.http.put(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}`, planet);
  }

  //delete planet
  deletePlanet(galaxyId: number, solarSystemId: number, planetId: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/planets/${planetId}`);
  }


  //Moons
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


  //Claim
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


  //Search
  searchStars(query: string, filters: any): Observable<any> {
    let params = new HttpParams()
      .set('q', query);

    // Add filters to params
    if (filters.users) params = params.set('filters[users]', 'true');
    if (filters.systems) params = params.set('filters[systems]', 'true');
    if (filters.planets) params = params.set('filters[planets]', 'true');
    if (filters.moons) params = params.set('filters[moons]', 'true');

    return this.http.get(`${this.apiUrl}/search`, { params });
  }



  //Wallpapers
  // Get if there is a wallpaper associated to given solarSystemId
  ifExistWallpaperForSystem(galaxyId: number, solarSystemId: number): Observable<any>{
    return this.http.get(`${this.apiUrl}/galaxies/${galaxyId}/solar-systems/${solarSystemId}/wallpaper/exists`);
  }
}
