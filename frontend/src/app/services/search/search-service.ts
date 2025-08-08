import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiConfigService } from '../api-config-service/api-config-service';

@Injectable({
  providedIn: 'root'
})
export class SearchService {
  constructor(
    private http: HttpClient,
    private apiConfigService: ApiConfigService
  ) {}

  search(query: string, filters: any): Observable<any> {
    let params = new HttpParams()
      .set('q', query);

    // Add filters to params
    if (filters.users) params = params.set('filters[users]', 'true');
    if (filters.systems) params = params.set('filters[systems]', 'true');
    if (filters.planets) params = params.set('filters[planets]', 'true');
    if (filters.moons) params = params.set('filters[moons]', 'true');

    return this.http.get(`${this.apiConfigService.baseUrl}/search`, { params });
  }

}
