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

}
