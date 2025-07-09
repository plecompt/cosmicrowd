import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-system-view',
  imports: [],
  templateUrl: './system-view.component.html',
  styleUrl: './system-view.component.css'
})
export class SystemViewComponent implements OnInit {

  constructor(public authService: AuthService){}

  ngOnInit(): void {
      // If user is not logged in
      if (!this.authService.isLoggedIn()) {
          this.authService.navigateTo('/home');
          return;
      }
    }

}
