import { Component } from '@angular/core';
import { NavigationService } from '../../services/navigation/navigation.service';
import { AuthService } from '../../services/auth/auth.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';

@Component({
  selector: 'app-account',
  templateUrl: './account.component.html',
  styleUrls: ['./account.component.css'],
  imports: [BackgroundStarsComponent]
})
export class AccountComponent {

  constructor(public navigationService: NavigationService, public authService: AuthService) {}

  changeEmail() {
    // TODO: Implement email change functionality
    console.log('Change email clicked');
  }

  changePassword() {
    // TODO: Implement password change functionality
    console.log('Change password clicked');
  }

  contactSupport() {
    // TODO: Implement contact support functionality
    console.log('Contact support clicked');
  }

  deleteAccount() {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
      // TODO: Implement account deletion
      console.log('Delete account confirmed');
    }
  }

  goBack() {
  }
}
