import { Component, Input, Output, EventEmitter } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Moon, MoonType } from '../../interfaces/solar-system/moon.interface';
import { User } from '../../interfaces/user/user.interface';
import { NotificationService } from '../../services/notifications/notification.service';
import { AuthService } from '../../services/auth/auth.service';
import { MoonEditComponent } from '../moon-edit/moon-edit.component';
import { TitleCasePipe } from '@angular/common';

@Component({
  selector: 'app-planet-edit',
  standalone: true,
  imports: [FormsModule, MoonEditComponent, TitleCasePipe],
  templateUrl: './planet-edit.component.html',
  styleUrls: ['./planet-edit.component.css']
})
export class PlanetEditComponent {
  @Input() planet: any = null;
  @Input() isVisible: boolean = false;
  @Output() close = new EventEmitter<void>();
  @Output() save = new EventEmitter<any>();
  @Output() editMoonEvent = new EventEmitter<any>();
  @Output() deleteMoonEvent = new EventEmitter<number>();
  @Output() addMoonEvent = new EventEmitter<void>();

  user!: User;
  currentGalaxy: number = 1; //actually there is only one galaxy, in the future it might change
  selectedMoon: Moon | null = null;
  isMoonEditVisible: boolean = false;

  constructor(private authService: AuthService, private notificationService: NotificationService){}

  //get current user
  getUser(){
    this.authService.me().subscribe({
      next: (response: any) => {
        this.user = response.user;
      },
      error: () => {
        this.notificationService.showError('Something wen`t wrong, please try again later', 5000, '/systems');
      }
    })
  }

  closeModal(): void {
    this.close.emit();
  }

  savePlanet(): void {
    // Validate constraints
    if (this.planet.planet_perigee > this.planet.planet_apogee) {
      alert('Perigee must be less than or equal to Apogee');
      return;
    }
    
    // Validate ranges
    if (this.planet.planet_orbital_longitude < 0 || this.planet.planet_orbital_longitude > 360) {
      alert('Orbital longitude must be between 0 and 360 degrees');
      return;
    }
    
    if (this.planet.planet_eccentricity < 0 || this.planet.planet_eccentricity > 1) {
      alert('Eccentricity must be between 0 and 1');
      return;
    }
    
    if (this.planet.planet_orbital_inclination < 0 || this.planet.planet_orbital_inclination > 360) {
      alert('Orbital inclination must be between 0 and 360 degrees');
      return;
    }
    
    if (this.planet.planet_inclination_angle < 0 || this.planet.planet_inclination_angle > 360) {
      alert('Inclination angle must be between 0 and 360 degrees');
      return;
    }
    
    this.save.emit(this.planet);
  }


  addMoon(): void {
    if (!this.planet) return;

    const newMoon: Moon = {
      moon_id: -42, // Temporary ID
      moon_name: 'New Moon',
      moon_desc: '',
      moon_type: 'rocky' as MoonType,
      moon_gravity: 1.6,
      moon_surface_temp: 250,
      moon_orbital_longitude: 0,
      moon_eccentricity: 0,
      moon_apogee: 405000,
      moon_perigee: 363000,
      moon_orbital_inclination: 0,
      moon_average_distance: 384400,
      moon_orbital_period: 27,
      moon_inclination_angle: 0,
      moon_rotation_period: 708,
      moon_mass: 73,
      moon_diameter: 3474,
      moon_rings: 0,
      moon_initial_x: 0,
      moon_initial_y: 0,
      moon_initial_z: 0,
      planet_id: this.planet.planet_id,
      galaxy_id: this.currentGalaxy,
      user_id: this.user.user_id,
    };

    this.planet.moons.push(newMoon);
    this.editMoon(newMoon);
  }

  editMoon(moon: Moon): void {
    this.selectedMoon = moon;
    this.isMoonEditVisible = true;
  }

  deleteMoon(moonId: number): void {
    if (!this.planet) return;
    
    if (confirm('Are you sure you want to delete this moon?')) {
      this.planet.moons = this.planet.moons.filter((moon: Moon) => moon.moon_id !== moonId);
    }
  }

  onMoonSaved(moon: Moon): void {
    this.closeMoonModal();
    console.log('Moon saved:', moon);
  }

  closeMoonModal(): void {
    this.selectedMoon = null;
    this.isMoonEditVisible = false;
  }
}
