import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ApiConfigService } from '../api-config-service/api-config-service';

@Injectable({
  providedIn: 'root'
})
export class GalaxiesService {
  
  constructor(
    private http: HttpClient,
    private apiConfigService: ApiConfigService
  ) {}

  // Get stars for animation
  getSolarSystemsForAnimation(galaxyId: number): Observable<any> {
    return this.http.get(`${this.apiConfigService.baseUrl}/galaxies/${galaxyId}/animation`);
  }
}