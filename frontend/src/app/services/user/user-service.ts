import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { ApiConfigService } from '../api-config-service/api-config-service';

@Injectable({
  providedIn: 'root'
})
export class UserService {

  constructor(
    private http: HttpClient,
    private apiConfigService: ApiConfigService
  ) {}

  getUserById(userId: number){
    return this.http.get(`${this.apiConfigService.baseUrl}/users/${userId}`);
  }

  //register
  register(user_login: string, user_password: string, user_email: string){
    return this.http.post(`${this.apiConfigService.baseUrl}/users/register`, {user_login, user_password, user_email});
  }

  //delete
  deleteAccount(current_password: string){
    return this.http.post(`${this.apiConfigService.baseUrl}/users/delete-account`, {current_password});
  }

  //user send an email
  contact(user_email: string, user_message: string, user_name: string = "", subject: string = ""){
    return this.http.post(`${this.apiConfigService.baseUrl}/users/contact`, {user_email, user_message, user_name, subject});
  }

  //3 step forgotten password
  //send email to user with the token in the url
  forgotPassword(user_email: string){
    return this.http.post(`${this.apiConfigService.baseUrl}/users/forgot-password`, {user_email});
  }

  //can check if the token is valid (before showing view to reset)
  verifyResetToken(token: string){
    return this.http.post(`${this.apiConfigService.baseUrl}/users/verify-token`, {token});
  }

  //update the password
  setNewPassword(new_password: string, token: string){
    return this.http.post(`${this.apiConfigService.baseUrl}/users/reset-password`, {new_password, token});
  }

  //password modify
  changePassword(current_password: string, new_password: string){
    return this.http.post(`${this.apiConfigService.baseUrl}/users/change-password`, {current_password, new_password});
  }

  //email modify
  changeEmail(current_password: string, new_email: string){
    return this.http.post(`${this.apiConfigService.baseUrl}/users/change-email`, {current_password, new_email});
  }
}
