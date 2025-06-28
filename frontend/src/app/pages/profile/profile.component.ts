import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth-service/auth-service';
import { User } from '../../models/user/user.model';

@Component({
  selector: 'app-profile',
  imports: [],
  templateUrl: './profile.component.html',
  styleUrl: './profile.component.css'
})
export class ProfileComponent implements OnInit{
  user!: User;

  constructor(private router: Router, private authService: AuthService) { }

  ngOnInit(): void {
    this.getUser();
  }

  getUser(){
    this.authService.me().subscribe({
      next: (response: any) => {
        this.user = response.user;
      }
    })
  }

  navigateTo(url: string) {
    this.router.navigateByUrl(url)
  }

}
