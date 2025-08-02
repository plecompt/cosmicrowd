import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SearchService {
  private apiUrl = 'http://localhost:8000/api/v1';

  constructor(private http: HttpClient) {}

  search(query: string, filters: any): Observable<any> {
    let params = new HttpParams()
      .set('q', query);

    // Add filters to params
    if (filters.users) params = params.set('filters[users]', 'true');
    if (filters.systems) params = params.set('filters[systems]', 'true');
    if (filters.planets) params = params.set('filters[planets]', 'true');
    if (filters.moons) params = params.set('filters[moons]', 'true');

    return this.http.get(`${this.apiUrl}/search`, { params });
  }

}
