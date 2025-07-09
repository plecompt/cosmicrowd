import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth/auth.service';
import { NotificationService } from '../../services/notifications/notification.service';

@Component({
  selector: 'app-systems',
  imports: [],
  templateUrl: './systems.component.html',
  styleUrl: './systems.component.css'
})
export class SystemsComponent implements OnInit{

  constructor(public authService: AuthService, private notificationService: NotificationService){}

  ngOnInit(): void {
    // If user is not logged in
    if (!this.authService.isLoggedIn()) {
        this.notificationService.showError('You can\'t access this page', 3000, '/home');
        return;
    }
  }

}
