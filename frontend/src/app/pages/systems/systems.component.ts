import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth/auth.service';
import { NotificationService } from '../../services/notifications/notification.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { Planet } from '../../interfaces/solar-system/planet.interface';
import { Router } from '@angular/router';
import { ModalService } from '../../services/modal/modal.service';

@Component({
  selector: 'app-systems',
  imports: [BackgroundStarsComponent],
  templateUrl: './systems.component.html',
  styleUrl: './systems.component.css'
})
export class SystemsComponent implements OnInit {
  solarSystems!: SolarSystem[];
  currentGalaxy: number = 1; //At the moment, there is only one galaxy, later, we may need to store the currentGalaxyId
  isLoaded: boolean = false;
  expandedPlanetId: number | null = null;

  constructor(private router: Router, public authService: AuthService, private notificationService: NotificationService, private galaxiesService: GalaxiesService, private modalService: ModalService) { }

  ngOnInit(): void {
    // If user is not logged in
    if (!this.authService.isLoggedIn()) {
      this.notificationService.showError('You can\'t access this page', 3000, '/home');
      return;
    }
    this.getSystems();
  }

  getSystems() {
    const user_id = parseInt(localStorage.getItem('user_id') || '');
    this.galaxiesService.getSolarSystemsForUser(user_id, this.currentGalaxy).subscribe({
      next: (systems) => {
        this.solarSystems = systems.data.solar_systems;
        //here we need to get images....
        this.isLoaded = true;
      },
      error: (error) => {
        this.notificationService.showError(error.error.message || 'Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

  togglePlanetExpansion(planet: any, event: Event) {
    event.stopPropagation();

    if (this.expandedPlanetId === planet.planet_id) {
      this.expandedPlanetId = null;
    } else {
      this.expandedPlanetId = planet.planet_id;

      setTimeout(() => {
        const expandedElement = document.querySelector('.planet-card.expanded');
        if (expandedElement) {
          expandedElement.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
        }
      }, 500);
    }
  }

  isPlanetExpanded(planet: any): boolean {
    return this.expandedPlanetId === planet.planet_id;
  }

  getFormattedTypeForSystem(system: SolarSystem): string {
    switch (system.solar_system_type) {
      case 'brown_dwarf':
        return 'Brown Dwarf';
      case 'red_dwarf':
        return 'Red Dwarf';
      case 'yellow_dwarf':
        return 'Yellow Dwarf';
      case 'white_dwarf':
        return 'White Dwarf';
      case 'red_giant':
        return 'Red Giant';
      case 'blue_giant':
        return 'Blue Giant';
      case 'red_supergiant':
        return 'Red Supergiant';
      case 'blue_supergiant':
        return 'Blue Supergiant';
      case 'hypergiant':
        return 'Hypergiant';
      case 'neutron_star':
        return 'Neutron Star';
      case 'pulsar':
        return 'Pulsar';
      case 'variable':
        return 'Variable';
      case 'binary':
        return 'Binary';
      case 'ternary':
        return 'Ternary';
      case 'black_hole':
        return 'Black Hole';
      default:
        return 'Yellow Dwarf';
    }
  }

  getImageForPlanetType(planet: Planet): string {
    switch (planet.planet_type) {
      case 'terrestrial':
        return '/planets/terrestrial.png';
      case 'gas':
        return '/planets/gas.png';
      case 'ice':
        return '/planets/ice.png';
      case 'super_earth':
        return '/planets/super-earth.png';
      case 'sub_neptune':
        return '/planets/sub-neptune.png';
      case 'dwarf':
        return '/planets/dwarf.png';
      case 'lava':
        return '/planets/lava.png';
      case 'carbon':
        return '/planets/carbon.png';
      case 'ocean':
        return '/planets/ocean.png';
      default:
        return '/planets/lava.png';
    }
  }

  getMoonsCountForSystem(system: SolarSystem): number {
    return system.planets.reduce((acc, planet) => acc + planet.moons.length, 0);
  }

  getPlanetsCountForSystem(system: SolarSystem): number {
    return system.planets.length;
  }

  unclaimSystem(solarSystemId: number): void {
    this.modalService.show({
      title: 'Unclaim Solar System',
      content: 'This action cannot be undone. Your claimed solar system will become available for other explorers.',
      showCancel: true,
      showConfirm: true,
      onConfirm: () => {
        this.galaxiesService.unclaimSolarSystem(parseInt(localStorage.getItem('user_id') || '0'), this.currentGalaxy, solarSystemId).subscribe({
          next: () => {
            this.getSystems();
            this.notificationService.showSuccess('You successfully unclaimed this system', 2500, '/systems');
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


  editSystem(solarSystemId: number) {
    this.router.navigate(['/edit-system', solarSystemId]);
  }

  noSystemNotification() {
    this.notificationService.showError('You can claim up to 3 solar systems', 2500, '/home');
  }
}
