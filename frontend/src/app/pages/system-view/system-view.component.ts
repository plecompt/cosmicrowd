import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { NotificationService } from '../../services/notifications/notification.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { PlanetType } from '../../interfaces/solar-system/planet.interface';

@Component({
  selector: 'app-system-view',
  imports: [],
  templateUrl: './system-view.component.html',
  styleUrl: './system-view.component.css'
})
export class SystemViewComponent implements OnInit {
  @ViewChild('container', { static: true }) containerRef!: ElementRef;
  scene!: THREE.Scene;
  camera!: THREE.PerspectiveCamera;
  renderer!: THREE.WebGLRenderer;
  composer!: EffectComposer;

  currentGalaxy: number = 1;
  solarSystemId!: number;
  solarSystem!: SolarSystem;

  constructor(
    private route: ActivatedRoute,
    private galaxiesService: GalaxiesService,
    private notificationService: NotificationService
  ) {}

  ngOnInit(): void {
    this.solarSystemId = this.route.snapshot.params['id'];
    this.getSolarSystem();
  }

  getSolarSystem() {
    this.galaxiesService.getSolarSystem(this.currentGalaxy, this.solarSystemId).subscribe({
      next: (data) => {
        this.solarSystem = data.solar_system;

        if (!this.solarSystem) {
          this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
          return;
        }

        this.init();
        this.animate();
        console.log(this.solarSystem);
      },
      error: (error) => {
        this.notificationService.showError(error.error?.message || 'Something went wrong, please try again later', 5000, '/home');
      }
    });
  }

private init(): void {
  const container = this.containerRef.nativeElement;

  // Scene and Camera
  this.scene = new THREE.Scene();
  this.scene.background = new THREE.Color(0x000010);
  this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 10, 10000);
  this.camera.position.set(0, 300, 1500);

  // Renderer
  this.renderer = new THREE.WebGLRenderer({ antialias: true });
  this.renderer.setSize(window.innerWidth, window.innerHeight);
  this.renderer.setPixelRatio(window.devicePixelRatio);
  this.renderer.outputColorSpace = THREE.SRGBColorSpace;
  this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
  this.renderer.toneMappingExposure = 1.5;
  container.appendChild(this.renderer.domElement);

  // Controls
  const controls = new OrbitControls(this.camera, this.renderer.domElement);
  controls.enableDamping = true;

  // Lights
  const ambient = new THREE.AmbientLight(0xffffff, 0.6);
  this.scene.add(ambient);

  const pointLight = new THREE.PointLight(0xfff1aa, 3, 3000);
  pointLight.position.set(0, 0, 0);
  this.scene.add(pointLight);

  // Postprocessing - Bloom
  const renderScene = new RenderPass(this.scene, this.camera);
  const bloomPass = new UnrealBloomPass(new THREE.Vector2(window.innerWidth, window.innerHeight), 1.5, 0.4, 0.85);
  bloomPass.threshold = 0;
  bloomPass.strength = 2.5;
  bloomPass.radius = 0.8;

  this.composer = new EffectComposer(this.renderer);
  this.composer.addPass(renderScene);
  this.composer.addPass(bloomPass);

  // Star (Sun)
  const starGeo = new THREE.SphereGeometry(30, 64, 64);
  const starMat = new THREE.MeshBasicMaterial({ color: 0xffffaa });
  const star = new THREE.Mesh(starGeo, starMat);
  this.scene.add(star);

  const scale = 1; // scaling

  // Planets and Moons
  this.solarSystem.planets.forEach((planet) => {
    const size = (planet.planet_diameter || 10000) / 2000;

    const planetX = (planet.planet_initial_x ?? 0) * scale;
    const planetY = (planet.planet_initial_y ?? 0) * scale;
    const planetZ = (planet.planet_initial_z ?? 0) * scale;


    const planetMat = this.getPlanetMaterial(planet.planet_type as PlanetType);
    const planetMesh = new THREE.Mesh(
      new THREE.SphereGeometry(size, 32, 32),
      planetMat
    );

    planetMesh.position.set(planetX, planetY, planetZ);
    this.scene.add(planetMesh);

    // Moons
    if (planet.moons && planet.moons.length > 0) {
      planet.moons.forEach((moon) => {
        const moonSize = (moon.moon_diameter || 3000) / 4000;

        const moonX = (moon.moon_initial_x ?? 0) * scale;
        const moonY = (moon.moon_initial_y ?? 0) * scale;
        const moonZ = (moon.moon_initial_z ?? 0) * scale;

        const moonMesh = new THREE.Mesh(
          new THREE.SphereGeometry(moonSize, 16, 16),
          new THREE.MeshStandardMaterial({ color: 0xbbbbbb, roughness: 1 })
        );
        moonMesh.position.set(planetX + moonX, planetY + moonY, planetZ + moonZ);
        this.scene.add(moonMesh);
      });
    }
  });

  // Starfield
  const stars = new THREE.BufferGeometry();
  const starCount = 20000;
  const starPos = new Float32Array(starCount * 3);
  for (let i = 0; i < starCount * 3; i++) {
    starPos[i] = (Math.random() - 0.5) * 10000;
  }
  stars.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
  const starMat2 = new THREE.PointsMaterial({ color: 0xffffff, size: 2, opacity: 0.6, transparent: true });
  const starField = new THREE.Points(stars, starMat2);
  this.scene.add(starField);
}

  private animate(): void {
    requestAnimationFrame(() => this.animate());
    this.composer.render();
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
