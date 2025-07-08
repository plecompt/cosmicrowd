import {
  Component,
  ElementRef,
  ViewChild,
  OnInit,
  AfterViewInit,
} from '@angular/core';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass';

@Component({
  selector: 'app-system-wallpaper-poc',
  standalone: true,
  template: `<div #threeContainer class="three-container"></div>`,
  styles: [
    `
      .three-container {
        width: 100%;
        height: 100vh;
        display: block;
        overflow: hidden;
      }
    `,
  ],
})
export class SystemWallpaperPocComponent implements OnInit, AfterViewInit {
  @ViewChild('threeContainer', { static: true }) threeContainer!: ElementRef;

  private scene!: THREE.Scene;
  private camera!: THREE.PerspectiveCamera;
  private renderer!: THREE.WebGLRenderer;
  private composer!: EffectComposer;
  private starTextures = {
    sun: 'https://raw.githubusercontent.com/JulienDurillon/assets/master/space/sun.jpg',
    planets: [
      'https://raw.githubusercontent.com/JulienDurillon/assets/master/space/mercury.jpg',
      'https://raw.githubusercontent.com/JulienDurillon/assets/master/space/venus.jpg',
      'https://raw.githubusercontent.com/JulienDurillon/assets/master/space/earth.jpg',
      'https://raw.githubusercontent.com/JulienDurillon/assets/master/space/mars.jpg',
      'https://raw.githubusercontent.com/JulienDurillon/assets/master/space/jupiter.jpg',
      'https://raw.githubusercontent.com/JulienDurillon/assets/master/space/saturn.jpg',
      'https://raw.githubusercontent.com/JulienDurillon/assets/master/space/uranus.jpg',
      'https://raw.githubusercontent.com/JulienDurillon/assets/master/space/neptune.jpg',
    ],
    moon: 'https://raw.githubusercontent.com/JulienDurillon/assets/master/space/moon.jpg',
  };

  ngOnInit(): void {
    this.initScene();
    this.setupCamera();
    this.setupRenderer();
    this.setupLighting();
    this.setupPostprocessing();
    this.loadSystem();
    this.animate();
  }

  ngAfterViewInit(): void {
    window.addEventListener('resize', () => this.onWindowResize());
  }

  private initScene(): void {
    this.scene = new THREE.Scene();
    this.scene.background = new THREE.Color(0x000010);
  }

  private setupCamera(): void {
    const aspect = window.innerWidth / window.innerHeight;
    this.camera = new THREE.PerspectiveCamera(50, aspect, 0.1, 5000);
    this.camera.position.set(0, 400, 1000);
  }

  private setupRenderer(): void {
    this.renderer = new THREE.WebGLRenderer({ antialias: true, preserveDrawingBuffer: true });
    this.renderer.setPixelRatio(window.devicePixelRatio);
    this.renderer.setSize(window.innerWidth, window.innerHeight);
    this.threeContainer.nativeElement.appendChild(this.renderer.domElement);
  }

  private setupLighting(): void {
    const ambient = new THREE.AmbientLight(0xffffff, 0.15);
    this.scene.add(ambient);

    const light = new THREE.PointLight(0xfff7aa, 3, 3000);
    light.position.set(0, 0, 0);
    this.scene.add(light);
  }

  private setupPostprocessing(): void {
    this.composer = new EffectComposer(this.renderer);
    this.composer.addPass(new RenderPass(this.scene, this.camera));

    const bloomPass = new UnrealBloomPass(
      new THREE.Vector2(window.innerWidth, window.innerHeight),
      1.5,
      0.4,
      0.85
    );
    bloomPass.threshold = 0.2;
    bloomPass.strength = 2.5;
    bloomPass.radius = 0.8;
    this.composer.addPass(bloomPass);
  }

  private loadSystem(): void {
    const textureLoader = new THREE.TextureLoader();

    // Star (Sun)
    const sunTex = textureLoader.load(this.starTextures.sun);
    const sun = new THREE.Mesh(
      new THREE.SphereGeometry(40, 64, 64),
      new THREE.MeshBasicMaterial({ map: sunTex })
    );
    this.scene.add(sun);

    // Planets
    const distances = [100, 150, 200, 250, 330, 400, 470, 540];
    const sizes = [6, 10, 10, 8, 20, 18, 16, 16];

    for (let i = 0; i < 8; i++) {
      const tex = textureLoader.load(this.starTextures.planets[i]);
      const planet = new THREE.Mesh(
        new THREE.SphereGeometry(sizes[i], 32, 32),
        new THREE.MeshStandardMaterial({ map: tex, roughness: 1 })
      );
      const angle = (i / 8) * Math.PI * 2;
      const x = Math.cos(angle) * distances[i];
      const z = Math.sin(angle) * distances[i];
      planet.position.set(x, 0, z);
      this.scene.add(planet);

      // Moons
      for (let j = 0; j < 3; j++) {
        const moonTex = textureLoader.load(this.starTextures.moon);
        const moon = new THREE.Mesh(
          new THREE.SphereGeometry(2, 16, 16),
          new THREE.MeshStandardMaterial({ map: moonTex })
        );
        const moonAngle = (j / 3) * Math.PI * 2;
        const moonDist = sizes[i] + 6 + j * 4;
        moon.position.set(
          x + Math.cos(moonAngle) * moonDist,
          0,
          z + Math.sin(moonAngle) * moonDist
        );
        this.scene.add(moon);
      }
    }

    // Stars background
    this.createStarField();
  }

  private createStarField(): void {
    const stars = new THREE.BufferGeometry();
    const starCount = 3000;
    const positions = new Float32Array(starCount * 3);

    for (let i = 0; i < starCount; i++) {
      positions[i * 3] = (Math.random() - 0.5) * 5000;
      positions[i * 3 + 1] = (Math.random() - 0.5) * 5000;
      positions[i * 3 + 2] = (Math.random() - 0.5) * 5000;
    }

    stars.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const material = new THREE.PointsMaterial({ color: 0xffffff, size: 2, opacity: 0.9 });
    const starField = new THREE.Points(stars, material);
    this.scene.add(starField);
  }

  private animate(): void {
    requestAnimationFrame(() => this.animate());
    this.composer.render();
  }

  private onWindowResize(): void {
    this.camera.aspect = window.innerWidth / window.innerHeight;
    this.camera.updateProjectionMatrix();
    this.renderer.setSize(window.innerWidth, window.innerHeight);
    this.composer.setSize(window.innerWidth, window.innerHeight);
  }
}