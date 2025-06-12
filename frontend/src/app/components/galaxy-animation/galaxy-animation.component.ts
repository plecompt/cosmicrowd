// src/app/components/galaxy-animation/galaxy-animation.component.ts
import { Component, ElementRef, ViewChild, AfterViewInit, OnDestroy, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import * as THREE from 'three';
import { GalaxiesService } from '../../services/galaxies-service/galaxies.service';
import { StarAnimation } from '../../interfaces/stars/star.interface';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';

@Component({
  selector: 'app-galaxy-animation',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './galaxy-animation.component.html',
  styleUrls: ['./galaxy-animation.component.css']
})
export class GalaxyAnimationComponent implements AfterViewInit, OnDestroy {
  @ViewChild('canvasContainer', { static: true }) canvasContainer!: ElementRef;
  @Output() starClick = new EventEmitter<StarAnimation>();
  @Output() userInteractionStart = new EventEmitter<void>();
  @Output() userInteractionEnd = new EventEmitter<void>();

  private scene!: THREE.Scene;
  private camera!: THREE.PerspectiveCamera;
  private renderer!: THREE.WebGLRenderer;
  private animationId!: number;
  private starMeshes: THREE.Mesh[] = [];
  private starsData: StarAnimation[] = [];

  private controls!: OrbitControls;
  private userIsInteracting = false;

  public fps = 0;
  public showFps = true;
  private lastTime = 0;
  private frameCount = 0;

  constructor(private galaxiesService: GalaxiesService) { }

  ngAfterViewInit(): void {
    this.initThreeJS();
    this.loadStarsData();
    this.setupControls();
    this.animate();
  }

  ngOnDestroy(): void {
    if (this.animationId) {
      cancelAnimationFrame(this.animationId);
    }

    if (this.controls) {
      this.controls.dispose();
    }

    if (this.renderer) {
      this.renderer.dispose();
    }

    // Nettoyage des event listeners
    window.removeEventListener('resize', () => this.onWindowResize());
  }

  private initThreeJS(): void {
    // Configuration de la scène
    this.scene = new THREE.Scene();
    this.scene.background = new THREE.Color(0x000010);

    // Configuration de la caméra
    this.camera = new THREE.PerspectiveCamera(
      75,
      window.innerWidth / window.innerHeight,
      0.1,
      10000
    );
    this.camera.position.set(0, 0, 200);

    // Configuration du renderer
    this.renderer = new THREE.WebGLRenderer({ antialias: true });
    this.renderer.setSize(window.innerWidth, window.innerHeight);
    this.canvasContainer.nativeElement.appendChild(this.renderer.domElement);

    // Gestion du redimensionnement
    window.addEventListener('resize', () => this.onWindowResize());
  }

  private setupControls(): void {
    this.controls = new OrbitControls(this.camera, this.renderer.domElement);

    // Configuration pour une galaxie
    this.controls.enableDamping = true; // Animation fluide
    this.controls.dampingFactor = 0.05;
    this.controls.screenSpacePanning = false;

    // Limites de zoom (éviter de sortir de la galaxie)
    this.controls.minDistance = 10;
    this.controls.maxDistance = 500;

    // Limites de rotation verticale
    this.controls.maxPolarAngle = Math.PI;
    this.controls.minPolarAngle = 0;

    // Vitesse de rotation
    this.controls.rotateSpeed = 0.5;
    this.controls.zoomSpeed = 1.0;

    // Point de focus au centre de la galaxie
    this.controls.target.set(0, 0, 0);

    // Listen controls to hide overlay
    this.setupControlsListeners();
  }

  private setupControlsListeners(): void {
    // Quand l'utilisateur commence à interagir
    this.controls.addEventListener('start', () => {
      this.userIsInteracting = true;
      this.userInteractionStart.emit();
    });

    // Quand l'utilisateur arrête d'interagir
    this.controls.addEventListener('end', () => {
      this.userIsInteracting = false;
      this.userInteractionEnd.emit();
    });
  }

  private loadStarsData(): void {
    this.galaxiesService.getStarsForAnimation(10000, 0).subscribe({
      next: (response) => {
        if (response.success) {
          this.starsData = response.stars;
          //here we may call createStarsWithLOD if galaxy is too big
          this.createStars();
        }
      },
      error: (error) => {
        console.error('Erreur lors du chargement des étoiles:', error);
        this.createDefaultStars();
      }
    });
  }

  private createStars(): void {
    this.starsData.forEach((starData, index) => {
      const geometry = new THREE.SphereGeometry(
        this.getStarSize(starData.star_diameter),
        16,
        16
      );

      const material = new THREE.MeshBasicMaterial({
        color: this.getStarColor(starData.star_surface_temp),
        transparent: true,
        opacity: 0.9
      });

      const star = new THREE.Mesh(geometry, material);

      // Position basée sur les données réelles ou générée
      star.position.set(
        starData.star_initial_x || (Math.random() - 0.5) * 100,
        starData.star_initial_y || (Math.random() - 0.5) * 100,
        starData.star_initial_z || (Math.random() - 0.5) * 100
      );

      // Stockage des données pour l'interaction
      (star as any).starData = starData;

      this.starMeshes.push(star);
      this.scene.add(star);
    });
    // Ajout d'un gestionnaire de clic
    this.setupClickHandler();
  }

  private createStarWithLOD(starData: any): THREE.Mesh {
    const geometry = new THREE.SphereGeometry(this.getStarSize(starData.star_diameter), 12, 8);
    const material = new THREE.MeshBasicMaterial({
      color: this.getStarColor(starData.surface_temperature)
    });

    const star = new THREE.Mesh(geometry, material);

    // LOD : simplifier les étoiles lointaines
    star.onBeforeRender = (renderer, scene, camera) => {
      const distance = camera.position.distanceTo(star.position);
      if (distance > 100) {
        // Géométrie simplifiée pour les étoiles lointaines
        star.geometry = new THREE.SphereGeometry(this.getStarSize(starData.star_diameter), 6, 4);
      }
    };

    return star;
  }

  private createDefaultStars(): void {
    // Création d'étoiles par défaut si le chargement échoue
    for (let i = 0; i < 100; i++) {
      const geometry = new THREE.SphereGeometry(0.5, 16, 16);
      const material = new THREE.MeshBasicMaterial({
        color: new THREE.Color().setHSL(Math.random(), 0.8, 0.8)
      });

      const star = new THREE.Mesh(geometry, material);
      star.position.set(
        (Math.random() - 0.5) * 100,
        (Math.random() - 0.5) * 100,
        (Math.random() - 0.5) * 100
      );

      this.starMeshes.push(star);
      this.scene.add(star);
    }
  }

  private getStarSize(diameter: number): number {
    // Conversion du diamètre réel en taille pour l'affichage
    return Math.max(0.2, Math.min(2, diameter / 100000));
  }

  private getStarColor(temperature: number): THREE.Color {
    // Couleur basée sur la température de surface
    if (temperature > 10000) return new THREE.Color(0x9bb0ff); // Bleu
    if (temperature > 7500) return new THREE.Color(0xaabfff);  // Blanc-bleu
    if (temperature > 6000) return new THREE.Color(0xccccff);  // Blanc
    if (temperature > 5200) return new THREE.Color(0xffcccc);  // Blanc-jaune
    if (temperature > 3700) return new THREE.Color(0xffcc99);  // Orange
    return new THREE.Color(0xff9999); // Rouge
  }

  private setupClickHandler(): void {
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();

    this.renderer.domElement.addEventListener('click', (event) => {
      mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
      mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

      raycaster.setFromCamera(mouse, this.camera);
      const intersects = raycaster.intersectObjects(this.starMeshes);

      if (intersects.length > 0) {
        const clickedStar = intersects[0].object as any;
        if (clickedStar.starData) {
          this.starClick.emit(clickedStar.starData);
        }
      }
    });
  }

  private animate(): void {
    this.animationId = requestAnimationFrame((time) => {
      // Calcul des FPS
      this.calculateFPS(time);

      // Updating controls
      if (this.controls) {
        this.controls.update();
      }

      // Rotation lente de la galaxie SEULEMENT si pas d'interaction
      if (!this.userIsInteracting) {
        this.scene.rotation.y += 0.001;
      }

      // Animation des étoiles
      this.starMeshes.forEach((star) => {
        star.rotation.x += 0.01;
        star.rotation.y += 0.01;
      });

      this.renderer.render(this.scene, this.camera);

      // Récursion pour la prochaine frame
      this.animate();
    });
  }

  private calculateFPS(time: number): void {
    // Protection contre les valeurs invalides
    if (!time || time <= 0) return;

    this.frameCount++;

    if (this.lastTime === 0) {
      this.lastTime = time;
      return;
    }

    if (time - this.lastTime >= 1000) { // Chaque seconde
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
  }
}
