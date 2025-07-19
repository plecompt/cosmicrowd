import { Component, ElementRef, Input, OnChanges, OnInit, SimpleChanges, ViewChild } from '@angular/core';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { PlanetType } from '../../interfaces/solar-system/planet.interface';

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

  ngOnInit(): void {
    if (this.solarSystem) {
      this.initThreeJS();
    }
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
    if (this.animationId) {
      cancelAnimationFrame(this.animationId);
    }
    if (this.renderer) {
      this.renderer.dispose();
    }
  }

  private onWindowResize(): void {
    const container = this.containerRef.nativeElement;
    const width = container.clientWidth;
    const height = container.clientHeight;

    this.camera.aspect = width / height;
    this.camera.updateProjectionMatrix();
    this.renderer.setSize(width, height);
    this.composer.setSize(width, height);
  }

  private initThreeJS(): void {
    const container = this.containerRef.nativeElement;
    const width = container.clientWidth;
    const height = container.clientHeight;

    // Scene
    this.scene = new THREE.Scene();
    this.scene.background = new THREE.Color(0x000010);

    // Camera
    this.camera = new THREE.PerspectiveCamera(75, width / height, 1, 15000);
    this.camera.position.set(0, 300, 1500);

    // Renderer
    this.renderer = new THREE.WebGLRenderer({ antialias: true });
    this.renderer.setSize(width, height);
    this.renderer.setPixelRatio(window.devicePixelRatio);
    this.renderer.outputColorSpace = THREE.SRGBColorSpace;
    this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
    this.renderer.toneMappingExposure = 1.5;
    container.appendChild(this.renderer.domElement);

    // Controls
    this.controls = new OrbitControls(this.camera, this.renderer.domElement);
    this.controls.enableDamping = true;

    // Lights
    const ambient = new THREE.AmbientLight(0xffffff, 0.6);
    this.scene.add(ambient);

    const pointLight = new THREE.PointLight(0xfff1aa, 3, 3000);
    pointLight.position.set(0, 0, 0);
    this.scene.add(pointLight);

    // Postprocessing
    this.setupPostProcessing();

    // Create solar system
    this.createSolarSystem();

    // Start animation
    this.animate();
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

  private clearScene(): void {
    // Remove all objects except lights
    const objectsToRemove = this.scene.children.filter(child => 
      !(child instanceof THREE.Light)
    );
    objectsToRemove.forEach(obj => this.scene.remove(obj));
  }

  private createSolarSystem(): void {
    // Scaling calculations
    const { starScale, planetScale, moonScale } = this.calculateSizeScales();
    const { planetDistanceScale, moonDistanceScale } = this.calculateDistanceScales();

    // Create star
    this.createStar(starScale);

    // Create planets and moons
    this.solarSystem.planets.forEach((planet) => {
      this.createPlanet(planet, planetScale, planetDistanceScale, moonScale, moonDistanceScale);
    });

    // Add skybox
    this.addSkybox();
  }

  private createStar(starScale: number): void {
    const starSize = this.clamp(this.solarSystem.solar_system_diameter * starScale, 10, 300);
    const starGeo = new THREE.SphereGeometry(starSize, 64, 64);
    const starMat = new THREE.MeshBasicMaterial({ color: 0xffffaa });
    const star = new THREE.Mesh(starGeo, starMat);
    this.scene.add(star);
  }

  private createPlanet(planet: any, planetScale: number, planetDistanceScale: number, moonScale: number, moonDistanceScale: number): void {
    const size = this.clamp((planet.planet_diameter || 10000) * planetScale, 5, 100);

    const planetX = (planet.planet_initial_x ?? 0) * planetDistanceScale;
    const planetY = (planet.planet_initial_y ?? 0) * planetDistanceScale;
    const planetZ = (planet.planet_initial_z ?? 0) * planetDistanceScale;

    const planetMat = this.getPlanetMaterial(planet.planet_type as PlanetType);
    const planetMesh = new THREE.Mesh(
      new THREE.SphereGeometry(size, 32, 32),
      planetMat
    );

    planetMesh.position.set(planetX, planetY, planetZ);
    this.scene.add(planetMesh);

    // Planet orbit
    this.createOrbit(planetX, planetY, planetZ, 0x5555ff, 0.3);

    // Create moons
    if (planet.moons && planet.moons.length > 0) {
      planet.moons.forEach((moon: any) => {
        this.createMoon(moon, planetX, planetY, planetZ, moonScale, moonDistanceScale);
      });
    }
  }

  private createMoon(moon: any, planetX: number, planetY: number, planetZ: number, moonScale: number, moonDistanceScale: number): void {
    const moonSize = this.clamp((moon.moon_diameter || 3000) * moonScale, 2, 20);

    const moonX = (moon.moon_initial_x ?? 0) * moonDistanceScale;
    const moonY = (moon.moon_initial_y ?? 0) * moonDistanceScale;
    const moonZ = (moon.moon_initial_z ?? 0) * moonDistanceScale;

    const moonMesh = new THREE.Mesh(
      new THREE.SphereGeometry(moonSize, 16, 16),
      new THREE.MeshStandardMaterial({ color: 0xbbbbbb, roughness: 1 })
    );
    moonMesh.position.set(planetX + moonX, planetY + moonY, planetZ + moonZ);
    this.scene.add(moonMesh);

    // Moon orbit
    const moonOrbitRadius = Math.sqrt(moonX * moonX + moonZ * moonZ);
    this.createMoonOrbit(planetX, planetY, planetZ, moonX, moonY, moonZ);
  }

private createOrbit(targetX: number, targetY: number, targetZ: number, color: number, opacity: number): void {
  const planetPos = new THREE.Vector3(targetX, targetY, targetZ);
  const center = new THREE.Vector3(0, 0, 0); // Soleil

  const direction = planetPos.clone().sub(center).normalize();
  const radius = planetPos.distanceTo(center);

  // Trouver un vecteur perpendiculaire (any non-parallel)
  let temp = new THREE.Vector3(0, 1, 0);
  if (Math.abs(direction.dot(temp)) > 0.99) temp = new THREE.Vector3(1, 0, 0);

  // Créer une base orthonormée dans le plan orbital
  const u = new THREE.Vector3().crossVectors(direction, temp).normalize(); // 1er vecteur du plan
  const v = new THREE.Vector3().crossVectors(direction, u).normalize();    // 2e vecteur du plan

  // Points du cercle
  const segments = 128;
  const points: THREE.Vector3[] = [];
  for (let i = 0; i <= segments; i++) {
    const theta = (i / segments) * Math.PI * 2;
    const point = new THREE.Vector3()
      .addScaledVector(u, Math.cos(theta) * radius)
      .addScaledVector(v, Math.sin(theta) * radius)
      .add(center); // on translate autour du centre
    points.push(point);
  }

  const geometry = new THREE.BufferGeometry().setFromPoints(points);
  const material = new THREE.LineBasicMaterial({ color, transparent: true, opacity });
  const orbit = new THREE.LineLoop(geometry, material);
  this.scene.add(orbit);
}

  private createMoonOrbit(planetX: number, planetY: number, planetZ: number, moonX: number, moonY: number, moonZ: number): void {
    const planetPos = new THREE.Vector3(planetX, planetY, planetZ);
    const moonRel = new THREE.Vector3(moonX, moonY, moonZ);
    const radius = moonRel.length();
    const center = planetPos.clone();
    const orbitNormal = moonRel.clone().normalize();

    const segments = 128;
    const points: THREE.Vector3[] = [];

    for (let i = 0; i <= segments; i++) {
      const theta = (i / segments) * Math.PI * 2;
      points.push(new THREE.Vector3(
        Math.cos(theta) * radius,
        Math.sin(theta) * radius,
        0
      ));
    }

    //apply rotation & translation
    const defaultNormal = new THREE.Vector3(0, 0, 1);
    const quaternion = new THREE.Quaternion().setFromUnitVectors(defaultNormal, orbitNormal);
    points.forEach(p => p.applyQuaternion(quaternion).add(center));

    const geometry = new THREE.BufferGeometry().setFromPoints(points);
    const material = new THREE.LineBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.2 });
    const orbit = new THREE.LineLoop(geometry, material);
    this.scene.add(orbit);
  }

  private addSkybox(): void {
    const loader = new THREE.CubeTextureLoader();
    const skybox = loader.load([
      'skybox/system/right.png',
      'skybox/system/left.png',
      'skybox/system/top.png',
      'skybox/system/bottom.png',
      'skybox/system/front.png',
      'skybox/system/back.png'
    ]);
    this.scene.background = skybox;
  }

  private animate(): void {
    this.animationId = requestAnimationFrame(() => this.animate());

    // Clamp camera position
    const clampVal = 8000;
    this.camera.position.x = this.clamp(this.camera.position.x, -clampVal, clampVal);
    this.camera.position.y = this.clamp(this.camera.position.y, -clampVal, clampVal);
    this.camera.position.z = this.clamp(this.camera.position.z, -clampVal, clampVal);

    this.composer.render();
  }

  private clamp(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), max);
  }

  private calculateSizeScales(): { starScale: number, planetScale: number, moonScale: number } {
    const starSize = this.solarSystem.solar_system_diameter || 1000000;
    const planetSizes: number[] = [];
    const moonSizes: number[] = [];

    this.solarSystem.planets.forEach(planet => {
      planetSizes.push(planet.planet_diameter || 10000);
      planet.moons?.forEach(moon => {
        moonSizes.push(moon.moon_diameter || 3000);
      });
    });

    const targetStarSize = 150;
    const targetPlanetAvgSize = 30;
    const targetMoonAvgSize = 10;

    const planetAvg = planetSizes.reduce((a, b) => a + b, 0) / planetSizes.length || 1;
    const moonAvg = moonSizes.reduce((a, b) => a + b, 0) / moonSizes.length || 1;

    return {
      starScale: targetStarSize / starSize,
      planetScale: targetPlanetAvgSize / planetAvg,
      moonScale: targetMoonAvgSize / moonAvg
    };
  }

  private calculateDistanceScales(): { planetDistanceScale: number, moonDistanceScale: number } {
    let maxPlanetDist = 0;
    let maxMoonDist = 0;

    this.solarSystem.planets.forEach(planet => {
      const x = planet.planet_initial_x ?? 0;
      const y = planet.planet_initial_y ?? 0;
      const z = planet.planet_initial_z ?? 0;

      const planetDist = Math.sqrt(x * x + y * y + z * z);
      maxPlanetDist = Math.max(maxPlanetDist, planetDist);

      planet.moons?.forEach(moon => {
        const mx = moon.moon_initial_x ?? 0;
        const my = moon.moon_initial_y ?? 0;
        const mz = moon.moon_initial_z ?? 0;
        const moonDist = Math.sqrt(mx * mx + my * my + mz * mz);
        maxMoonDist = Math.max(maxMoonDist, moonDist);
      });
    });

    return {
      planetDistanceScale: 5000 / maxPlanetDist,
      moonDistanceScale: 500 / maxMoonDist
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

    const texture = textureLoader.load(textureMap[type]);

    return new THREE.MeshStandardMaterial({
      map: texture,
      roughness: 0.8,
      metalness: 0.3,
    });
  }
}
