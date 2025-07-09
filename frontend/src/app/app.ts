import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { NavigationBarComponent } from './components/navbar/navbar.component';
import { ModalComponent } from './components/modal/modal.component';
import { NotificationComponent } from './components/notification/notification.component';


@Component({
  selector: 'app-root',
  imports: [RouterOutlet, NavigationBarComponent, ModalComponent, NotificationComponent],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App {
  protected title = 'cosmicrowd';
}
