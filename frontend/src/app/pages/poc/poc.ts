import { Component, ElementRef, ViewChild, AfterViewInit } from '@angular/core';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass';

@Component({
  selector: 'app-stylized-system',
  standalone: true,
  template: `<div #container class="w-full h-screen"></div>`
})
export class StylizedSystemComponent implements AfterViewInit {
  @ViewChild('container', { static: true }) containerRef!: ElementRef;
  private scene!: THREE.Scene;
  private camera!: THREE.PerspectiveCamera;
  private renderer!: THREE.WebGLRenderer;
  private composer!: EffectComposer;

  ngAfterViewInit(): void {
    this.init();
    this.animate();
  }

  private init(): void {
    const container = this.containerRef.nativeElement;

    // Scene and Camera
    this.scene = new THREE.Scene();
    this.scene.background = new THREE.Color(0x000010);
    this.camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 5000);
    this.camera.position.set(0, 250, 800);

    // Renderer
    this.renderer = new THREE.WebGLRenderer({ antialias: true });
    this.renderer.setSize(window.innerWidth, window.innerHeight);
    this.renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(this.renderer.domElement);

    // Controls
    const controls = new OrbitControls(this.camera, this.renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;

    // Lights
    const ambient = new THREE.AmbientLight(0xffffff, 0.1);
    this.scene.add(ambient);

    const pointLight = new THREE.PointLight(0xfff1aa, 2, 3000);
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

    // Planets and moons
    const planetColors = [0xff8844, 0xffcc00, 0x3399ff, 0xee6666, 0xaa6633, 0xffaa77, 0x5577ff, 0x2255ff];
    const planetDistances = [80, 120, 170, 230, 300, 380, 460, 560];

    planetDistances.forEach((dist, i) => {
      const size = 5 + i;
      const angle = (i / 8) * Math.PI * 2;
      const planet = new THREE.Mesh(
        new THREE.SphereGeometry(size, 32, 32),
        new THREE.MeshStandardMaterial({ color: planetColors[i], roughness: 0.4, metalness: 0.5 })
      );
      planet.position.set(Math.cos(angle) * dist, 0, Math.sin(angle) * dist);
      this.scene.add(planet);

      for (let j = 0; j < 3; j++) {
        const moon = new THREE.Mesh(
          new THREE.SphereGeometry(1.5, 16, 16),
          new THREE.MeshStandardMaterial({ color: 0xbbbbbb, roughness: 1 })
        );
        const moonAngle = (j / 3) * Math.PI * 2;
        const moonDist = size + 5 + j * 3;
        moon.position.set(
          planet.position.x + Math.cos(moonAngle) * moonDist,
          0,
          planet.position.z + Math.sin(moonAngle) * moonDist
        );
        this.scene.add(moon);
      }
    });

    // Starfield
    const stars = new THREE.BufferGeometry();
    const starCount = 2000;
    const starPos = new Float32Array(starCount * 3);
    for (let i = 0; i < starCount * 3; i++) {
      starPos[i] = (Math.random() - 0.5) * 5000;
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
}
