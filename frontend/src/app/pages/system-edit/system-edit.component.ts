import { Component, Input, ViewChild } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { NotificationService } from '../../services/notifications/notification.service';
import { AuthService } from '../../services/auth/auth.service';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { SystemAnimationComponent } from '../../components/system-animation/system-animation.component';
import { FormsModule } from '@angular/forms';
import { PlanetEditComponent } from '../../components/planet-edit/planet-edit.component';
import { User } from '../../interfaces/user/user.interface';
import { SystemValidationService } from '../../services/system-validation/system-validation-service';

@Component({
  selector: 'app-system-edit',
  imports: [SystemAnimationComponent, PlanetEditComponent, FormsModule],
  templateUrl: './system-edit.component.html',
  styleUrls: ['./system-edit.component.css', '../../shared/styles/edit.template.css']
})
export class SystemEditComponent {
  @ViewChild('editPlanetRef') planetEditComponent!: PlanetEditComponent;
  @ViewChild(SystemAnimationComponent) systemAnimationComponent!: SystemAnimationComponent;

  @Input() refresh: any = null;
  
  // Follow system
  followedObject: any = null;
  isFollowing = false;

  user!: User;
  solarSystemId!: number;
  solarSystemOwner!: string;
  currentGalaxy: number = 1; //actually there is only one galaxy, in the future it might change
  solarSystems!: SolarSystem[];
  solarSystem!: SolarSystem;
  selectedPlanet: any = null;
  isPanelCollapsed: boolean = false;

  constructor(
    private route: ActivatedRoute,
    public authService: AuthService,
    private notificationService: NotificationService,
    private galaxiesService: GalaxiesService,
    private systemValidationService: SystemValidationService,
  ) { }


  ngOnInit() {
    this.solarSystemId = this.route.snapshot.params['id'];
    this.checkOwner();
    this.getUser();
  }

  //check user is connected and own this system
  checkOwner() {
    this.galaxiesService.getSolarSystemOwner(this.currentGalaxy, this.solarSystemId).subscribe({
      next: (systems) => {
        this.solarSystemOwner = systems.data.owner;

        // If user is not logged in or don't own this system
        if (!this.authService.isLoggedIn() || localStorage.getItem('user_login') != this.solarSystemOwner) {
          this.notificationService.showError('You can\'t access this page', 3000, '/home');
          return;
        } else {
          //if user is logged in, and own the solarSystem, get user solarSystems
          this.getSolarSystems();
        }
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

  //get current user
  getUser() {
    this.authService.me().subscribe({
      next: (response: any) => {
        this.user = response.data.user;
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later', 5000, '/systems');
      }
    })
  }

  //SolarSystem
  getSolarSystems() {
    const user_id = parseInt(localStorage.getItem('user_id') || '');

    this.galaxiesService.getSolarSystemsForUser(user_id, this.currentGalaxy).subscribe({
      next: (solarSystems) => {
        this.solarSystems = solarSystems.data.solar_systems;
        const result = this.solarSystems.find(system => system.solar_system_id == this.solarSystemId);
        //in case we didnt find the solarSystem in solarSystems, seem unlikly, might occur if backend die
        if (result == undefined) {
          this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
        } else {
          this.solarSystem = result;
        }
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

  saveSolarSystem(): void {
    // Validate system
    const validation = this.systemValidationService.validateSystem(this.solarSystem);

    if (!validation.isValid) {
      this.notificationService.showError(validation.errors[0], 2000);
      return;
    }

    // Updating in db
    this.galaxiesService.updateSolarSystem(this.currentGalaxy, this.solarSystem.solar_system_id, this.solarSystem).subscribe({
      next: () => {
        this.notificationService.showSuccess('You successfully updated your solar system !', 2000);
      },
      error: (error) => {
        this.notificationService.showError(error || 'Something went wrong, please try again later', 5000);
      }
    });
    //in any case, refresh
    this.getSolarSystems();
  }


  togglePanel(): void {
    this.isPanelCollapsed = !this.isPanelCollapsed;
  }

  viewObject(obj: any, type: string): void {
    // Stop following if currently following
    if (this.isFollowing) {
      this.followedObject = null;
      this.isFollowing = false;
      if (this.systemAnimationComponent) {
        this.systemAnimationComponent.stopFollowing();
      }
    }

    if (this.systemAnimationComponent) {
      this.systemAnimationComponent.viewObject(obj, type);
    }
  }

  followObject(obj: any, type: string): void {
    if (this.isFollowing && this.followedObject?.id === obj[`${type}_id`] && this.followedObject?.type === type) {
      // Stop following
      this.followedObject = null;
      this.isFollowing = false;
      if (this.systemAnimationComponent) {
        this.systemAnimationComponent.stopFollowing();
      }
    } else {
      // Start following
      this.followedObject = { id: obj[`${type}_id`], type: type, data: obj };
      this.isFollowing = true;
    }
  }
}
