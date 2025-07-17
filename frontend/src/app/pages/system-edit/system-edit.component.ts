import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { NotificationService } from '../../services/notifications/notification.service';
import { AuthService } from '../../services/auth/auth.service';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { SystemAnimationComponent } from '../../components/system-animation/system-animation.component';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { PlanetEditComponent } from '../../components/planet-edit/planet-edit.component';
import { User } from '../../interfaces/user/user.interface';
import { Planet, PlanetType } from '../../interfaces/solar-system/planet.interface';

@Component({
  selector: 'app-system-edit',
  imports: [SystemAnimationComponent, PlanetEditComponent, FormsModule],
  templateUrl: './system-edit.component.html',
  styleUrls: ['./system-edit.component.css', '../../shared/styles/edit.template.css']
})
export class SystemEditComponent {
  user!: User;
  solarSystemId!: number;
  solarSystemOwner!: string;
  currentGalaxy: number = 1; //actually there is only one galaxy, in the future it might change
  solarSystems!: SolarSystem[];
  solarSystem!: SolarSystem | undefined;
  selectedPlanet: any = null;
  showPlanetEdit: boolean = false;

  constructor(
    private route: ActivatedRoute, 
    private router: Router,
    public authService: AuthService, 
    private notificationService: NotificationService, 
    private galaxiesService: GalaxiesService
  ) { }


  ngOnInit() {
    this.solarSystemId = this.route.snapshot.params['id'];
    this.checkOwner();
    this.getUser();
  }

  //check user is connected and own this system
  checkOwner() {
    this.galaxiesService.getSolarSystemOwner(parseInt(localStorage.getItem('user_id') || '0'), this.currentGalaxy, this.solarSystemId).subscribe({
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
      error: (error) => {
        this.notificationService.showError(error.error.message || 'Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

  //get current user
  getUser(){
    this.authService.me().subscribe({
      next: (response: any) => {
        this.user = response.data.user;
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later', 5000, '/systems');
      }
    })
  }

  getSolarSystems() {
    const user_id = parseInt(localStorage.getItem('user_id') || '');

    this.galaxiesService.getSolarSystemsForUser(user_id, this.currentGalaxy).subscribe({
      next: (solarSystems) => {
        this.solarSystems = solarSystems.data.solar_systems;
        this.solarSystem = this.solarSystems.find(system => system.solar_system_id == this.solarSystemId);
        console.log(this.solarSystem);
        //in case we didnt find the solarSystem in solarSystems, seem unlikly, might occur if backend die
        if (this.solarSystem == undefined){
          this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
        }
      },
      error: (error) => {
        this.notificationService.showError(error.error.message || 'Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

  // Edit methods
  updateSystemName(): void {
    if (this.solarSystem && this.solarSystem.solar_system_name.length <= 50) {
      console.log('Updating system name:', this.solarSystem.solar_system_name);
    }
  }

  updateSystemDescription(): void {
    if (this.solarSystem && (!this.solarSystem.solar_system_desc || this.solarSystem.solar_system_desc.length <= 255)) {
      console.log('Updating system description:', this.solarSystem.solar_system_desc);
    }
  }

  updateStarType(): void {
    if (this.solarSystem) {
      console.log('Updating star type:', this.solarSystem.solar_system_type);
    }
  }

  updateStarProperties(): void {
    if (this.solarSystem) {
      // Check constraints
      if (this.solarSystem.solar_system_gravity < 0) this.solarSystem.solar_system_gravity = 0;
      if (this.solarSystem.solar_system_surface_temp < 0) this.solarSystem.solar_system_surface_temp = 0;
      if (this.solarSystem.solar_system_diameter < 0) this.solarSystem.solar_system_diameter = 0;
      if (this.solarSystem.solar_system_mass < 0) this.solarSystem.solar_system_mass = 0;
      if (this.solarSystem.solar_system_luminosity < 0) this.solarSystem.solar_system_luminosity = 0;
      
      console.log('Updating star properties');
    }
  }

  planetShow(planet: any): void {
    //zoom on planet
  }

  planetEdit(planet: any): void {
    this.selectedPlanet = { ...planet };
    this.showPlanetEdit = true;
  }

  closePlanetEdit(): void {
    this.showPlanetEdit = false;
    this.selectedPlanet = null;
  }

  savePlanet(planet: any): void {
    // Find and update the planet in the array
    if (this.solarSystem?.planets){
      const index = this.solarSystem.planets.findIndex((p: any) => p.planet_id === planet.planet_id);
      
      if (index !== -1) {
        this.solarSystem.planets[index] = planet;
      }

      console.log('Saving planet:', planet);
      this.closePlanetEdit();

    } else {
      this.notificationService.showError('Something went wrong, please try again later', 5000, '/systems')
    }
  }

  deletePlanet(planetId: number): void {
    //NEED TO USE MODAL FORM TO CONFIRM
    if (confirm('Are you sure you want to delete this planet?')) {
      if (this.solarSystem?.planets){
        this.solarSystem.planets = this.solarSystem.planets.filter((p: any) => p.planet_id !== planetId);
        console.log('Deleting planet:', planetId);
      } else {
        this.notificationService.showError('Something went wrong, please try again later', 5000, '/systems')
      }
    }
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
      planet_average_distance: 1000000,
      planet_orbital_period: 365,
      planet_inclination_angle: 0,
      planet_rotation_period: 24,
      planet_mass: 1,
      planet_diameter: 12742,
      planet_rings: 0,
      planet_initial_x: 0,
      planet_initial_y: 0,
      planet_initial_z: 0,
      galaxy_id: this.currentGalaxy,
      solar_system_id: this.solarSystem!.solar_system_id,
      user_id: this.user.user_id,
      expanded: false,
      moons: []
    };

    if (this.solarSystem?.planets){
      this.solarSystem.planets.push(newPlanet);
      this.planetEdit(newPlanet);
    }

  }

 editMoon(moon: any): void {
    console.log('Editing moon:', moon);
    // TODO: Implement moon editing
  }

  deleteMoon(moonId: number): void {
    // NEED TO CONFIRM WITH MODAL
    if (confirm('Are you sure you want to delete this moon?')) {
      console.log('Deleting moon:', moonId);
      // TODO: Implement moon deletion
    }
  }

  addMoon(): void {
    console.log('Adding new moon');
    // TODO: Implement moon addition
  }

  saveChanges(): void {
    console.log('Saving all changes:', this.solarSystem);
    // TODO: Implement save to backend
    alert('Changes saved successfully!');
  }

  goBack(): void {
    this.router.navigate(['/system', this.solarSystemId]);
  }

}
