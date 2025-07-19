import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Moon, MoonType } from '../../interfaces/solar-system/moon.interface';
import { MoonValidationService } from '../../services/moon-validation/moon-validation-service';
import { NotificationService } from '../../services/notifications/notification.service';
import { Planet } from '../../interfaces/solar-system/planet.interface';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { AuthService } from '../../services/auth/auth.service';
import { User } from '../../interfaces/user/user.interface';
import { ModalService } from '../../services/modal/modal.service';

@Component({
  selector: 'app-moon-edit',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './moon-edit.component.html',
  styleUrls: ['./moon-edit.component.css', '../../shared/styles/edit.template.css']
})
export class MoonEditComponent {
  @Output() refresh = new EventEmitter<void>();
  @Output() closeParentModal = new EventEmitter<void>();

  user!: User;
  currentGalaxy: number = 1; //actually there is only one galaxy, in the future it might change
  isVisible: boolean = false;
  moon!: Moon;
  currentPlanet!: Planet;

  constructor(private authService: AuthService, private moonValidationService: MoonValidationService, private notificationService: NotificationService, private galaxiesService: GalaxiesService, private modalService: ModalService) { }

  ngOnInit(): void {
    this.getUser();
  }

  emitRefresh(): void {
    this.refresh.emit();
  }

  emitCloseParentModal(): void {
    this.closeParentModal.emit();
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

  openModal(): void {
    this.isVisible = true;
  }

  closeModal(): void {
    this.isVisible = false;
  }

  addMoon(planet: Planet): void {
    const newMoon: Moon = {
      moon_id: -42, // Temporary ID
      moon_name: 'New awesome moon',
      moon_desc: '',
      moon_type: 'rocky' as MoonType,
      moon_gravity: 1.6,
      moon_surface_temp: 250,
      moon_orbital_longitude: 0,
      moon_eccentricity: 0,
      moon_apogee: 420000,
      moon_perigee: 420000,
      moon_orbital_inclination: 0,
      moon_average_distance: -42, // Temporary distance
      moon_orbital_period: 30,
      moon_inclination_angle: 0,
      moon_rotation_period: 12,
      moon_mass: 50,
      moon_diameter: 1337,
      moon_rings: 0,
      moon_initial_x: 0,
      moon_initial_y: 0,
      moon_initial_z: 0,
      planet_id: planet.planet_id,
      galaxy_id: this.currentGalaxy,
      user_id: this.user.user_id,
    };

    if (planet && planet.moons.length < 3) {
      this.currentPlanet = planet;
      this.moon = newMoon;
      this.openModal();
    } else {
      this.notificationService.showError('You can\'t have more than 3 moons by planet', 2500);
    }
  }

  editMoon(planet: Planet, moon: Moon): void {
    this.moon = moon;
    this.currentPlanet = planet;
    this.openModal();
  }

  saveMoon(): void {
    // Validate moon
    const validation = this.moonValidationService.validateMoon(this.moon);

    if (!validation.isValid) {
      this.notificationService.showError(validation.errors[0], 2000);
      return;
    }

    // Calculate average distance from perigee and apogee
    this.moon.moon_average_distance = (this.moon.moon_perigee + this.moon.moon_apogee) / 2;

    //if moon_id is -42, it's a new moon we need to insert in db else, we're modifying an allready existing moon
    if (this.moon.moon_id == -42) {
      this.galaxiesService.addMoon(this.currentGalaxy, this.currentPlanet.solar_system_id, this.currentPlanet.planet_id, this.moon).subscribe({
        next: () => {
          this.emitRefresh();
          this.emitCloseParentModal();
          this.notificationService.showSuccess('You successfully added a new moon around your planet !', 2000);
        },
        error: (error) => {
          this.notificationService.showError(error || 'Something went wrong, please try again later', 5000);
        }
      })
    } else {
      this.galaxiesService.updateMoon(this.currentGalaxy, this.currentPlanet.solar_system_id, this.currentPlanet.planet_id, this.moon.moon_id, this.moon).subscribe({
        next: () => {
          this.emitRefresh();
          this.emitCloseParentModal();
          this.notificationService.showSuccess('You successfully updated your moon', 2000);
        },
        error: (error) => {
          this.notificationService.showError(error || 'Something went wrong, please try again later', 5000);
        }
      })
    }

    this.closeModal();
  }

  deleteMoon(planet: Planet, moonId: number): void {
    this.modalService.show({
      title: 'Delete Moon ?',
      content: 'Are you sure you want to delete your moon ?',
      showCancel: true,
      showConfirm: true,
      onConfirm: () => {
        this.galaxiesService.deleteMoon(this.currentGalaxy, planet.solar_system_id, planet.planet_id, moonId).subscribe({
          next: () => {
            this.emitRefresh();
            this.emitCloseParentModal();
            this.notificationService.showSuccess('Moon successfully deleted !', 2500);
          },
          error: (error) => {
            this.notificationService.showError(error.error.message || 'Something went wrong, please try again later', 5000, '/systems');
          }
        });
      },
      onCancel: () => {
        return;
      }
    })
  }
}
