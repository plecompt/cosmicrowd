import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { NotificationService } from '../../services/notifications/notification.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { SystemAnimationComponent } from '../../components/system-animation/system-animation.component';
import { ModalService } from '../../services/modal/modal.service';
import { LikeableType, LikesService } from '../../services/likes/likes.service';

@Component({
  selector: 'app-system-view',
  imports: [SystemAnimationComponent],
  templateUrl: './system-view.component.html',
  styleUrl: './system-view.component.css'
})
export class SystemViewComponent implements OnInit {
  currentGalaxy: number = 1; //currently there is only one galaxy, so hardcoding to 1, might change in the futur.
  solarSystemId!: number;
  solarSystem!: SolarSystem;
  isLoading: boolean = true;

  constructor(
    private route: ActivatedRoute,
    private galaxiesService: GalaxiesService,
    private modalService: ModalService,
    private likesService: LikesService,
    private notificationService: NotificationService
  ) { }

  ngOnInit(): void {
    this.solarSystemId = this.route.snapshot.params['id'];
    this.getSolarSystem();
  }

  getSolarSystem() {
    this.galaxiesService.getSolarSystem(this.currentGalaxy, this.solarSystemId).subscribe({
      next: (solarSystem) => {
        this.solarSystem = solarSystem.data.solar_system;
        this.isLoading = false;

        if (!this.solarSystem) {
          this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
          return;
        }
      },
      error: (error) => {
        this.isLoading = false;
        this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

  onObjectClicked(event: { type: string, data: any }): void {
    this.showModal(event);
  }

  private async showModal(objectData: any): Promise<void> {
    const modalData = this.getModalData(objectData);
    if (!modalData) return;

    this.modalService.show({
      title: modalData.name,
      content: modalData.content,
      showLike: true,
      onLike: () => this.handleLike(modalData)
    });
  }

  private getModalData(objectData: any): any {
    const dataMap: { [key: string]: any } = {
      star: {
        name: this.solarSystem.solar_system_name,
        content: this.getStarModalContent(),
        likeableType: LikeableType.SOLAR_SYSTEM,
        systemId: this.solarSystem.solar_system_id
      },
      planet: {
        name: objectData.data.planet_name,
        content: this.getPlanetModalContent(objectData.data),
        likeableType: LikeableType.PLANET,
        systemId: this.solarSystem.solar_system_id,
        planetId: objectData.data.planet_id
      },
      moon: {
        name: objectData.data.moon_name,
        content: this.getMoonModalContent(objectData.data),
        likeableType: LikeableType.MOON,
        systemId: this.solarSystem.solar_system_id,
        planetId: objectData.data.planet_id,
        moonId: objectData.data.moon_id
      }
    };
    return dataMap[objectData.type];
  }

  private handleLike(modalData: any): void {
    this.likesService.like(
      modalData.likeableType,
      1,
      modalData.systemId,
      modalData.planetId,
      modalData.moonId
    ).subscribe({
      next: (success) => this.notificationService.showSuccess(success.message, 2000),
      error: () => this.notificationService.showError('Something went wrong, please try again later.', 2500)
    });
  }

  private createInfoGrid(items: Array<{ label: string, value: any }>): string {
    const infoItems = items
      .filter(item => item.value !== undefined && item.value !== null)
      .map(item => `<div class="info-item"><strong>${item.label}:</strong> ${item.value}</div>`)
      .join('');
    return `<div class="info-grid" style="margin-top: 15px;">${infoItems}</div>`;
  }

  private getStarModalContent(): string {
    const planetsCount = this.solarSystem.planets?.length || 0;
    const moonsCount = this.solarSystem.planets?.reduce((total, planet) =>
      total + (planet.moons?.length || 0), 0) || 0;

    return this.createInfoGrid([
      { label: 'Description', value: this.solarSystem.solar_system_desc || 'No description' },
      { label: 'Type', value: this.solarSystem.solar_system_type?.replace('_', ' ') || 'Unknown' },
      { label: 'Diameter', value: `${this.solarSystem.solar_system_diameter?.toLocaleString()} km` },
      { label: 'Mass', value: `${this.solarSystem.solar_system_mass?.toLocaleString()} x 10^24 kg` },
      { label: 'Surface Temperature', value: `${this.solarSystem.solar_system_surface_temp} K` },
      { label: 'Gravity', value: `${this.solarSystem.solar_system_gravity} m/s²` },
      { label: 'Luminosity', value: `${this.solarSystem.solar_system_luminosity?.toLocaleString()} L` },
      { label: 'Planets', value: planetsCount },
      { label: 'Moons', value: moonsCount }
    ]);
  }

  private getPlanetModalContent(planet: any): string {
    return this.createInfoGrid([
      { label: 'Description', value: planet.planet_desc || 'No description' },
      { label: 'Type', value: planet.planet_type },
      { label: 'Diameter', value: `${planet.planet_diameter?.toLocaleString()} km` },
      { label: 'Mass', value: `${planet.planet_mass?.toLocaleString()} x 10^24 kg` },
      { label: 'Surface Temperature', value: `${planet.planet_surface_temp} K` },
      { label: 'Gravity', value: `${planet.planet_gravity} m/s²` },
      { label: 'Average Distance from Star', value: `${planet.planet_average_distance?.toLocaleString()} km` },
      { label: 'Orbital Period', value: `${planet.planet_orbital_period?.toLocaleString()} days` },
      { label: 'Rotation Period', value: `${planet.planet_rotation_period} hours` },
      { label: 'Rings', value: planet.planet_rings || 0 },
      { label: 'Moons', value: planet.moons?.length || 0 }
    ]);
  }

  private getMoonModalContent(moon: any): string {
    return this.createInfoGrid([
      { label: 'Description', value: moon.moon_desc || 'No description' },
      { label: 'Type', value: moon.moon_type },
      { label: 'Diameter', value: `${moon.moon_diameter?.toLocaleString()} km` },
      { label: 'Mass', value: `${moon.moon_mass?.toLocaleString()} x 10^24 kg` },
      { label: 'Surface Temperature', value: `${moon.moon_surface_temp} K` },
      { label: 'Gravity', value: `${moon.moon_gravity} m/s²` },
      { label: 'Average Distance from Planet', value: `${moon.moon_average_distance?.toLocaleString()} km` },
      { label: 'Orbital Period', value: `${moon.moon_orbital_period} days` },
      { label: 'Rotation Period', value: `${moon.moon_rotation_period} hours` },
      { label: 'Rings', value: moon.moon_rings || 0 }
    ]);
  }

}