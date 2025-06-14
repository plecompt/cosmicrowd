import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs'; 
import { tap, shareReplay, finalize } from 'rxjs/operators';
import { Router } from '@angular/router';
import { User } from '../../interfaces/user/user.interface';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  constructor(private http: HttpClient, private router: Router) {}

  private setSession(authResult: any) {
    localStorage.setItem('token', authResult.access_token);
    localStorage.setItem('user_id', authResult.user.user_id.toString());
  }

  public isLoggedIn(){
    return localStorage.getItem('user_id') && localStorage.getItem('token') ? true : false;
  }

  isLoggedOut(){
    return !this.isLoggedIn();
  }

  public clearSession(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('user_id');
    this.router.navigateByUrl(``);
  }

  //register
  register(login: string, password: string, email: string){
    return this.http.post('http://localhost:8000/api/v1/auth/register', {login, password, email});
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
      tap(),
      finalize(() => {
        this.clearSession();
      }),
      shareReplay(1)
    );
  }

  //3 step forgotten password
  //send email to user with the token in the url
  forgotPassword(email: string){
    return this.http.post('http://localhost:8000/api/v1/auth/forgot-password', {email});
  }

  //can check if the token is valid (before showing view to reset)
  verifyResetToken(token: string){
    return this.http.post('http://localhost:8000/api/v1/auth/verify-token', {token});
  }

  //update the password
  setNewPassword(password: string, token: string){
    return this.http.post('http://localhost:8000/api/v1/auth/reset-password', {password, token});
  }

  //password modify
  changePassword(oldPassword: string, newPassword: string, email: string){
    return this.http.post('http://localhost:8000/api/v1/auth/change-password', {oldPassword, newPassword, email});
  }

  //email modify
  changeEmail(email: string, userId: number){
    return this.http.post('http://localhost:8000/api/v1/auth/change-email', {email, userId});
  }
}
