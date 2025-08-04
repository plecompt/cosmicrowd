import { Component } from '@angular/core';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { NavigationService } from '../../services/navigation/navigation.service';

@Component({
  selector: 'app-rgpd',
  imports: [BackgroundStarsComponent],
  templateUrl: './rgpd.component.html',
  styleUrl: './rgpd.component.css'
})
export class RgpdComponent {

  constructor(private navigationService: NavigationService){}

  goToContact(){
    this.navigationService.navigateTo('/contact');
  }

}
