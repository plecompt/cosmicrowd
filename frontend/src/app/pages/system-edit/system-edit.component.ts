import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { NotificationService } from '../../services/notifications/notification.service';
import { AuthService } from '../../services/auth/auth.service';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';

@Component({
  selector: 'app-system-edit',
  imports: [],
  templateUrl: './system-edit.component.html',
  styleUrl: './system-edit.component.css'
})
export class SystemEditComponent {
  solarSystemId!: number;
  solarSystemOwner!: string;
  currentGalaxy: number = 1; //actually there is only one galaxy, in the future it might change
  solarSystems!: SolarSystem[];
  solarSystem!: SolarSystem | undefined;

  constructor(private route: ActivatedRoute, public authService: AuthService, private notificationService: NotificationService, private galaxiesService: GalaxiesService) { }

  ngOnInit() {
    this.solarSystemId = this.route.snapshot.params['id'];
    this.checkOwner();
  }

  checkOwner() {
    this.galaxiesService.getSolarSystemOwner(parseInt(localStorage.getItem('user_id') || '0'), this.currentGalaxy, this.solarSystemId).subscribe({
      next: (data) => {
        this.solarSystemOwner = data.owner;

        // If user is not logged in or don't own this system
        if (!this.authService.isLoggedIn() || localStorage.getItem('user_login') != this.solarSystemOwner) {
          this.notificationService.showError('You can\'t access this page', 3000, '/home');
          return;
        } else {
          //if user is logged in, and own the solarSystem, get user solarSystems
          this.getSolarSystems();
        }
      },
      error: (error) => {
        this.notificationService.showError(error.error?.message || 'Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

  getSolarSystems() {
    const user_id = parseInt(localStorage.getItem('user_id') || '');

    this.galaxiesService.getSolarSystemsForUser(user_id, this.currentGalaxy).subscribe({
      next: (data) => {
        this.solarSystems = data.solar_systems;
        this.solarSystem = this.solarSystems.find(system => system.solar_system_id == this.solarSystemId);
        //in case we didnt find the solarSystem in solarSystems, seem unlikly, might occur if backend die
        if (this.solarSystem == undefined){
          this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
        }
      },
      error: (error) => {
        this.notificationService.showError(error.error?.message || 'Something went wrong, please try again later', 5000, '/home');
      }
    });
  }



}
