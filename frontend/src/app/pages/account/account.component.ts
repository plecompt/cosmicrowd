import { Component, OnInit } from '@angular/core';
import { NavigationService } from '../../services/navigation/navigation.service';
import { AuthService } from '../../services/auth/auth.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { NotificationService } from '../../services/notifications/notification.service';

@Component({
  selector: 'app-account',
  templateUrl: './account.component.html',
  styleUrls: ['./account.component.css'],
  imports: [BackgroundStarsComponent]
})
export class AccountComponent implements OnInit {

  constructor(
    public navigationService: NavigationService,
    public authService: AuthService,
    private notificationService: NotificationService
  ) {}

  ngOnInit(): void {
    if (!this.authService.isLoggedIn()){
      this.notificationService.showError('You can\'t access this page.', 2500, '/home');
    }
  }

}
