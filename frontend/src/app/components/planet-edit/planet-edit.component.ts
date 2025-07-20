import { Component, Input, Output, EventEmitter, OnInit, ViewChild } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Moon, MoonType } from '../../interfaces/solar-system/moon.interface';
import { User } from '../../interfaces/user/user.interface';
import { NotificationService } from '../../services/notifications/notification.service';
import { AuthService } from '../../services/auth/auth.service';
import { MoonEditComponent } from '../moon-edit/moon-edit.component';
import { TitleCasePipe } from '@angular/common';
import { PlanetValidationService } from '../../services/planet-validation/planet-validation-service';
import { PlanetType } from '../../interfaces/solar-system/planet.interface';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { ModalService } from '../../services/modal/modal.service';

@Component({
  selector: 'app-planet-edit',
  standalone: true,
  imports: [FormsModule, MoonEditComponent, TitleCasePipe],
  templateUrl: './planet-edit.component.html',
  styleUrls: ['./planet-edit.component.css', '../../shared/styles/edit.template.css']
})
export class PlanetEditComponent implements OnInit {
  @ViewChild('editMoonRef') moonEditComponent!: MoonEditComponent;
  @Input() planet: any = null;
  @Input() solarSystem!: SolarSystem;
  @Output() refresh = new EventEmitter<void>();

  user!: User;
  currentGalaxy: number = 1; //actually there is only one galaxy, in the future it might change
  isVisible: boolean = false;
  showAddMoons: boolean = false;

  constructor(private authService: AuthService, private notificationService: NotificationService, private planetValidationService: PlanetValidationService, private galaxiesService: GalaxiesService, private modalService: ModalService) { }

  ngOnInit(): void {
    this.getUser();
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

  emitRefresh(): void{
    this.refresh.emit();
  }

  closeModal(): void {
    this.isVisible = false;
  }

  openModal(): void {
    this.isVisible = true;
  }

  editPlanet(planet: any): void {
    this.planet = planet;
    this.showAddMoons = true;
    this.openModal();
  }

  addPlanet(): void {
    const newPlanet = {
      planet_id: -42, // Temporary ID
      planet_name: 'New awesome planet',
      planet_desc: '',
      planet_type: 'terrestrial' as PlanetType,
      planet_gravity: 1.0,
      planet_surface_temp: 288,
      planet_orbital_longitude: 0,
      planet_eccentricity: 0,
      planet_apogee: 1000000,
      planet_perigee: 1000000,
      planet_orbital_inclination: 0,
      planet_average_distance: -42, // Temporary distance
      planet_orbital_period: 365,
      planet_inclination_angle: 0,
      planet_rotation_period: 24,
      planet_mass: 1000,
      planet_diameter: 42000,
      planet_rings: 2,
      planet_initial_x: 0,
      planet_initial_y: 0,
      planet_initial_z: 0,
      galaxy_id: this.currentGalaxy,
      solar_system_id: this.solarSystem!.solar_system_id,
      user_id: this.user.user_id,
      expanded: false,
      moons: []
    };

    if (this.solarSystem?.planets && this.solarSystem.planets.length < 8) {
      this.planet = newPlanet;
      this.isVisible = true;
      this.showAddMoons = false; //because we need to create planet before creating moons
    } else {
      this.notificationService.showError('You can\'t have more than 8 planets', 2500);
    }
  }

  savePlanet(): void {
    const validation = this.planetValidationService.validatePlanet(this.planet);

    if (!validation.isValid) {
      this.notificationService.showError(validation.errors[0], 2000);
      return;
    }

    //calculating average distance based of perigee and apogee
    this.planet.planet_average_distance = (this.planet.planet_apogee + this.planet.planet_perigee) / 2;

    //if planet_id is -42, it's a new planet we need to insert in db else, we're modifying an allready existing planet
    if (this.planet.planet_id == -42) {
      this.galaxiesService.addPlanet(this.currentGalaxy, this.planet.solar_system_id, this.planet).subscribe({
        next: () => {
          this.emitRefresh();
          this.notificationService.showSuccess('You successfully added a new planet to your solar system !', 2000);
        },
        error: (error) => {
          this.notificationService.showError(error || 'Something went wrong, please try again later', 5000);
        }
      })
    } else {
      this.galaxiesService.updatePlanet(this.currentGalaxy, this.planet.solar_system_id, this.planet.planet_id, this.planet).subscribe({
        next: () => {
          this.emitRefresh();
          this.notificationService.showSuccess('You successfully updated your planet', 2000);
        },
        error: (error) => {
          this.notificationService.showError(error || 'Something went wrong, please try again later', 5000);
        }
      })
    }

    this.closeModal();
  }

  deletePlanet(planetId: number): void {
    this.modalService.show({
      title: 'Delete Planet ?',
      content: 'Are you sure you want to delete your planet ?',
      showCancel: true,
      showConfirm: true,
      onConfirm: () => {
        this.galaxiesService.deletePlanet(this.currentGalaxy, this.solarSystem.solar_system_id, planetId).subscribe({
          next: () => {
            this.emitRefresh();
            this.notificationService.showSuccess('Planet successfully deleted !', 2500);
          },
          error: (error) => {
            this.notificationService.showError(error.message || 'Something went wrong, please try again later', 5000, '/systems');
          }
        });
      },
      onCancel: () => {
        return;
      }
    })
  }
}
