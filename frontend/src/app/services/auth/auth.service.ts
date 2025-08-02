import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs'; 
import { tap, shareReplay, finalize } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(private http: HttpClient) {}

  //set session in localStorage
  private setSession(authResult: any) {
    localStorage.setItem('token', authResult.data.access_token);
    localStorage.setItem('user_id', authResult.data.user.user_id.toString());
    localStorage.setItem('user_login', authResult.data.user.user_login.toString());
  }

  //return if user is loggedIn
  public isLoggedIn(){
    return localStorage.getItem('user_id') && localStorage.getItem('token') ? true : false;
  }

  //clear session when logout
  public clearSession(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('user_id');
  }

  //login
  login(user_email: string, user_password: string): any{
    return this.http.post('http://localhost:8000/api/v1/auth/login', {user_email, user_password}).pipe(
      tap(res=> this.setSession(res)),
      shareReplay(1)
    );
  }

  //logout
  logout(): Observable<any> {
     return this.http.post<any>('http://localhost:8000/api/v1/auth/logout', {}).pipe(
      finalize(() => {
        this.clearSession();
      }),
      shareReplay(1)
    );
  }

  //return connected user
  me(){
    return this.http.get('http://localhost:8000/api/v1/auth/me');
  }
}
