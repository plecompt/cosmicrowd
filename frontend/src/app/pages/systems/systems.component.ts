import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth/auth.service';
import { NotificationService } from '../../services/notifications/notification.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';

@Component({
  selector: 'app-systems',
  imports: [],
  templateUrl: './systems.component.html',
  styleUrl: './systems.component.css'
})
export class SystemsComponent implements OnInit{
  solarSystems!: SolarSystem[];
  currentGalaxy: number = 1; //At the moment, there is only one galaxy, later, we may need to store the currentGalaxyId
  
  constructor(public authService: AuthService, private notificationService: NotificationService, private galaxiesService: GalaxiesService){}

  ngOnInit(): void {
    // If user is not logged in
    if (!this.authService.isLoggedIn()) {
        this.notificationService.showError('You can\'t access this page', 3000, '/home');
        return;
    }
    this.getSystems();
  }

  getSystems(){
    const user_id = parseInt(localStorage.getItem('user_id') || '');
    this.galaxiesService.getSolarSystemsForUser(user_id, this.currentGalaxy).subscribe({
      next: (data) => {
        this.solarSystems = data.solar_systems;
        
        console.log(this.solarSystems);
      },
      error: (error) => {
        this.notificationService.showError(error.error?.message || 'Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

  getMoonsCountForSystem(system: SolarSystem): number {
    return system.planets.reduce((acc, planet) => acc + planet.moons.length, 0);
  }

  getPlanetsCountForSystem(system: SolarSystem): number {
    return system.planets.length;
  }

}
