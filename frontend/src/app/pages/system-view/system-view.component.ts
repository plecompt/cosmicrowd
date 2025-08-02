import { Component, OnInit, ViewChild } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { NotificationService } from '../../services/notifications/notification.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { SystemAnimationComponent } from '../../components/system-animation/system-animation.component';
import { ModalService } from '../../services/modal/modal.service';
import { LikeableType, LikesService } from '../../services/likes/likes.service';
import { WallpaperService } from '../../services/wallpaper/wallpaper-service';
import { WallpaperSettings } from '../../interfaces/wallpaper/wallpaper.interface';
import { AuthService } from '../../services/auth/auth.service';
import { SolarSystemsService } from '../../services/solar-systems/solar-systems-service';

@Component({
  selector: 'app-system-view',
  imports: [SystemAnimationComponent],
  templateUrl: './system-view.component.html',
  styleUrls: ['../../shared/styles/edit.template.css', './system-view.component.css']
})
export class SystemViewComponent implements OnInit {
  @ViewChild(SystemAnimationComponent) systemAnimationComponent!: SystemAnimationComponent;
  
  solarSystemOwner!: string;
  currentGalaxy: number = 1; //currently there is only one galaxy, so hardcoding to 1, might change in the futur.
  solarSystemId!: number;
  solarSystem!: SolarSystem;
  isLoading: boolean = true;
  
  // Panel state
  isPanelCollapsed = false;
  showRenderOptions = false;
  
  // Follow system
  followedObject: any = null;
  isFollowing = false;
  
  // Render
  renderer: any;
  currentCameraData: any = null;
  currentOrbitalPositions: any = null;

  renderOptions: WallpaperSettings = {
    camera: {
      position: { x: 0, y: 0, z: 0 }, // Will be set when saving
      target: { x: 0, y: 0, z: 0 },   // Will be set when saving
      fov: 0                          // Will be set when saving
    },

    visibility: {
      orbits: true,      // showOrbits
      labels: false,     // Planet/moon labels
      background: true,   // Skybox/stars
      animateOrbits: true // Animate orbit
    },

    effects: {
      brightness: 1,     // brightness
      contrast: 1,       // contrast
      saturation: 1      // saturation
    },

    scale: {
      star: 1,          // scale.star
      planets: 1,       // scale.planets
      moons: 1,         // scale.moons
      orbits: 1         // scale.orbits
    },

    orbitalPositions: {
      planets: {},      // Will be filled when capturing
      moons: {}         // Will be filled when capturing
    },

    metadata: {
      systemId: 0,      // Will be set from solarSystem.id
      createdAt: '',    // Will be set when saving
      resolution: { width: 1920, height: 1080 } // Will be set when saving
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
    private solarSystemsService: SolarSystemsService,
    private modalService: ModalService,
    private likesService: LikesService,
    private notificationService: NotificationService,
    private wallpaperService: WallpaperService,
    private authService: AuthService
  ) { }

  ngOnInit(): void {
    this.solarSystemId = this.route.snapshot.params['id'];
    this.checkOwner();
    this.getSolarSystem();
  }

  //check user is connected and own this system
  checkOwner() {
    this.solarSystemsService.getSolarSystemOwner(this.currentGalaxy, this.solarSystemId).subscribe({
      next: (systems) => {
        this.solarSystemOwner = systems.data.owner;

        // If user is not logged in or don't own this system
        if (this.authService.isLoggedIn() && (localStorage.getItem('user_login') == this.solarSystemOwner)) {
          this.showRenderOptions = true;
        } else {
          this.showRenderOptions = false;
        }
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

  getSolarSystem() {
    this.solarSystemsService.getSolarSystem(this.currentGalaxy, this.solarSystemId).subscribe({
      next: (solarSystem) => {
        this.solarSystem = solarSystem.data.solar_system;
        if (!this.solarSystem) {
          this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
          return;
        }
        if (this.authService.isLoggedIn()){
          this.loadLikes();
        } else {
          this.isLoading = false;
        }
      },
      error: () => {
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
      solar_system: {
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
      visibility: {
        ...this.renderOptions.visibility,
        orbits: !this.renderOptions.visibility.orbits
      }
    };
  }

  onResolutionChange(event: Event): void {
    const target = event.target as HTMLSelectElement;
    const [width, height] = target.value.split('x').map(Number);
    this.renderOptions = {
      ...this.renderOptions,
      metadata: {
        ...this.renderOptions.metadata,
        resolution: { width, height }
      }
    };
  }

  onRenderReady(data: any): void {
    this.renderer = data;
  }

  toggleOrbitalAnimation() {
    this.renderOptions = {
      ...this.renderOptions,
      visibility: {
        ...this.renderOptions.visibility,
        animateOrbits: !this.renderOptions.visibility.animateOrbits
      }
    };
  }

  onBrightnessChange(event: Event) {
    const target = event.target as HTMLInputElement;
    this.renderOptions = {
      ...this.renderOptions,
      effects: {
        ...this.renderOptions.effects,
        brightness: parseFloat(target.value)
      }
    };
  }

  onContrastChange(event: Event) {
    const target = event.target as HTMLInputElement;
    this.renderOptions = {
      ...this.renderOptions,
      effects: {
        ...this.renderOptions.effects,
        contrast: parseFloat(target.value)
      }
    };
  }

  onSaturationChange(event: Event) {
    const target = event.target as HTMLInputElement;
    this.renderOptions = {
      ...this.renderOptions,
      effects: {
        ...this.renderOptions.effects,
        saturation: parseFloat(target.value)
      }
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

  // Export wallpaper as image
  exportWallpaper(): void {
    // get camera position, target and fov
    this.systemAnimationComponent.updateCameraData();
    console.log(this.renderOptions.camera.position.x);

    const canvas = document.querySelector('canvas');
    if (!canvas) return;

    const width = this.renderOptions.metadata.resolution.width;
    const height = this.renderOptions.metadata.resolution.height;
    
    // Force complete render with all post-processing effects
    const renderer = this.systemAnimationComponent?.renderer;
    const composer = this.systemAnimationComponent?.composer; // EffectComposer for bloom
    const scene = this.systemAnimationComponent?.scene;
    const camera = this.systemAnimationComponent?.camera;
    
    if (renderer && scene && camera) {
      // If you have post-processing composer (bloom, etc.)
      if (composer) {
        composer.render();
      } else {
        renderer.render(scene, camera);
      }
      
      // Wait for render to complete
      renderer.domElement.toBlob((blob) => {
        if (!blob) return;
        
        // Create temporary canvas for scaling
        const tempCanvas = document.createElement('canvas');
        const ctx = tempCanvas.getContext('2d');
        if (!ctx) return;

        tempCanvas.width = width;
        tempCanvas.height = height;

        // Create image from blob to scale it
        const img = new Image();
        img.onload = () => {
          ctx.drawImage(img, 0, 0, width, height);
          
          tempCanvas.toBlob((finalBlob) => {
            if (!finalBlob) return;
            
            const url = URL.createObjectURL(finalBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${this.solarSystem.solar_system_name}_wallpaper_${this.renderOptions.metadata.resolution.width}x${this.renderOptions.metadata.resolution.height}.png`;
            link.click();
            
            URL.revokeObjectURL(url);
          }, 'image/png');
        };
        
        img.src = URL.createObjectURL(blob);
      }, 'image/png');
    }
  }

  // Save wallpaper settings to database
  saveWallpaper(): void {
    if (!this.solarSystem) return;
    // get camera position, target and fov
    this.systemAnimationComponent.updateCameraData();
    console.log(this.renderOptions.camera.position.x);

    this.renderOptions.metadata.createdAt = new Date().toString();
    this.renderOptions.metadata.systemId = this.solarSystemId;

    this.wallpaperService.saveWallpaper(this.currentGalaxy, this.solarSystem.solar_system_id, JSON.stringify(this.renderOptions)).subscribe({
      next: () => {
        this.notificationService.showSuccess('You successfully saved your wallpaper settings.', 2000);
      },
      error: (error) => {
        this.notificationService.showError(error.message || 'Something went wrong, please try again later.', 2500);
      }
    });
  }

  // datas to inject in db
  onCameraUpdate(cameraData: any): void {
    this.renderOptions = {
      ...this.renderOptions,
      camera: {
        position: cameraData.position,
        target: cameraData.target,
        fov: cameraData.fov
      }
    };
  }

  onOrbitalPositionsUpdate(positions: any): void {
    this.renderOptions = {
      ...this.renderOptions,
      orbitalPositions: {
        planets: positions.planets,
        moons: positions.moons
      }
    };
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