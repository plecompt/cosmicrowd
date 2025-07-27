import { Component, OnInit, ViewChild } from '@angular/core';
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
  styleUrls: ['../../shared/styles/edit.template.css', './system-view.component.css']
})
export class SystemViewComponent implements OnInit {
  @ViewChild(SystemAnimationComponent) systemAnimationComponent!: SystemAnimationComponent;
  
  currentGalaxy: number = 1; //currently there is only one galaxy, so hardcoding to 1, might change in the futur.
  solarSystemId!: number;
  solarSystem!: SolarSystem;
  isLoading: boolean = true;
  
  // Panel state
  isPanelCollapsed = false;
  
  // Follow system
  followedObject: any = null;
  isFollowing = false;
  
  // Render
  renderer: any;

  renderOptions = {
    showOrbits: true,
    resolution: '1920x1080',
    quality: 'high',
    
    // Animation control
    orbitsPaused: false,
    
    // Visual effects
    brightness: 1.0,
    contrast: 1.0,
    saturation: 1.0,

    scale: {
      star: 1.0,
      planets: 1.0,
      moons: 1.0,
      orbits: 1.0 
    }
  };
    
  //Liked Objects
  private likedObjects = new Map<LikeableType, Set<number>>([
    [LikeableType.SOLAR_SYSTEM, new Set<number>()],
    [LikeableType.PLANET, new Set<number>()],
    [LikeableType.MOON, new Set<number>()]
  ]);

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
        if (!this.solarSystem) {
          this.isLoading = false;
          this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
          return;
        }
        this.loadLikes();
      },
      error: (error) => {
        this.isLoading = false;
        this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

  //Follow 
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

  //Click and Likes 
  onObjectClicked(event: { type: string, data: any }): void {
    this.showModal(event);
  }

  private async showModal(objectData: any): Promise<void> {
    const modalData = this.getModalData(objectData);
    if (!modalData) return;

    this.modalService.show({
      title: modalData.name,
      content: modalData.content,
      showLike: modalData.isLiked ? false : true,
      showUnlike: modalData.isLiked ? true : false,
      onLike: () => this.handleLike(modalData),
      onUnlike: () => this.handleLike(modalData)
    });
  }

  private getModalData(objectData: any): any {
    const dataMap: { [key: string]: any } = {
      star: {
        name: this.solarSystem.solar_system_name,
        content: this.getStarModalContent(),
        likeableType: LikeableType.SOLAR_SYSTEM,
        systemId: this.solarSystem.solar_system_id,
        isLiked: this.isLiked(LikeableType.SOLAR_SYSTEM, this.solarSystem.solar_system_id)
      },
      planet: {
        name: objectData.data.planet_name,
        content: this.getPlanetModalContent(objectData.data),
        likeableType: LikeableType.PLANET,
        systemId: this.solarSystem.solar_system_id,
        planetId: objectData.data.planet_id,
        isLiked: this.isLiked(LikeableType.PLANET, objectData.data.planet_id)
      },
      moon: {
        name: objectData.data.moon_name,
        content: this.getMoonModalContent(objectData.data),
        likeableType: LikeableType.MOON,
        systemId: this.solarSystem.solar_system_id,
        planetId: objectData.data.planet_id,
        moonId: objectData.data.moon_id,
        isLiked: this.isLiked(LikeableType.MOON, objectData.data.moon_id)
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
      next: (success) => {
        // toggle local cache
        this.toggleLikeCache(modalData.likeableType, this.getLikeId(modalData));
        this.notificationService.showSuccess(success.message, 2000);
      },
      error: () => this.notificationService.showError('Something went wrong, please try again later.', 2500)
    });
  }

  private getLikeId(modalData: any): number {
    switch (modalData.likeableType) {
      case LikeableType.SOLAR_SYSTEM:
        return modalData.systemId;
      case LikeableType.PLANET:
        return modalData.planetId;
      case LikeableType.MOON:
        return modalData.moonId;
      default:
        return 0;
    }
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

  // Render
  toggleOrbitVisibility(): void {
    this.renderOptions = {
      ...this.renderOptions,
      showOrbits: !this.renderOptions.showOrbits
    };
  }

  onResolutionChange(event: Event): void {
    const target = event.target as HTMLSelectElement;
    this.renderOptions = {
      ...this.renderOptions,
      resolution: target.value
    };
  }

  onRenderReady(data: any): void {
    this.renderer = data;
  }

  toggleOrbitalAnimation() {
    this.renderOptions = {
      ...this.renderOptions,
      orbitsPaused: !this.renderOptions.orbitsPaused
    };
  }

  onBrightnessChange(event: Event) {
    const target = event.target as HTMLInputElement;
    this.renderOptions = {
      ...this.renderOptions,
      brightness: parseFloat(target.value)
    };
  }

  onContrastChange(event: Event) {
    const target = event.target as HTMLInputElement;
    this.renderOptions = {
      ...this.renderOptions,
      contrast: parseFloat(target.value)
    };
  }

  onSaturationChange(event: Event) {
    const target = event.target as HTMLInputElement;
    this.renderOptions = {
      ...this.renderOptions,
      saturation: parseFloat(target.value)
    };
  }

  onStarScaleChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    this.renderOptions = {
      ...this.renderOptions,
      scale: {
        ...this.renderOptions.scale,
        star: parseFloat(target.value)
      }
    };
  }

  onPlanetsScaleChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    this.renderOptions = {
      ...this.renderOptions,
      scale: {
        ...this.renderOptions.scale,
        planets: parseFloat(target.value)
      }
    };
  }

  onMoonsScaleChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    this.renderOptions = {
      ...this.renderOptions,
      scale: {
        ...this.renderOptions.scale,
        moons: parseFloat(target.value)
      }
    };
  }

  onOrbitScaleChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    this.renderOptions = {
      ...this.renderOptions,
      scale: {
        ...this.renderOptions.scale,
        orbits: parseFloat(target.value)
      }
    };
  }

  exportWallpaper() {
    // TODO: Implementation wallpaper export
    console.log('Creating wallpaper with settings:', this.renderOptions);
  }

  //Likes
  private loadLikes(): void {
    if (!this.solarSystem) return;
    
    const ids = {
      solar_system: [this.solarSystem.solar_system_id],
      planet: this.solarSystem.planets?.map(p => p.planet_id) || [],
      moon: this.solarSystem.planets?.flatMap(p => p.moons?.map(m => m.moon_id) || []) || []
    };

    const typeMapping = {
      solar_system: LikeableType.SOLAR_SYSTEM,
      planet: LikeableType.PLANET,
      moon: LikeableType.MOON
    };

    let loadedCount = 0;
    const totalToLoad = Object.values(ids).filter(arr => arr.length > 0).length;

    Object.entries(ids).forEach(([type, typeIds]) => {
      if (typeIds.length > 0) {
        this.likesService.getUserLikes(typeIds.join(','), type).subscribe({
          next: (response) => {
            const likeableType = typeMapping[type as keyof typeof typeMapping];
            response.data.forEach((id: number) => this.likedObjects.get(likeableType)?.add(id));
            
            loadedCount++;
            if (loadedCount === totalToLoad) {
              this.isLoading = false;
            }
          },
          error: () => {
            loadedCount++;
            if (loadedCount === totalToLoad) {
              this.isLoading = false;
            }
          }
        });
      }
    });

    // If there is no likes
    if (totalToLoad === 0) {
      this.isLoading = false;
    }
  }

  isLiked(type: LikeableType, id: number): boolean {
    return this.likedObjects.get(type)?.has(id) || false;
  }

  private toggleLikeCache(type: LikeableType, id: number): void {
    const likedSet = this.likedObjects.get(type);
    if (likedSet?.has(id)) {
      likedSet.delete(id);
    } else {
      likedSet?.add(id);
    }
  }
}