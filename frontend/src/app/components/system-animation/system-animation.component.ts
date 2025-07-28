import { Component, ElementRef, EventEmitter, Input, OnChanges, OnInit, Output, SimpleChanges, ViewChild } from '@angular/core';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { PlanetType } from '../../interfaces/solar-system/planet.interface';
import { WallpaperSettings } from '../../interfaces/wallpaper/wallpaper.interface';

@Component({
  selector: 'app-system-animation',
  templateUrl: './system-animation.component.html',
  styleUrls: ['./system-animation.component.css']
})
export class SystemAnimationComponent implements OnInit, OnChanges {
  // #region Component Properties
  @ViewChild('container', { static: true }) containerRef!: ElementRef;
  
  @Input() solarSystem!: SolarSystem;
  @Input() viewMode: 'view' | 'edit' = 'view';
  @Input() followedObject: any = null;
  @Input() renderOptions: WallpaperSettings = {
    camera: {
      position: { x: 0, y: 0, z: 0 },
      target: { x: 0, y: 0, z: 0 },
      fov: 75
    },
    visibility: {
      orbits: true,
      labels: true,
      background: true,
      animateOrbits: true
    },
    effects: {
      brightness: 1,
      contrast: 1,
      saturation: 1
    },
    scale: {
      star: 1.0,
      planets: 1.0,
      moons: 1.0,
      orbits: 1.0
    },
    orbitalPositions: {
      planets: {},
      moons: {}
    },
    metadata: {
      systemId: 0,
      createdAt: '',
      resolution: { width: 1920, height: 1080 }
    }
  };

  @Output() objectClicked = new EventEmitter<{type: string, data: any}>();
  @Output() renderReady = new EventEmitter<any>();
  @Output() cameraUpdate = new EventEmitter<{
    position: { x: number, y: number, z: number },
    target: { x: number, y: number, z: number },
    fov: number
  }>();
  @Output() orbitalPositionsUpdate = new EventEmitter<{
    planets: { [key: string]: { x: number, y: number, z: number } },
    moons: { [key: string]: { x: number, y: number, z: number } }
  }>();

  // Three.js core objects
  private scene!: THREE.Scene;
  private camera!: THREE.PerspectiveCamera;
  private renderer!: THREE.WebGLRenderer;
  private composer!: EffectComposer;
  private controls!: OrbitControls;
  private isInitialized = false;

  // Interaction and tracking
  private raycaster = new THREE.Raycaster();
  private mouse = new THREE.Vector2();
  private clickableObjects: THREE.Mesh[] = [];
  private moonEllipses: THREE.LineLoop[] = [];
  private animationId: number = 0;
  private lastScales: { starScale: number, planetScale: number, moonScale: number } | null = null;

  // Scale factors
  private moonDistanceScale: number = 1;

  // Throttling
  private cameraUpdateTimeout: any;
  private orbitalUpdateTimeout: any;
  // #endregion

  // #region Lifecycle
  ngOnInit(): void {
    this.setupScene();
    this.setupCamera();
    this.setupRenderer();
    this.setupControls();
    this.setupLights();
    this.setupPostProcessing();
    this.setupEventListeners();
    this.createSolarSystem();
    this.animate();

    this.isInitialized = true;
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (!this.isInitialized) {
      return;
    }

    if (changes['solarSystem'] && !changes['solarSystem'].firstChange) {
      this.clearScene();
      this.createSolarSystem();
    }

    if (changes['followedObject']) {
      if (this.followedObject) {
        this.startFollowing();
      } else {
        this.stopFollowing();
      }
    }

    if (changes['renderOptions'] && !changes['renderOptions'].firstChange) {
      this.updateRenderSettings();
    }
  }

  ngOnDestroy(): void {
    if (this.animationId) {
      cancelAnimationFrame(this.animationId);
    }
    this.renderer?.dispose();
  }
  // #endregion

  // #region Scene Setup
  private setupScene(): void {
    this.scene = new THREE.Scene();
  }

  private setupCamera(): void {
    const container = this.containerRef.nativeElement;
    const aspectRatio = container.clientWidth / container.clientHeight;
    this.camera = new THREE.PerspectiveCamera(75, aspectRatio, 0.1, 20000);
    this.camera.position.set(0, 500, 1500);
  }

  private setupRenderer(): void {
    const container = this.containerRef.nativeElement;
    this.renderer = new THREE.WebGLRenderer({ antialias: true, preserveDrawingBuffer: true });
    this.renderer.setSize(container.clientWidth, container.clientHeight);
    this.renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(this.renderer.domElement);
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

  private addSkybox(): void {
    const loader = new THREE.CubeTextureLoader();
    this.scene.background = loader.load([
      'skybox/system/right.png', 'skybox/system/left.png',
      'skybox/system/top.png', 'skybox/system/bottom.png',
      'skybox/system/front.png', 'skybox/system/back.png'
    ]);
  }
  // #endregion

  // #region Controls Setup
  private setupControls(): void {
    this.controls = new OrbitControls(this.camera, this.renderer.domElement);
    this.controls.enableDamping = true;
    this.controls.addEventListener('change', () => this.onCameraChange());
  }

  private setupEventListeners(): void {
    this.renderer.domElement.addEventListener('click', (event) => this.onCanvasClick(event));
    
    window.addEventListener('resize', () => {
      const container = this.containerRef.nativeElement;
      this.camera.aspect = container.clientWidth / container.clientHeight;
      this.camera.updateProjectionMatrix();
      this.renderer.setSize(container.clientWidth, container.clientHeight);
      this.composer.setSize(container.clientWidth, container.clientHeight);
    });
  }

  private onCanvasClick(event: MouseEvent): void {
    const container = this.containerRef.nativeElement;
    const rect = container.getBoundingClientRect();
    
    this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    this.raycaster.setFromCamera(this.mouse, this.camera);
    const intersects = this.raycaster.intersectObjects(this.clickableObjects);

    if (intersects.length > 0) {
      const intersectedObject = intersects[0].object as THREE.Mesh;
      this.objectClicked.emit({
        type: intersectedObject.userData['type'],
        data: intersectedObject.userData['data']
      });
    }
  }
  // #endregion

  // #region Camera Management
  private onCameraChange(): void {
    if (this.cameraUpdateTimeout) {
      clearTimeout(this.cameraUpdateTimeout);
    }

    this.cameraUpdateTimeout = setTimeout(() => {
      this.updateCameraData();
    }, 500);
  }

  private updateCameraData(): void {
    if (this.camera && this.controls) {
      this.cameraUpdate.emit({
        position: {
          x: this.camera.position.x,
          y: this.camera.position.y,
          z: this.camera.position.z
        },
        target: {
          x: this.controls.target.x,
          y: this.controls.target.y,
          z: this.controls.target.z
        },
        fov: this.camera.fov
      });
    }
  }

  viewObject(obj: any, type: string): void {
    const objectId = obj[`${type}_id`];
    const targetObject = this.clickableObjects.find(mesh => 
      mesh.userData['type'] === type && mesh.userData['data'][`${type}_id`] === objectId
    );

    if (targetObject) {
      const targetPosition = targetObject.position.clone();
      const distance = this.calculateViewDistance(targetObject);
      
      const offset = new THREE.Vector3(0, distance * 0.2, distance);
      const cameraPosition = targetPosition.clone().add(offset);
      
      this.controls.target.copy(targetPosition);
      this.camera.position.copy(cameraPosition);
      this.controls.update();
      this.controls.enabled = true;
    }
  }

  stopFollowing(): void {
    this.followedObject = null;

    if (this.controls){
      this.controls.enabled = true;
    }
    
    const currentTarget = new THREE.Vector3();
    this.camera.getWorldDirection(currentTarget);
    currentTarget.multiplyScalar(500).add(this.camera.position);
    
    this.controls.target.copy(currentTarget);
    this.controls.update();
  }

  private startFollowing(): void {
    if (!this.followedObject) return;
    
    const objectId = this.followedObject.id;
    const type = this.followedObject.type;

    const targetObject = this.clickableObjects.find(mesh => 
      mesh.userData['type'] === type && mesh.userData['data'][`${type}_id`] === objectId
    );

    if (targetObject) {
      const targetPosition = targetObject.position.clone();
      const distance = this.calculateViewDistance(targetObject);
      const offset = new THREE.Vector3(0, distance * 0.3, distance);
      const cameraTargetPosition = targetPosition.clone().add(offset);

      this.animateCameraToFollow(cameraTargetPosition, targetPosition);
    }
  }

  private animateCameraToFollow(targetPosition: THREE.Vector3, lookAtPosition: THREE.Vector3): void {
    const startPosition = this.camera.position.clone();
    const duration = 1500;
    const startTime = Date.now();

    const animateToFollow = () => {
      const elapsed = Date.now() - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      const easeProgress = 1 - Math.pow(1 - progress, 3);
      
      this.camera.position.lerpVectors(startPosition, targetPosition, easeProgress);
      this.camera.lookAt(lookAtPosition);
      
      if (progress < 1) {
        requestAnimationFrame(animateToFollow);
      } else {
        this.controls.enabled = false;
      }
    };
    
    animateToFollow();
  }

  private updateFollowCamera(): void {
    if (!this.followedObject) return;

    const objectId = this.followedObject.id;
    const type = this.followedObject.type;
    
    const targetObject = this.clickableObjects.find(mesh => 
      mesh.userData['type'] === type && mesh.userData['data'][`${type}_id`] === objectId
    );

    if (targetObject) {
      const targetPosition = targetObject.position.clone();
      const distance = this.calculateViewDistance(targetObject);
      
      const time = Date.now() * 0.0003;
      const offset = new THREE.Vector3(
        Math.sin(time) * distance * 0.8,
        distance * 0.4,
        Math.cos(time) * distance * 0.8
      );
      
      const desiredPosition = targetPosition.clone().add(offset);
      
      this.camera.position.lerp(desiredPosition, 0.02);
      this.camera.lookAt(targetPosition);
    }
  }

  private calculateViewDistance(object: THREE.Mesh): number {
    const geometry = object.geometry as THREE.SphereGeometry;
    const radius = geometry.parameters?.radius || 50;
    
    let baseMultiplier;
    switch (object.userData['type']) {
      case 'solar_system':
        baseMultiplier = 10;
        break;
      case 'planet':
        baseMultiplier = 8;
        break;
      case 'moon':
        baseMultiplier = 5;
        break;
      default:
        baseMultiplier = 20;
    }
    
    return Math.max(radius * baseMultiplier, 100);
  }
  // #endregion

  // #region Solar System Creation
  private createSolarSystem(): void {
    const { starScale, planetScale, moonScale } = this.calculateSizeScales();
    const { planetDistanceScale, moonDistanceScale } = this.calculateDistanceScales();
    this.moonDistanceScale = moonDistanceScale;

    // Apply user scaling from renderOptions
    const finalPlanetScale = planetScale * (this.renderOptions.scale?.planets || 1);
    const finalMoonScale = moonScale * (this.renderOptions.scale?.moons || 1);
    const finalStarScale = starScale * (this.renderOptions.scale?.star || 1);
    const finalPlanetOrbitScale = planetDistanceScale * (this.renderOptions.scale?.orbits || 1);
    const finalMoonOrbitScale = moonDistanceScale * (this.renderOptions.scale?.orbits || 1);

    this.createStar(finalStarScale);
    this.solarSystem.planets.forEach(planet => 
      this.createPlanet(planet, finalPlanetScale, finalPlanetOrbitScale, finalMoonScale, finalMoonOrbitScale)
    );
    this.addSkybox();
  }

  private createStar(starScale: number): void {
    const starSize = this.clamp(this.solarSystem.solar_system_diameter * starScale, 10, 300);
    const star = new THREE.Mesh(
      new THREE.SphereGeometry(starSize, 64, 64),
      new THREE.MeshBasicMaterial({ color: 0xffffaa })
    );
    star.userData = { type: 'solar_system', data: this.solarSystem };
    this.scene.add(star);
    this.clickableObjects.push(star);
  }

  private createPlanet(planet: any, planetScale: number, planetDistanceScale: number, moonScale: number, moonDistanceScale: number): void {
    const size = this.clamp((planet.planet_diameter || 12000) * planetScale, 5, 100);
    const apogeeDistance = (planet.planet_apogee || 0) * planetDistanceScale;
    const perigeeDistance = (planet.planet_perigee || 0) * planetDistanceScale;
    
    if (this.renderOptions.visibility.orbits) {
      this.createEllipse(planet, apogeeDistance, 0, 0, -perigeeDistance, 0, 0, 0x5555ff);
    }
    
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

  private getPlanetMaterial(type: PlanetType): THREE.Material {
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

  private clearScene(): void {
    this.scene.children
      .filter(child => !(child instanceof THREE.Light))
      .forEach(obj => this.scene.remove(obj));
    this.clickableObjects = [];
  }
  // #endregion

  // #region Orbit Management
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

    ellipse.userData = { type: 'orbit' };

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
          
          if (this.renderOptions.visibility.orbits) {
            const moonOrbitGeometry = this.createEllipseGeometry(moon, apogeeDistance, 0, 0, -perigeeDistance, 0, 0);
            const moonOrbitMaterial = new THREE.LineBasicMaterial({ color: 0x888888, transparent: true, opacity: 0.2 });
            const moonOrbitLine = new THREE.LineLoop(moonOrbitGeometry, moonOrbitMaterial);
            moonOrbitLine.position.copy(planetMesh.position);
            this.scene.add(moonOrbitLine);
            this.moonEllipses.push(moonOrbitLine);
          }
        });
      }
    });
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
  // #endregion

  // #region Animation and Rendering
  private animate(): void {
    this.animationId = requestAnimationFrame(() => this.animate());

    const clampVal = 8000;
    this.camera.position.x = this.clamp(this.camera.position.x, -clampVal, clampVal);
    this.camera.position.y = this.clamp(this.camera.position.y, -clampVal, clampVal);
    this.camera.position.z = this.clamp(this.camera.position.z, -clampVal, clampVal);

    if (this.renderOptions.visibility.animateOrbits != false) {
      this.animateOrbits();
      this.updateMoonOrbits();
    }
    
    if (this.followedObject) {
      this.updateFollowCamera();
    } else {
      this.controls.update();
    }
    
    this.composer.render();
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

    this.throttleOrbitalUpdate();
  }

  private throttleOrbitalUpdate(): void {
    if (this.orbitalUpdateTimeout) {
      clearTimeout(this.orbitalUpdateTimeout);
    }

    this.orbitalUpdateTimeout = setTimeout(() => {
      this.updateOrbitalPositions();
    }, 100);
  }

  private updateOrbitalPositions(): void {
    const planetsPositions: { [key: string]: { x: number, y: number, z: number } } = {};
    const moonsPositions: { [key: string]: { x: number, y: number, z: number } } = {};

    this.clickableObjects.forEach(obj => {
      if (obj.userData['type'] === 'planet') {
        const planetId = obj.userData['data'].planet_id;
        planetsPositions[planetId] = {
          x: obj.position.x,
          y: obj.position.y,
          z: obj.position.z
        };
      } else if (obj.userData['type'] === 'moon') {
        const moonId = obj.userData['data'].moon_id;
        moonsPositions[moonId] = {
          x: obj.position.x,
          y: obj.position.y,
          z: obj.position.z
        };
      }
    });

    this.orbitalPositionsUpdate.emit({
      planets: planetsPositions,
      moons: moonsPositions
    });
  }

  private updateRenderSettings(): void {    
    this.applyVisibilitySettings();
    
    if (this.renderOptions.camera && this.controls) {
      this.applyCameraSettings();
    }

    // Check if scales changed
    const currentScales = this.calculateSizeScales();
    const scalesChanged = !this.lastScales || 
        this.lastScales.starScale !== currentScales.starScale ||
        this.lastScales.planetScale !== currentScales.planetScale ||
        this.lastScales.moonScale !== currentScales.moonScale;
        
    if (scalesChanged) {    
      const currentDistance = this.camera.position.distanceTo(this.controls.target);
      const currentDirection = this.camera.position.clone().normalize();
      
      // Safeguard: if camera is at origin or distance is 0, use default position
      if (currentDistance < 1 || this.camera.position.length() < 1) {
        this.camera.position.set(0, 500, 1500);
        this.controls.target.set(0, 0, 0);
      } else {
        this.clearScene();
        this.createSolarSystem();
        
        // Reposition camera
        const newDistance = Math.max(currentDistance, 500);
        this.camera.position.copy(currentDirection.multiplyScalar(newDistance));
      }
      
      this.controls.update();
      this.lastScales = { ...currentScales };
    }
    
    // Apply other settings
    this.applyBrightnessAndContrast();
  }

  private applyVisibilitySettings(): void {  
    let orbitCount = 0;
    this.scene.traverse((child) => {
      if ((child as any).userData?.type === 'orbit') {
        child.visible = this.renderOptions.visibility.orbits;
        orbitCount++;
      }
    });
  }

  private applyCameraSettings(): void {
    if (this.renderOptions.camera && this.controls) {
      this.camera.position.set(
        this.renderOptions.camera.position.x,
        this.renderOptions.camera.position.y,
        this.renderOptions.camera.position.z
      );
      this.controls.target.set(
        this.renderOptions.camera.target.x,
        this.renderOptions.camera.target.y,
        this.renderOptions.camera.target.z
      );
      this.camera.fov = this.renderOptions.camera.fov;
      this.camera.updateProjectionMatrix();
      this.controls.update();
    }
  }

  private applyBrightnessAndContrast(): void {
    // if (this.renderOptions.postProcessing) {
    //   // You need to implement post-processing effects here
    //   // For now, just log the values
    //   console.log('Brightness:', this.renderOptions.postProcessing.brightness);
    //   console.log('Contrast:', this.renderOptions.postProcessing.contrast);
    //   console.log('Saturation:', this.renderOptions.postProcessing.saturation);
    // }
  }

  // #endregion

  // #region Offscreen Rendering
  async generateThumbnail(solarSystemData: SolarSystem, width: number, height: number): Promise<string> {
    // Create offscreen renderer
    const offscreenRenderer = new THREE.WebGLRenderer({ 
      antialias: true, 
      preserveDrawingBuffer: true 
    });
    offscreenRenderer.setSize(width, height);
    offscreenRenderer.setPixelRatio(1);

    // Create offscreen scene and camera
    const offscreenScene = new THREE.Scene();
    const offscreenCamera = new THREE.PerspectiveCamera(75, width / height, 0.1, 20000);
    
    // Setup lights for offscreen scene
    offscreenScene.add(new THREE.AmbientLight(0xffffff, 0.6));
    const offscreenPointLight = new THREE.PointLight(0xfff1aa, 3, 3000);
    offscreenScene.add(offscreenPointLight);

    // Create temporary solar system in offscreen scene
    const tempSolarSystem = solarSystemData;
    const { starScale, planetScale, moonScale } = this.calculateSizeScalesForSystem(tempSolarSystem);
    const { planetDistanceScale, moonDistanceScale } = this.calculateDistanceScalesForSystem(tempSolarSystem);

    // Create star
    const starSize = this.clamp(tempSolarSystem.solar_system_diameter * starScale, 10, 300);
    const offscreenStar = new THREE.Mesh(
      new THREE.SphereGeometry(starSize, 32, 32),
      new THREE.MeshBasicMaterial({ color: 0xffffaa })
    );
    offscreenScene.add(offscreenStar);

    // Create planets
    tempSolarSystem.planets.forEach(planet => {
      const planetSize = this.clamp((planet.planet_diameter || 12000) * planetScale, 5, 100);
      const initialAngle = (planet.planet_orbital_longitude || 0) * (Math.PI / 180);
      const planetPos = this.getOrbitPositionForSystem(planet, planetDistanceScale, initialAngle);

      const offscreenPlanet = new THREE.Mesh(
        new THREE.SphereGeometry(planetSize, 16, 16),
        this.getPlanetMaterial(planet.planet_type as PlanetType)
      );
      offscreenPlanet.position.copy(planetPos);
      offscreenScene.add(offscreenPlanet);

      // Create moons
      planet.moons?.forEach(moon => {
        const moonSize = this.clamp((moon.moon_diameter || 3000) * moonScale, 2, 20);
        const moonInitialAngle = (moon.moon_orbital_longitude || 0) * (Math.PI / 180);
        const moonRelativePos = this.getOrbitPositionForSystem(moon, moonDistanceScale, moonInitialAngle);
        const moonPos = planetPos.clone().add(moonRelativePos);

        const offscreenMoon = new THREE.Mesh(
          new THREE.SphereGeometry(moonSize, 8, 8),
          new THREE.MeshStandardMaterial({ color: 0xbbbbbb, roughness: 1 })
        );
        offscreenMoon.position.copy(moonPos);
        offscreenScene.add(offscreenMoon);
      });
    });

    // Position camera for good view
    const boundingBox = new THREE.Box3().setFromObject(offscreenScene);
    const center = boundingBox.getCenter(new THREE.Vector3());
    const size = boundingBox.getSize(new THREE.Vector3());
    const maxDim = Math.max(size.x, size.y, size.z);
    
    offscreenCamera.position.set(
      center.x + maxDim * 0.8,
      center.y + maxDim * 0.6,
      center.z + maxDim * 1.2
    );
    offscreenCamera.lookAt(center);

    // Render to canvas
    offscreenRenderer.render(offscreenScene, offscreenCamera);

    // Extract image data
    const canvas = offscreenRenderer.domElement;
    const dataURL = canvas.toDataURL('image/png');

    // Cleanup
    offscreenRenderer.dispose();
    offscreenScene.clear();

    return dataURL;
  }

  private calculateSizeScalesForSystem(system: SolarSystem): { starScale: number, planetScale: number, moonScale: number } {
    const starSize = system.solar_system_diameter || 1000000;
    const planetSizes = system.planets.map(p => p.planet_diameter || 12000);
    const moonSizes = system.planets.flatMap(p => p.moons?.map(m => m.moon_diameter || 3000) || []);

    const maxPlanetSize = Math.max(...planetSizes, 1);
    const maxMoonSize = Math.max(...moonSizes, 1);

    return {
      starScale: 200 / starSize,
      planetScale: 80 / maxPlanetSize,
      moonScale: 15 / maxMoonSize
    };
  }

  private calculateDistanceScalesForSystem(system: SolarSystem): { planetDistanceScale: number, moonDistanceScale: number } {
    let maxPlanetDist = 0;
    let maxMoonDist = 0;

    system.planets.forEach(planet => {
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

  private getOrbitPositionForSystem(body: any, distanceScale: number, angle: number): THREE.Vector3 {
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
  // #endregion

  // #region Utility Methods
  private calculateSizeScales(): { starScale: number, planetScale: number, moonScale: number } {
    const starSize = this.solarSystem.solar_system_diameter || 1000000;
    const planetSizes = this.solarSystem.planets.map(p => p.planet_diameter || 10000);
    const moonSizes = this.solarSystem.planets.flatMap(p => p.moons?.map(m => m.moon_diameter || 3000) || []);

    const planetAvg = planetSizes.reduce((a, b) => a + b, 0) / planetSizes.length || 1;
    const moonAvg = moonSizes.reduce((a, b) => a + b, 0) / moonSizes.length || 1;

    // Base scales multiplied by slider values
    return {
      starScale: (150 / starSize) * this.renderOptions.scale.star,
      planetScale: (30 / planetAvg) * this.renderOptions.scale.planets,
      moonScale: (10 / moonAvg) * this.renderOptions.scale.moons
    };
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

  private clamp(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), max);
  }
  // #endregion
}

