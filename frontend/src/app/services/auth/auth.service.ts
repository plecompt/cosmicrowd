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
    this.router.navigateByUrl('/home');
  }

  //register
  register(user_login: string, user_password: string, user_email: string){
    return this.http.post('http://localhost:8000/api/v1/auth/register', {user_login, user_password, user_email});
  }

  //delete
  deleteAccount(current_password: string){
    return this.http.post('http://localhost:8000/api/v1/auth/delete-account', {current_password});
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

  //user send an email
  contact(user_email: string, user_message: string, user_name: string = "", subject: string = ""){
    return this.http.post('http://localhost:8000/api/v1/auth/contact', {user_email, user_message, user_name, subject});
  }

  //return connected user
  me(){
    return this.http.get('http://localhost:8000/api/v1/auth/me');
  }

  //3 step forgotten password
  //send email to user with the token in the url
  forgotPassword(user_email: string){
    return this.http.post('http://localhost:8000/api/v1/auth/forgot-password', {user_email});
  }

  //can check if the token is valid (before showing view to reset)
  verifyResetToken(token: string){
    return this.http.post('http://localhost:8000/api/v1/auth/verify-token', {token});
  }

  //update the password
  setNewPassword(new_password: string, token: string){
    return this.http.post('http://localhost:8000/api/v1/auth/reset-password', {new_password, token});
  }

  //password modify
  changePassword(current_password: string, new_password: string){
    return this.http.post('http://localhost:8000/api/v1/auth/change-password', {current_password, new_password});
  }

  //email modify
  changeEmail(current_password: string, new_email: string){
    return this.http.post('http://localhost:8000/api/v1/auth/change-email', {current_password, new_email});
  }
}
