import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth/auth.service';
import { User } from '../../models/user/user.model';
import { NotificationService } from '../../services/notifications/notification.service';

@Component({
  selector: 'app-profile',
  imports: [],
  templateUrl: './profile.component.html',
  styleUrl: './profile.component.css'
})
export class ProfileComponent implements OnInit{
  user!: User;

  constructor(public authService: AuthService, private notificationService: NotificationService) { }

  ngOnInit(): void {
    // If user is not logged in
    if (!this.authService.isLoggedIn()) {
        this.notificationService.showError('You can\'t access this page', 3000, '/home');
        return;
    }
    this.getUser();
  }

  //get current user
  getUser(){
    this.authService.me().subscribe({
      next: (response: any) => {
        this.user = response.data.user;
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later', 5000, '/systems');
      }
    })
  }
}
