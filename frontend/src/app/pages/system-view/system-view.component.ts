import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth/auth.service';
import { NotificationService } from '../../services/notifications/notification.service';

@Component({
  selector: 'app-system-view',
  imports: [],
  templateUrl: './system-view.component.html',
  styleUrl: './system-view.component.css'
})
export class SystemViewComponent implements OnInit {

  constructor(public authService: AuthService, private notificationService: NotificationService){}

  ngOnInit(): void {
      // If user is not logged in
      if (!this.authService.isLoggedIn()) {
          this.notificationService.showError('You can\'t access this page', 3000, '/home');
          return;
      }
    }

}
