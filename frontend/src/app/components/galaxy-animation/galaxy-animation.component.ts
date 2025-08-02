import { Component, ElementRef, ViewChild, AfterViewInit, OnDestroy } from '@angular/core';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer.js';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass.js';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass.js';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { ModalService } from '../../services/modal/modal.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { NotificationService } from '../../services/notifications/notification.service';
import { NavigationService } from '../../services/navigation/navigation.service';
import { firstValueFrom } from 'rxjs';
import { SolarSystemsService } from '../../services/solar-systems/solar-systems-service';
import { ClaimService } from '../../services/claim/claim-service';

type StarType = keyof ReturnType<GalaxyAnimationComponent['getStarColorMap']>;

@Component({
  selector: 'app-galaxy-animation',
  templateUrl: './galaxy-animation.component.html',
  styleUrls: ['./galaxy-animation.component.css']
})
export class GalaxyAnimationComponent implements AfterViewInit, OnDestroy {
  // Current User
  userLogin: string = localStorage.getItem('user_login') || '';
  userId: string = localStorage.getItem('user_id') || '';

  // Reference to the canvas container DOM element
  @ViewChild('canvasContainer', { static: true }) canvasContainer!: ElementRef;

  // Three.js core objects
  private scene = new THREE.Scene();
  private camera!: THREE.PerspectiveCamera;
  private renderer!: THREE.WebGLRenderer;
  private composer!: EffectComposer;
  private controls!: OrbitControls;
  private bloomPass!: UnrealBloomPass;

  // Animation and interaction variables
  private animationId = 0;
  private raycaster = new THREE.Raycaster();
  private mouse = new THREE.Vector2();

  // Solar systems data and sprites
  private systems: THREE.Sprite[] = [];
  private systemsData: SolarSystem[] = [];
  private scaleFactor = 1;

  constructor(
    private galaxiesService: GalaxiesService,
    private modalService: ModalService,
    private notificationService: NotificationService,
    private navigationService: NavigationService,
    private claimService: ClaimService,
    private solarSystemsService: SolarSystemsService
  ) { }

  ngAfterViewInit(): void {
    // Initialize Three.js scene
    this.init();
    // Start animation loop
    this.animate();
  }

  ngOnDestroy(): void {
    // Cancel animation frame to prevent memory leaks
    cancelAnimationFrame(this.animationId);
    // Dispose of Three.js objects
    this.controls?.dispose();
    this.renderer?.dispose();
    this.composer?.dispose();
    // Dispose of sprite materials
    this.systems.forEach(sprite => (sprite.material as THREE.Material).dispose());
    // Remove event listeners
    window.removeEventListener('resize', this.onResize);
    this.renderer.domElement.removeEventListener('click', this.onClick);
  }

  private init() {
    // Initialize camera with perspective projection
    this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 20000);

    // Initialize WebGL renderer
    this.renderer = new THREE.WebGLRenderer({ antialias: true });
    this.renderer.setSize(window.innerWidth, window.innerHeight);
    this.canvasContainer.nativeElement.appendChild(this.renderer.domElement);

    // Initialize orbit controls for camera movement
    this.controls = new OrbitControls(this.camera, this.renderer.domElement);
    this.controls.enableDamping = true;

    // Set up post-processing effects
    const renderPass = new RenderPass(this.scene, this.camera);
    this.bloomPass = new UnrealBloomPass(new THREE.Vector2(window.innerWidth, window.innerHeight), 1.2, 0.8, 0.1);
    this.composer = new EffectComposer(this.renderer);
    this.composer.addPass(renderPass);
    this.composer.addPass(this.bloomPass);

    // Set dark space background
    this.scene.background = new THREE.Color(0x000010);

    // Add event listeners
    window.addEventListener('resize', this.onResize);
    this.renderer.domElement.addEventListener('click', this.onClick);

    // Load solar systems data
    this.loadSystems();

    // Set initial camera position for panoramic view
    this.setCameraPositionAndOrientation(16, -265, 45, -160, 80);

    // Load SkyBox
    this.createSkyBox();
  }

  private onResize = () => {
    // Update camera aspect ratio on window resize
    this.camera.aspect = window.innerWidth / window.innerHeight;
    this.camera.updateProjectionMatrix();
    // Update renderer size
    this.renderer.setSize(window.innerWidth, window.innerHeight);
    // Update composer size
    this.composer.setSize(window.innerWidth, window.innerHeight);
  };

  private loadSystems() {
    // Fetch solar systems data from service
    this.galaxiesService.getSolarSystemsForAnimation(1).subscribe({
      next: (systems) => {
        this.systemsData = systems.data;
        // Create sprites for each system
        this.createSprites();
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later.', 5000, '/home');
      }
    });
  }

  private createSprites() {
    // Remove existing sprites from scene
    this.systems.forEach(s => this.scene.remove(s));
    this.systems = [];

    // Create sprite for each solar system
    this.systemsData.forEach(data => {
      // Get star color and size based on type
      const color = new THREE.Color(this.getStarColorMap()[data.solar_system_type]);
      const size = this.getStarSize(data.solar_system_type);
      // Create texture with gradient effect
      const texture = this.createTexture(color);
      // Create sprite material with additive blending for glow effect
      const material = new THREE.SpriteMaterial({ map: texture, transparent: true, blending: THREE.AdditiveBlending });
      const sprite = new THREE.Sprite(material);
      // Set sprite size
      sprite.scale.setScalar(size);
      // Set sprite position in 3D space
      sprite.position.set(data.solar_system_initial_x * this.scaleFactor || 0, data.solar_system_initial_y * this.scaleFactor || 0, data.solar_system_initial_z * this.scaleFactor || 0);
      // Store system data in sprite for later reference
      sprite.userData = data;
      // Add sprite to scene and array
      this.scene.add(sprite);
      this.systems.push(sprite);
    });
  }

  private createSkyBox() {
    const loader = new THREE.CubeTextureLoader();
    const skybox = loader.load([
      'skybox/galaxy/right.png',
      'skybox/galaxy/left.png',
      'skybox/galaxy/top.png',
      'skybox/galaxy/bottom.png',
      'skybox/galaxy/front.png',
      'skybox/galaxy/back.png'
    ]);
    this.scene.background = skybox;
  }

  private createTexture(color: THREE.Color): THREE.Texture {
    // Create canvas for texture generation
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = 64;
    const ctx = canvas.getContext('2d')!;
    // Create radial gradient for star glow effect
    const gradient = ctx.createRadialGradient(32, 32, 0, 32, 32, 28);
    gradient.addColorStop(0, '#FFF');
    gradient.addColorStop(0.5, `#${color.getHexString()}`);
    gradient.addColorStop(1, 'transparent');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, 64, 64);
    // Return Three.js texture from canvas
    return new THREE.CanvasTexture(canvas);
  }

  private getStarSize(type: StarType): number {
    // Define relative sizes for different star types
    const sizes: Record<StarType, number> = {
      'brown_dwarf': 0.3, 'red_dwarf': 0.4, 'yellow_dwarf': 1, 'white_dwarf': 0.2,
      'red_giant': 1.5, 'blue_giant': 1.2, 'red_supergiant': 2, 'blue_supergiant': 2,
      'hypergiant': 2.5, 'neutron_star': 0.1, 'pulsar': 0.15, 'variable': 0.8,
      'binary': 1.0, 'ternary': 1.2, 'black_hole': 1.8
    };
    return sizes[type] * this.scaleFactor;
  }

  private getStarColorMap() {
    // Define color mapping for different star types
    return {
      'brown_dwarf': 0x4A1810, 'red_dwarf': 0xCC4125, 'yellow_dwarf': 0xFFF2A6, 'white_dwarf': 0xF5F5FF,
      'red_giant': 0xE85D2A, 'blue_giant': 0xB3CCFF, 'red_supergiant': 0xC41E3A, 'blue_supergiant': 0x9BB5FF,
      'hypergiant': 0xFFB347, 'neutron_star': 0xE0E0FF, 'pulsar': 0x7FFFD4, 'variable': 0xFFE135,
      'binary': 0xFF9500, 'ternary': 0xFFB84D, 'black_hole': 0xFF4500
    };
  }

  private animate(): void {
    // Request next animation frame
    this.animationId = requestAnimationFrame(() => this.animate());
    // Update orbit controls
    this.controls?.update();
    // Apply perpetual rotation to scene
    this.scene.rotation.z += 0.0003;
    // Render scene with post-processing
    this.composer.render();
  }

  private onClick = (event: MouseEvent) => {
    // Calculate mouse position in normalized device coordinates
    const bounds = this.renderer.domElement.getBoundingClientRect();
    this.mouse.x = ((event.clientX - bounds.left) / bounds.width) * 2 - 1;
    this.mouse.y = -((event.clientY - bounds.top) / bounds.height) * 2 + 1;

    // Check for intersections with solar system sprites
    this.raycaster.setFromCamera(this.mouse, this.camera);
    const intersects = this.raycaster.intersectObjects(this.systems);

    // If a sprite is clicked, show modal with system information
    if (intersects.length > 0) {
      const selectedSprite = intersects[0].object;
      const starData = selectedSprite.userData;
      //we need to load detailed data...
      this.solarSystemsService.getSolarSystem(starData['galaxy_id'], starData['solar_system_id']).subscribe({
        next: (systemInformation) => {
          this.showModal(systemInformation.data.solar_system);
        },
        error: () => {
          this.notificationService.showError('Something went wrong, please try again later.', 5000, '/home');
        }
      })
    }
  };

private async showModal(starData: any): Promise<void> {
  // Extract star name and owner, if system is already claimed
  const name = starData.solar_system_name;
  let owner = "";

  // Wait for the owner to be fetched if system is claimed
  if (starData.user_id != null) {
    try {
      const response = await firstValueFrom(
        this.solarSystemsService.getSolarSystemOwner(1, starData.solar_system_id)
      );
      owner = response.data.owner;
    } catch (error) {
      this.notificationService.showError('Something went wrong, please try again later.', 5000, '/home');
      return;
    }
  }

  // Format system information for display as HTML
  const formattedMessage = `
      <div class="info-grid" style="margin-top: 15px;">
        <div class="info-item">
          <strong>Description:</strong> ${starData.solar_system_desc}
        </div>
        <div class="info-item">
          <strong>Type:</strong> ${this.getFormattedTypeForSystem(starData)}
        </div>
        <div class="info-item">
          <strong>Diameter:</strong> ${starData.solar_system_diameter.toString()} km
        </div>
        <div class="info-item">
          <strong>Mass:</strong> ${starData.solar_system_mass} x 10^24 kg
        </div>
        <div class="info-item">
          <strong>Surface Temperature:</strong> ${starData.solar_system_surface_temp} K
        </div>
        <div class="info-item">
          <strong>Gravity:</strong> ${starData.solar_system_gravity} m.s²
        </div>
        <div class="info-item">
          <strong>Planets:</strong> ${this.getPlanetsCountForSystem(starData) || '0'}
        </div>
        <div class="info-item">
          <strong>Moons:</strong> ${this.getMoonsCountForSystem(starData) || '0'}
        </div>
        <div class="info-item owner">
          <strong>Owner:</strong> ${owner || 'Unclaimed'}
        </div>
      </div>
    </div>
  `;

    // Open modal with system information
    this.modalService.show({
      title: name,
      content: formattedMessage,
      showClaim: starData.user_login ? false : true,
      showView: true,
      onClaim: () => {
        // Check if user can claim this system
        this.claimService.isSolarSystemClaimable(parseInt(this.userId), starData.galaxy_id, starData.solar_system_id).subscribe({
          next: () => {
              // User can claim, proceed with claim
              this.claimService.claimSolarSystem(parseInt(this.userId), starData.galaxy_id, starData.solar_system_id).subscribe({
                next: (claimResponse: any) => {
                  starData.user_login = this.userLogin;
                  starData.user_id = this.userId;
                  this.notificationService.showSuccess(claimResponse.message, 3000, '/systems');
                },
                error: (error) => {
                  this.notificationService.showError(error.message || 'Something went wrong, please try again later.', 5000, '/home');
                  return ;
                }
              });
          },
          error: (error) => {
            this.notificationService.showError(error.message || 'Something went wrong, please try again later.', 5000, '/home');
          }
        });
      },
      onView: () => {
        // User clicked on view, sending him to view-system
        this.navigationService.navigateTo(`/view-system/${starData.solar_system_id}`);
      }
    });
  }


  getMoonsCountForSystem(system: SolarSystem): number {
    return system.planets.reduce((acc, planet) => acc + planet.moons.length, 0);
  }

  getPlanetsCountForSystem(system: SolarSystem): number {
    return system.planets.length;
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

  setCameraPositionAndOrientation(posX: number, posY: number, posZ: number, yawDeg: number, pitchDeg: number): void {
    // Set camera position in 3D space
    this.camera.position.set(posX, posY, posZ);

    // Convert degrees to radians for calculation
    const yaw = THREE.MathUtils.degToRad(yawDeg);
    const pitch = THREE.MathUtils.degToRad(pitchDeg);

    // Calculate direction vector from yaw and pitch angles
    const dir = new THREE.Vector3();
    dir.x = Math.sin(yaw) * Math.cos(pitch);
    dir.y = Math.sin(pitch);
    dir.z = Math.cos(yaw) * Math.cos(pitch);

    // Calculate look-at point ahead of camera
    const lookAtPos = new THREE.Vector3();
    lookAtPos.addVectors(this.camera.position, dir);

    // Set camera orientation to look at calculated point
    this.camera.lookAt(lookAtPos);

    // Set controls target to galaxy center for better navigation
    this.controls.target.set(0, 0, 0);

    // Update controls to apply changes
    this.controls.update();
  }
}
