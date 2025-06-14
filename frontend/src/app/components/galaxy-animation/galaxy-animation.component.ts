import { Component, ElementRef, ViewChild, AfterViewInit, OnDestroy, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import * as THREE from 'three';
import { GalaxiesService } from '../../services/galaxies-service/galaxies.service';
import { SolarSystemAnimation } from '../../interfaces/solar-system/solar-system.interface';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer.js';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass.js';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass.js';

type StarType = 'brown_dwarf' | 'red_dwarf' | 'yellow_dwarf' | 'white_dwarf' | 'red_giant' | 'blue_giant' | 'red_supergiant' | 'blue_supergiant' | 'hypergiant' | 'neutron_star' | 'pulsar' | 'variable' | 'binary' | 'ternary' | 'black_hole';

@Component({
  selector: 'app-galaxy-animation',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './galaxy-animation.component.html',
  styleUrls: ['./galaxy-animation.component.css']
})
export class GalaxyAnimationComponent implements AfterViewInit, OnDestroy {
  @ViewChild('canvasContainer', { static: true }) canvasContainer!: ElementRef;
  @Output() solarSystemClick = new EventEmitter<SolarSystemAnimation>();
  @Output() userInteractionStart = new EventEmitter<void>();
  @Output() userInteractionEnd = new EventEmitter<void>();

  private composer!: EffectComposer;
  private bloomPass!: UnrealBloomPass;
  private scene!: THREE.Scene;
  private camera!: THREE.PerspectiveCamera;
  private renderer!: THREE.WebGLRenderer;
  private animationId!: number;
  private solarSystemMeshes: THREE.Sprite[] = [];
  private solarSystemsData: SolarSystemAnimation[] = [];
  private controls!: OrbitControls;
  private userIsInteracting = false;

  public fps = 0;
  public showFps = false;
  private lastTime = 0;
  private frameCount = 0;

  constructor(private galaxiesService: GalaxiesService) {}

  ngAfterViewInit(): void {
    this.initThreeJS();
    this.setupControls();
    this.setupPostProcessing();
    this.loadSolarSystemsData();
    this.animate();
  }

  ngOnDestroy(): void {
    if (this.animationId) cancelAnimationFrame(this.animationId);
    if (this.controls) this.controls.dispose();
    if (this.renderer) this.renderer.dispose();
    
    if (this.composer) {
      this.composer.dispose();
    }

    this.solarSystemMeshes.forEach(mesh => {
      if (mesh.material) {
        (mesh.material as THREE.Material).dispose();
      }
    });

    window.removeEventListener('resize', this.onWindowResize.bind(this));
  }

  private initThreeJS(): void {
    this.scene = new THREE.Scene();
    this.scene.background = new THREE.Color(0x000010); //same a global background

    this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 2000);
    this.camera.position.set(0, 800, 0);

    this.renderer = new THREE.WebGLRenderer({ antialias: true });
    this.renderer.setSize(window.innerWidth, window.innerHeight);
    this.canvasContainer.nativeElement.appendChild(this.renderer.domElement);

    window.addEventListener('resize', this.onWindowResize.bind(this));
  }

  private setupPostProcessing(): void {
    this.composer = new EffectComposer(this.renderer);
    
    const renderPass = new RenderPass(this.scene, this.camera);
    this.composer.addPass(renderPass);
    
    this.bloomPass = new UnrealBloomPass(
      new THREE.Vector2(window.innerWidth, window.innerHeight),
      1.2,
      0.8,
      0.1
    );
    
    this.composer.addPass(this.bloomPass);
  }

  private setupControls(): void {
    this.controls = new OrbitControls(this.camera, this.renderer.domElement);
    this.controls.enableDamping = true;
    this.controls.dampingFactor = 0.05;
    this.controls.screenSpacePanning = true;
    this.controls.minDistance = 0;
    this.controls.maxDistance = 300;
    this.controls.maxPolarAngle = Math.PI;
    this.controls.minPolarAngle = 0;
    this.controls.rotateSpeed = 0.5;
    this.controls.zoomSpeed = 1.0;
    this.controls.target.set(0, 0, 0);
    
    this.controls.addEventListener('start', () => {
      this.userIsInteracting = true;
      this.userInteractionStart.emit();
    });

    this.controls.addEventListener('end', () => {
      this.userIsInteracting = false;
      this.userInteractionEnd.emit();
    });
  }

  private loadSolarSystemsData(): void {
    this.galaxiesService.getSolarSystemsForAnimation(1).subscribe({
      next: (response: SolarSystemAnimation[]) => {
        console.log('Solar systems received:', response);
        
        if (response?.length > 0) {
          this.solarSystemsData = response;
          this.createSolarSystemsWithSprites();
          this.adjustCameraForGalaxy();
          this.setupClickHandler();
        } else {
          console.warn('No solar systems received');
        }
      },
      error: (error) => {
        console.error('Error loading solar systems:', error);
      }
    });
  }

  private createSolarSystemsWithSprites(): void {
    if (!this.solarSystemsData?.length) return;
    
    // Nettoyer les anciens sprites
    this.solarSystemMeshes.forEach(mesh => {
      if (mesh.material) {
        (mesh.material as THREE.Material).dispose();
      }
      this.scene.remove(mesh);
    });
    this.solarSystemMeshes = [];
    
    // Créer les nouveaux sprites
    this.solarSystemsData.forEach((system) => {
      const position = this.getSystemPosition(system);
      const color = new THREE.Color(this.getStarColorMap()[system.solar_system_type]);
      const size = this.getStarSize(system.solar_system_type);
      
      const texture = this.createStarTexture(color, size, system.solar_system_type);
      
      const material = new THREE.SpriteMaterial({
        map: texture,
        transparent: true,
        blending: THREE.AdditiveBlending
      });
      
      const sprite = new THREE.Sprite(material);
      sprite.position.copy(position);
      sprite.scale.setScalar(size); // A voir
      sprite.userData = { systemData: system };
      
      this.solarSystemMeshes.push(sprite);
      this.scene.add(sprite);
    });
    
    console.log(`${this.solarSystemMeshes.length} sprites created`);
  }

  private getSystemPosition(system: SolarSystemAnimation): THREE.Vector3 {
    return new THREE.Vector3(
      system.solar_system_initial_x || 0,
      system.solar_system_initial_y || 0, 
      system.solar_system_initial_z || 0
    );
  }

  private getStarSize(type: StarType): number {
    const baseSizes: Record<StarType, number> = {
      'brown_dwarf': 0.3,
      'red_dwarf': 0.4,
      'yellow_dwarf': 1,
      'white_dwarf': 0.2,
      'red_giant': 1.5,
      'blue_giant': 1.2,
      'red_supergiant': 2,
      'blue_supergiant': 2,
      'hypergiant': 2.5,
      'neutron_star': 0.1,
      'pulsar': 0.15,
      'variable': 0.8,
      'binary': 1.0,
      'ternary': 1.2,
      'black_hole': 1.8
    };
    return baseSizes[type];
  }

  private createStarTexture(color: THREE.Color, size: number, type: StarType): THREE.Texture {
    const canvas = document.createElement('canvas');
    const canvasSize = 64;
    canvas.width = canvasSize;
    canvas.height = canvasSize;
    
    const ctx = canvas.getContext('2d')!;
    const center = canvasSize / 2;
    const radius = center * 0.8;
    
    const gradient = ctx.createRadialGradient(center, center, 0, center, center, radius);
    
    switch(type) {
      case 'brown_dwarf':
        gradient.addColorStop(0, '#8B4513');
        gradient.addColorStop(0.7, '#4A1810');
        gradient.addColorStop(1, 'transparent');
        break;
      case 'red_dwarf':
        gradient.addColorStop(0, '#FF6B47');
        gradient.addColorStop(0.6, '#CC4125');
        gradient.addColorStop(1, 'transparent');
        break;
      case 'yellow_dwarf':
        gradient.addColorStop(0, '#FFFFFF');
        gradient.addColorStop(0.3, '#FFF2A6');
        gradient.addColorStop(0.7, '#FFE066');
        gradient.addColorStop(1, 'transparent');
        break;
      case 'white_dwarf':
        gradient.addColorStop(0, '#FFFFFF');
        gradient.addColorStop(0.5, '#F5F5FF');
        gradient.addColorStop(1, 'transparent');
        break;
      case 'red_giant':
      case 'red_supergiant':
        gradient.addColorStop(0, '#FFB366');
        gradient.addColorStop(0.4, '#E85D2A');
        gradient.addColorStop(0.8, '#C41E3A');
        gradient.addColorStop(1, 'transparent');
        break;
      case 'blue_giant':
      case 'blue_supergiant':
        gradient.addColorStop(0, '#FFFFFF');
        gradient.addColorStop(0.4, '#B3CCFF');
        gradient.addColorStop(0.7, '#9BB5FF');
        gradient.addColorStop(1, 'transparent');
        break;
      case 'black_hole':
        gradient.addColorStop(0, '#FF8C00');
        gradient.addColorStop(0.5, '#FF4500');
        gradient.addColorStop(0.8, '#DC143C');
        gradient.addColorStop(1, 'transparent');
        break;
      default:
        const colorHex = `#${color.getHexString()}`;
        gradient.addColorStop(0, '#FFFFFF');
        gradient.addColorStop(0.3, colorHex);
        gradient.addColorStop(0.7, colorHex);
        gradient.addColorStop(1, 'transparent');
    }
    
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, canvasSize, canvasSize);
    
    const texture = new THREE.CanvasTexture(canvas);
    texture.needsUpdate = true;
    
    return texture;
  }

  private getStarColorMap(): { [key in StarType]: number } {
    return {
      'brown_dwarf': 0x4A1810,
      'red_dwarf': 0xCC4125,
      'yellow_dwarf': 0xFFF2A6,
      'white_dwarf': 0xF5F5FF,
      'red_giant': 0xE85D2A,
      'blue_giant': 0xB3CCFF,
      'red_supergiant': 0xC41E3A,
      'blue_supergiant': 0x9BB5FF,
      'hypergiant': 0xFFB347,
      'neutron_star': 0xE0E0FF,
      'pulsar': 0x7FFFD4,
      'variable': 0xFFE135,
      'binary': 0xFF9500,
      'ternary': 0xFFB84D,
      'black_hole': 0xFF4500
    };
  }

  private adjustCameraForGalaxy(): void {
    if (this.solarSystemMeshes.length === 0) return;

    const bounds = new THREE.Box3();
    this.solarSystemMeshes.forEach(mesh => bounds.expandByObject(mesh));

    const center = bounds.getCenter(new THREE.Vector3());
    const size = bounds.getSize(new THREE.Vector3());
    const distance = Math.max(size.x, size.y, size.z);

    console.log('distance: ',distance)

    this.camera.position.set(center.x, center.y + distance, center.z);
    this.camera.lookAt(center);
    this.camera.updateProjectionMatrix();
  }

  private setupClickHandler(): void {
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();

    this.renderer.domElement.addEventListener('click', (event) => {
      if (this.userIsInteracting) return;

      mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
      mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

      raycaster.setFromCamera(mouse, this.camera);
      const intersects = raycaster.intersectObjects(this.solarSystemMeshes);

      if (intersects.length > 0) {
        const clickedSystem = intersects[0].object as any;
        console.log(clickedSystem.userData.systemData)
        this.solarSystemClick.emit(clickedSystem.userData.systemData);
      }
    });
  }

  private animate(): void {
    this.animationId = requestAnimationFrame((time) => {
      this.calculateFPS(time);

      if (this.controls) this.controls.update();

      if (!this.userIsInteracting) {
        this.scene.rotation.z += 0.0003;
      }

      // Optimisation : calcul tous les 3 frames
      // if (this.solarSystemMeshes.length > 0 && this.frameCount % 10 === 0) {
      //   this.solarSystemMeshes.forEach((sprite, index) => {
      //     const distance = this.camera.position.distanceTo(sprite.position);
      //     this.adjustSpriteGlowForDistance(sprite, distance);
          
      //     // if (index % 5 === 0) {
      //     //   sprite.rotation.z += 0.002;
      //     // }
      //   });
      // }

      this.composer.render();
      this.animate();
    });
  }

  private adjustSpriteGlowForDistance(sprite: THREE.Sprite, distance: number): void {
    const minDistance = 1;
    const maxDistance = 250;
    const minScale = 1.0;
    const maxScale = 2.5;
    
    const normalizedDistance = Math.min(Math.max((distance - minDistance) / (maxDistance - minDistance), 0), 1);
    const scale = minScale + (normalizedDistance * (maxScale - minScale));
    
    sprite.scale.setScalar(scale);
  }

  private calculateFPS(time: number): void {
    if (!time || time <= 0) return;

    this.frameCount++;

    if (this.lastTime === 0) {
      this.lastTime = time;
      return;
    }

    if (time - this.lastTime >= 1000) {
      this.fps = Math.round((this.frameCount * 1000) / (time - this.lastTime));
      this.frameCount = 0;
      this.lastTime = time;
    }
  }

  toggleFps(): void {
    this.showFps = !this.showFps;
  }

  private onWindowResize(): void {
    this.camera.aspect = window.innerWidth / window.innerHeight;
    this.camera.updateProjectionMatrix();
    this.renderer.setSize(window.innerWidth, window.innerHeight);

    if (this.composer) {
      this.composer.setSize(window.innerWidth, window.innerHeight);
    }
  }
}
