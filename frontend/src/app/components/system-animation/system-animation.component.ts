import { Component, ElementRef, Input, OnChanges, OnInit, SimpleChanges, ViewChild } from '@angular/core';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { PlanetType } from '../../interfaces/solar-system/planet.interface';
import { ModalService } from '../../services/modal/modal.service';
import { LikeableType, LikesService } from '../../services/likes/likes.service';
import { NotificationService } from '../../services/notifications/notification.service';

@Component({
  selector: 'app-system-animation',
  templateUrl: './system-animation.component.html',
  styleUrls: ['./system-animation.component.css']
})
export class SystemAnimationComponent implements OnInit, OnChanges {
  @ViewChild('container', { static: true }) containerRef!: ElementRef;
  @Input() solarSystem!: SolarSystem;
  @Input() viewMode: 'view' | 'edit' = 'view';
  @Input() renderConfig: any = {};

  scene!: THREE.Scene;
  camera!: THREE.PerspectiveCamera;
  renderer!: THREE.WebGLRenderer;
  composer!: EffectComposer;
  controls!: OrbitControls;
  animationId!: number;
  raycaster = new THREE.Raycaster();
  mouse = new THREE.Vector2();
  clickableObjects: THREE.Mesh[] = [];
  moonEllipses: THREE.LineLoop[] = [];
  moonDistanceScale: number = 0;

  constructor(
    private modalService: ModalService,
    private likesService: LikesService,
    private notificationService: NotificationService
  ) {}

  ngOnInit(): void {
    if (this.solarSystem) this.initThreeJS();
    window.addEventListener('resize', this.onWindowResize.bind(this));
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['solarSystem'] && this.solarSystem && this.scene) {
      this.clearScene();
      this.createSolarSystem();
    }
  }

  ngOnDestroy(): void {
    window.removeEventListener('resize', this.onWindowResize.bind(this));
    this.renderer?.domElement.removeEventListener('click', this.onCanvasClick.bind(this));
    if (this.animationId) cancelAnimationFrame(this.animationId);
    this.renderer?.dispose();
  }

  private onWindowResize(): void {
    const { clientWidth: width, clientHeight: height } = this.containerRef.nativeElement;
    this.camera.aspect = width / height;
    this.camera.updateProjectionMatrix();
    this.renderer.setSize(width, height);
    this.composer.setSize(width, height);
  }

  private initThreeJS(): void {
    const container = this.containerRef.nativeElement;
    const { clientWidth: width, clientHeight: height } = container;

    this.setupScene();
    this.setupCamera(width, height);
    this.setupRenderer(width, height, container);
    this.setupControls();
    this.setupLights();
    this.setupPostProcessing();
    this.createSolarSystem();
    this.animate();
  }

  private setupScene(): void {
    this.scene = new THREE.Scene();
    this.scene.background = new THREE.Color(0x000010);
  }

  private setupCamera(width: number, height: number): void {
    this.camera = new THREE.PerspectiveCamera(75, width / height, 1, 15000);
    this.camera.position.set(0, 300, 1500);
  }

  private setupRenderer(width: number, height: number, container: HTMLElement): void {
    this.renderer = new THREE.WebGLRenderer({ antialias: true });
    this.renderer.setSize(width, height);
    this.renderer.setPixelRatio(window.devicePixelRatio);
    this.renderer.outputColorSpace = THREE.SRGBColorSpace;
    this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
    this.renderer.toneMappingExposure = 1.5;
    container.appendChild(this.renderer.domElement);
    this.renderer.domElement.addEventListener('click', this.onCanvasClick.bind(this));
  }

  private setupControls(): void {
    this.controls = new OrbitControls(this.camera, this.renderer.domElement);
    this.controls.enableDamping = true;
  }

  private setupLights(): void {
    this.scene.add(new THREE.AmbientLight(0xffffff, 0.6));
    const pointLight = new THREE.PointLight(0xfff1aa, 3, 3000);
    this.scene.add(pointLight);
  }

  private setupPostProcessing(): void {
    const container = this.containerRef.nativeElement;
    const renderScene = new RenderPass(this.scene, this.camera);
    const bloomPass = new UnrealBloomPass(
      new THREE.Vector2(container.clientWidth, container.clientHeight),
      1.5, 0.4, 0.85
    );
    bloomPass.threshold = 0;
    bloomPass.strength = 2.5;
    bloomPass.radius = 0.8;

    this.composer = new EffectComposer(this.renderer);
    this.composer.addPass(renderScene);
    this.composer.addPass(bloomPass);
  }

  private onCanvasClick(event: MouseEvent): void {
    const container = this.containerRef.nativeElement;
    const rect = container.getBoundingClientRect();
    
    this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    this.raycaster.setFromCamera(this.mouse, this.camera);
    const intersects = this.raycaster.intersectObjects(this.clickableObjects);

    if (intersects.length > 0) {
      this.showModal(intersects[0].object.userData);
    }
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
    const dataMap: {[key: string]: any} = {
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

  private createInfoGrid(items: Array<{label: string, value: any}>): string {
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

  private clearScene(): void {
    this.scene.children
      .filter(child => !(child instanceof THREE.Light))
      .forEach(obj => this.scene.remove(obj));
    this.clickableObjects = [];
  }

  private createSolarSystem(): void {
    const { starScale, planetScale, moonScale } = this.calculateSizeScales();
    const { planetDistanceScale, moonDistanceScale } = this.calculateDistanceScales();
    this.moonDistanceScale = moonDistanceScale;

    this.createStar(starScale);
    this.solarSystem.planets.forEach(planet => 
      this.createPlanet(planet, planetScale, planetDistanceScale, moonScale, moonDistanceScale)
    );
    this.addSkybox();
  }

  private createStar(starScale: number): void {
    const starSize = this.clamp(this.solarSystem.solar_system_diameter * starScale, 10, 300);
    const star = new THREE.Mesh(
      new THREE.SphereGeometry(starSize, 64, 64),
      new THREE.MeshBasicMaterial({ color: 0xffffaa })
    );
    star.userData = { type: 'star', data: this.solarSystem };
    this.scene.add(star);
    this.clickableObjects.push(star);
  }

  private createEllipseGeometry(body: any, x1: number, y1: number, z1: number, x2: number, y2: number, z2: number): THREE.BufferGeometry {
    const point1 = new THREE.Vector3(x1, y1, z1);
    const point2 = new THREE.Vector3(x2, y2, z2);
    const center = point1.clone().add(point2).multiplyScalar(0.5);
    const semiMajorAxis = point1.distanceTo(point2) / 2;
    const direction = point1.clone().sub(center).normalize();
    
    const inclination = ((body.planet_orbital_inclination || body.moon_orbital_inclination || 0) * Math.PI) / 180;
    const longitudeOfAscendingNode = ((body.planet_inclination_angle || body.moon_inclination_angle || 0) * Math.PI) / 180;
    
    let perpendicular = new THREE.Vector3(0, 1, 0);
    perpendicular.applyAxisAngle(new THREE.Vector3(1, 0, 0), inclination);
    perpendicular.applyAxisAngle(new THREE.Vector3(0, 1, 0), longitudeOfAscendingNode);
    perpendicular = perpendicular.cross(direction).normalize().cross(direction).normalize();
    
    const semiMinorAxis = semiMajorAxis * 0.8;
    const points: THREE.Vector3[] = [];
    
    for (let i = 0; i <= 64; i++) {
      const angle = (i / 64) * Math.PI * 2;
      const point = center.clone()
        .add(direction.clone().multiplyScalar(semiMajorAxis * Math.cos(angle)))
        .add(perpendicular.clone().multiplyScalar(semiMinorAxis * Math.sin(angle)));
      points.push(point);
    }
    
    return new THREE.BufferGeometry().setFromPoints(points);
  }

  private createEllipse(body: any, x1: number, y1: number, z1: number, x2: number, y2: number, z2: number, color: number = 0x555555): void {
    const geometry = this.createEllipseGeometry(body, x1, y1, z1, x2, y2, z2);
    const material = new THREE.LineBasicMaterial({ color, transparent: true, opacity: 0.3 });
    const ellipse = new THREE.LineLoop(geometry, material);
    this.scene.add(ellipse);
  }

  private clearMoonOrbits(): void {
    this.moonEllipses.forEach(ellipse => {
      this.scene.remove(ellipse);
      ellipse.geometry.dispose();
      (ellipse.material as THREE.Material).dispose();
    });
    this.moonEllipses = [];
  }

  private updateMoonOrbits(): void {
    this.clearMoonOrbits();
    if (!this.solarSystem?.planets) return;

    this.solarSystem.planets.forEach(planet => {
      const planetMesh = this.scene.children.find((child: any) => 
        child.userData['type'] === 'planet' && child.userData['data'] === planet
      );
      
      if (planetMesh && planet.moons) {
        planet.moons.forEach(moon => {
          const apogeeDistance = (moon.moon_apogee || 0) * this.moonDistanceScale;
          const perigeeDistance = (moon.moon_perigee || 0) * this.moonDistanceScale;
          
          const geometry = this.createEllipseGeometry(
            moon,
            planetMesh.position.x + apogeeDistance, planetMesh.position.y, planetMesh.position.z,
            planetMesh.position.x - perigeeDistance, planetMesh.position.y, planetMesh.position.z
          );
          
          const ellipse = new THREE.LineLoop(
            geometry,
            new THREE.LineBasicMaterial({ color: 0x888888, transparent: true, opacity: 0.3 })
          );
          
          this.moonEllipses.push(ellipse);
          this.scene.add(ellipse);
        });
      }
    });
  }

  private createPlanet(planet: any, planetScale: number, planetDistanceScale: number, moonScale: number, moonDistanceScale: number): void {
    const size = this.clamp((planet.planet_diameter || 10000) * planetScale, 5, 100);
    const apogeeDistance = (planet.planet_apogee || 0) * planetDistanceScale;
    const perigeeDistance = (planet.planet_perigee || 0) * planetDistanceScale;
    
    this.createEllipse(planet, apogeeDistance, 0, 0, -perigeeDistance, 0, 0, 0x5555ff);
    
    const initialAngle = (planet.planet_orbital_longitude || 0) * (Math.PI / 180);
    const planetPos = this.getOrbitPosition(planet, planetDistanceScale, initialAngle);

    const planetMesh = new THREE.Mesh(
      new THREE.SphereGeometry(size, 32, 32),
      this.getPlanetMaterial(planet.planet_type as PlanetType)
    );
    planetMesh.position.copy(planetPos);
    planetMesh.userData = { 
      type: 'planet', 
      data: planet,
      orbitalAngle: initialAngle,
      distanceScale: planetDistanceScale
    };
    this.clickableObjects.push(planetMesh);
    this.scene.add(planetMesh);
    
    planet.moons?.forEach((moon: any) => this.createMoon(moon, planetPos, moonScale, moonDistanceScale));
  }

  private createMoon(moon: any, planetPos: THREE.Vector3, moonScale: number, moonDistanceScale: number): void {
    const moonSize = this.clamp((moon.moon_diameter || 3000) * moonScale, 2, 20);
    const initialAngle = (moon.moon_orbital_longitude || 0) * (Math.PI / 180);
    const moonRelativePos = this.getOrbitPosition(moon, moonDistanceScale, initialAngle);
    const moonPos = planetPos.clone().add(moonRelativePos);

    const moonMesh = new THREE.Mesh(
      new THREE.SphereGeometry(moonSize, 16, 16),
      new THREE.MeshStandardMaterial({ color: 0xbbbbbb, roughness: 1 })
    );
    moonMesh.position.copy(moonPos);
    moonMesh.userData = { 
      type: 'moon', 
      data: moon,
      orbitalAngle: initialAngle,
      distanceScale: moonDistanceScale,
      planetPos: planetPos
    };
    this.clickableObjects.push(moonMesh);
    this.scene.add(moonMesh);
  }

  private getOrbitPosition(body: any, distanceScale: number, angle: number): THREE.Vector3 {
    const apogeeDistance = (body.planet_apogee || body.moon_apogee || 0) * distanceScale;
    const perigeeDistance = (body.planet_perigee || body.moon_perigee || 0) * distanceScale;
    
    if (apogeeDistance === 0 && perigeeDistance === 0) return new THREE.Vector3(0, 0, 0);
    
    const point1 = new THREE.Vector3(apogeeDistance, 0, 0);
    const point2 = new THREE.Vector3(-perigeeDistance, 0, 0);
    const center = point1.clone().add(point2).multiplyScalar(0.5);
    const semiMajorAxis = point1.distanceTo(point2) / 2;
    const direction = point1.clone().sub(center).normalize();
    
    const inclination = ((body.planet_orbital_inclination || body.moon_orbital_inclination || 0) * Math.PI) / 180;
    const longitudeOfAscendingNode = ((body.planet_inclination_angle || body.moon_inclination_angle || 0) * Math.PI) / 180;
    
    let perpendicular = new THREE.Vector3(0, 1, 0);
    perpendicular.applyAxisAngle(new THREE.Vector3(1, 0, 0), inclination);
    perpendicular.applyAxisAngle(new THREE.Vector3(0, 1, 0), longitudeOfAscendingNode);
    perpendicular = perpendicular.cross(direction).normalize().cross(direction).normalize();
    
    const semiMinorAxis = semiMajorAxis * 0.8;
    
    return center.clone()
      .add(direction.clone().multiplyScalar(semiMajorAxis * Math.cos(angle)))
      .add(perpendicular.clone().multiplyScalar(semiMinorAxis * Math.sin(angle)));
  }

  private calculateDistanceScales(): { planetDistanceScale: number, moonDistanceScale: number } {
    let maxPlanetDist = 0;
    let maxMoonDist = 0;

    this.solarSystem.planets.forEach(planet => {
      maxPlanetDist = Math.max(maxPlanetDist, planet.planet_perigee || 0, planet.planet_apogee || 0);
      planet.moons?.forEach(moon => {
        maxMoonDist = Math.max(maxMoonDist, moon.moon_perigee || 0, moon.moon_apogee || 0);
      });
    });

    return {
      planetDistanceScale: 5000 / (maxPlanetDist || 1),
      moonDistanceScale: 500 / (maxMoonDist || 1)
    };
  }

  private animateOrbits(): void {
    if (!this.solarSystem?.planets) return;

    this.solarSystem.planets.forEach(planet => {
      const planetOrbitalPeriod = planet.planet_orbital_period || 365;
      const planetSpeed = 360 / planetOrbitalPeriod;
      planet.planet_orbital_longitude = (planet.planet_orbital_longitude + planetSpeed * 0.5) % 360;
      
      const planetMesh = this.clickableObjects.find(obj => 
        obj.userData['type'] === 'planet' && obj.userData['data'].planet_id === planet.planet_id
      );
      
      if (planetMesh) {
        const angle = (planet.planet_orbital_longitude * Math.PI) / 180;
        planetMesh.position.copy(this.getOrbitPosition(planet, planetMesh.userData['distanceScale'], angle));
        planetMesh.userData['orbitalAngle'] = angle;
        
        planet.moons?.forEach(moon => {
          const moonOrbitalPeriod = moon.moon_orbital_period || 27;
          const moonSpeed = 360 / moonOrbitalPeriod;
          moon.moon_orbital_longitude = (moon.moon_orbital_longitude + moonSpeed * 0.5) % 360;
          
          const moonMesh = this.clickableObjects.find(obj => 
            obj.userData['type'] === 'moon' && obj.userData['data'].moon_id === moon.moon_id
          );
          
          if (moonMesh) {
            const moonAngle = (moon.moon_orbital_longitude * Math.PI) / 180;
            const moonRelativePos = this.getOrbitPosition(moon, moonMesh.userData['distanceScale'], moonAngle);
            moonMesh.position.copy(planetMesh.position.clone().add(moonRelativePos));
            moonMesh.userData['orbitalAngle'] = moonAngle;
            moonMesh.userData['planetPos'] = planetMesh.position.clone();
          }
        });
      }
    });
  }

  private animate(): void {
    this.animationId = requestAnimationFrame(() => this.animate());

    const clampVal = 8000;
    this.camera.position.x = this.clamp(this.camera.position.x, -clampVal, clampVal);
    this.camera.position.y = this.clamp(this.camera.position.y, -clampVal, clampVal);
    this.camera.position.z = this.clamp(this.camera.position.z, -clampVal, clampVal);

    this.animateOrbits();
    this.updateMoonOrbits();
    this.composer.render();
  }

  private clamp(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), max);
  }

  private calculateSizeScales(): { starScale: number, planetScale: number, moonScale: number } {
    const starSize = this.solarSystem.solar_system_diameter || 1000000;
    const planetSizes = this.solarSystem.planets.map(p => p.planet_diameter || 10000);
    const moonSizes = this.solarSystem.planets.flatMap(p => p.moons?.map(m => m.moon_diameter || 3000) || []);

    const planetAvg = planetSizes.reduce((a, b) => a + b, 0) / planetSizes.length || 1;
    const moonAvg = moonSizes.reduce((a, b) => a + b, 0) / moonSizes.length || 1;

    return {
      starScale: 150 / starSize,
      planetScale: 30 / planetAvg,
      moonScale: 10 / moonAvg
    };
  }

  getPlanetMaterial(type: PlanetType): THREE.Material {
    const textureLoader = new THREE.TextureLoader();
    const textureMap: { [key in PlanetType]: string } = {
      terrestrial: 'planets-textures/terrestrial.png',
      gas: 'planets-textures/gas.png',
      ice: 'planets-textures/ice.png',
      super_earth: 'planets-textures/super-earth.png',
      sub_neptune: 'planets-textures/sub-neptune.png',
      dwarf: 'planets-textures/dwarf.png',
      lava: 'planets-textures/lava.png',
      carbon: 'planets-textures/carbon.png',
      ocean: 'planets-textures/ocean.png',
    };

    return new THREE.MeshStandardMaterial({
      map: textureLoader.load(textureMap[type]),
      roughness: 0.8,
      metalness: 0.3,
    });
  }

  private addSkybox(): void {
    const loader = new THREE.CubeTextureLoader();
    this.scene.background = loader.load([
      'skybox/system/right.png', 'skybox/system/left.png',
      'skybox/system/top.png', 'skybox/system/bottom.png',
      'skybox/system/front.png', 'skybox/system/back.png'
    ]);
  }
}
