import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-systems',
  imports: [],
  templateUrl: './systems.component.html',
  styleUrl: './systems.component.css'
})
export class SystemsComponent implements OnInit{

  constructor(public authService: AuthService){}

  ngOnInit(): void {
    // If user is not logged in
    if (!this.authService.isLoggedIn()) {
        this.authService.navigateTo('/home');
        return;
    }
  }

}
