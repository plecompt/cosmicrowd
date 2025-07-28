import { Injectable } from '@angular/core';
import * as THREE from 'three';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer.js';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass.js';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass.js';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { PlanetType } from '../../interfaces/solar-system/planet.interface';
import { GalaxiesService } from '../galaxies/galaxies.service';
import { WallpaperService } from '../wallpaper/wallpaper-service';

@Injectable({
  providedIn: 'root'
})
export class ThumbnailService {
  constructor(private galaxiesService: GalaxiesService, private wallpaperService: WallpaperService) { }

  async generateThumbnail(
    galaxyId: number,
    solarSystemId: number,
    width: number = 300, 
    height: number = 200
  ): Promise<string> {
    const solarSystemResponse = await this.galaxiesService.getSolarSystem(galaxyId, solarSystemId).toPromise();
    const wallpaperResponse = await this.wallpaperService.getWallpaper(galaxyId, solarSystemId).toPromise();

    const solarSystemData = solarSystemResponse?.data?.solar_system;
    const wallpaper = wallpaperResponse?.data;

    let wallpaperSettings;
    try {
      wallpaperSettings = JSON.parse(wallpaper.wallpaper_settings);
    } catch (parseError) {
      wallpaperSettings = {
        camera: { position: { x: 0, y: 300, z: 1500 }, target: { x: 0, y: 0, z: 0 }, fov: 75 },
        visibility: { orbits: true, labels: false, background: true, animateOrbits: true },
        effects: { brightness: 1, contrast: 1, saturation: 1 },
        scale: { star: 1, planets: 1, moons: 1, orbits: 1 },
        orbitalPositions: { planets: {}, moons: {} }
      };
    }

    return await this.renderThumbnail(solarSystemData, wallpaperSettings, width, height);
  }

  private async renderThumbnail(
    solarSystemData: SolarSystem,
    wallpaperSettings: any,
    width: number, 
    height: number
  ): Promise<string> {
    const offscreenRenderer = new THREE.WebGLRenderer({ 
      antialias: true, 
      preserveDrawingBuffer: true,
      alpha: true
    });
    offscreenRenderer.setSize(width, height);
    offscreenRenderer.setPixelRatio(1);
    offscreenRenderer.outputColorSpace = THREE.SRGBColorSpace;
    offscreenRenderer.toneMapping = THREE.ACESFilmicToneMapping;
    offscreenRenderer.toneMappingExposure = 1.5;
    offscreenRenderer.setClearColor(0x000000, 0);

    const offscreenScene = new THREE.Scene();

    if (wallpaperSettings.visibility?.background !== false) {
      const skyboxTexture = await this.loadSkyboxTexture();
      if (skyboxTexture) {
        offscreenScene.background = skyboxTexture;
      } else {
        offscreenScene.background = new THREE.Color(0x000011);
      }
    } else {
      offscreenScene.background = new THREE.Color(0x000033);
    }

    const cameraSettings = wallpaperSettings.camera || {};
    const offscreenCamera = new THREE.PerspectiveCamera(
      cameraSettings.fov || 75, 
      width / height, 
      1, 
      15000
    );

    if (cameraSettings.position) {
      offscreenCamera.position.set(
        cameraSettings.position.x || 0,
        cameraSettings.position.y || 300,
        cameraSettings.position.z || 1500
      );
    }

    if (cameraSettings.target) {
      offscreenCamera.lookAt(
        cameraSettings.target.x || 0,
        cameraSettings.target.y || 0,
        cameraSettings.target.z || 0
      );
    }

    const composer = this.setupPostProcessing(offscreenRenderer, offscreenScene, offscreenCamera, width, height);

    offscreenScene.add(new THREE.AmbientLight(0xffffff, 0.8));
    const pointLight = new THREE.PointLight(0xfff1aa, 3, 3000);
    pointLight.position.set(0, 0, 0);
    offscreenScene.add(pointLight);
    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.5);
    directionalLight.position.set(1000, 1000, 1000);
    directionalLight.lookAt(0, 0, 0);
    offscreenScene.add(directionalLight);

    const baseScales = this.calculateSizeScales(solarSystemData);
    const { planetDistanceScale, moonDistanceScale } = this.calculateDistanceScales(solarSystemData);

    const finalPlanetScale = baseScales.planetScale * (wallpaperSettings.scale?.planets || 1);
    const finalMoonScale = baseScales.moonScale * (wallpaperSettings.scale?.moons || 1);
    const finalStarScale = baseScales.starScale * (wallpaperSettings.scale?.star || 1);
    const finalPlanetOrbitScale = planetDistanceScale * (wallpaperSettings.scale?.orbits || 1);
    const finalMoonOrbitScale = moonDistanceScale * (wallpaperSettings.scale?.orbits || 1);

    this.createStar(offscreenScene, solarSystemData, finalStarScale);

    for (let index = 0; index < solarSystemData.planets.length; index++) {
      const planet = solarSystemData.planets[index];
      await this.createPlanet(
        offscreenScene, 
        planet, 
        finalPlanetScale, 
        finalPlanetOrbitScale, 
        finalMoonScale, 
        finalMoonOrbitScale,
        wallpaperSettings
      );
    }

    this.updatePostProcessing(composer, wallpaperSettings);
    offscreenRenderer.render(offscreenScene, offscreenCamera);

    const canvas = offscreenRenderer.domElement;
    const dataURL = canvas.toDataURL('image/png');

    offscreenRenderer.dispose();
    offscreenScene.clear();

    return dataURL;
  }

  private setupPostProcessing(
    renderer: THREE.WebGLRenderer, 
    scene: THREE.Scene, 
    camera: THREE.PerspectiveCamera, 
    width: number, 
    height: number
  ): EffectComposer {
    const renderScene = new RenderPass(scene, camera);
    const bloomPass = new UnrealBloomPass(
      new THREE.Vector2(width, height),
      1.5, 0.4, 0.85
    );
    bloomPass.threshold = 0;
    bloomPass.strength = 2.5;
    bloomPass.radius = 0.8;

    const composer = new EffectComposer(renderer);
    composer.addPass(renderScene);
    composer.addPass(bloomPass);
    return composer;
  }

  private updatePostProcessing(composer: EffectComposer, wallpaperSettings: any): void {
    const bloomPass = composer.passes.find(pass => pass instanceof UnrealBloomPass) as UnrealBloomPass;
    if (bloomPass && wallpaperSettings.effects) {
      bloomPass.strength = wallpaperSettings.effects.brightness;
      bloomPass.radius = wallpaperSettings.effects.contrast;
      bloomPass.threshold = Math.max(0, 1 - wallpaperSettings.effects.saturation);
    }
  }

  private createStar(scene: THREE.Scene, solarSystemData: SolarSystem, starScale: number): void {
    const starSize = this.clamp(solarSystemData.solar_system_diameter * starScale, 10, 300);
    const star = new THREE.Mesh(
      new THREE.SphereGeometry(starSize, 64, 64),
      new THREE.MeshStandardMaterial({ 
        color: 0xffffaa,
        emissive: 0xffffaa,
        emissiveIntensity: 0.5,
        roughness: 0.1,
        metalness: 0.0
      })
    );
    star.userData = { type: 'solar_system', data: solarSystemData };
    scene.add(star);
  }

  private async createPlanet(
    scene: THREE.Scene,
    planet: any, 
    planetScale: number, 
    planetDistanceScale: number, 
    moonScale: number, 
    moonDistanceScale: number,
    wallpaperSettings: any
  ): Promise<void> {
    const size = this.clamp((planet.planet_diameter || 10000) * planetScale, 5, 100);
    const apogeeDistance = (planet.planet_apogee || 0) * planetDistanceScale;
    const perigeeDistance = (planet.planet_perigee || 0) * planetDistanceScale;

    if (wallpaperSettings.visibility?.orbits) {
      this.createEllipse(scene, planet, apogeeDistance, 0, 0, -perigeeDistance, 0, 0, 0x5555ff);
    }

    let planetPos: THREE.Vector3;
    const planetId = planet.planet_id.toString();
    if (wallpaperSettings.orbitalPositions?.planets?.[planetId]) {
      const savedPos = wallpaperSettings.orbitalPositions.planets[planetId];
      planetPos = new THREE.Vector3(savedPos.x, savedPos.y, savedPos.z);
    } else {
      const initialAngle = (planet.planet_orbital_longitude || 0) * (Math.PI / 180);
      const currentAngle = this.calculateCurrentOrbitalAngle(planet, initialAngle);
      planetPos = this.getOrbitPosition(planet, planetDistanceScale, currentAngle);
    }

    const material = await this.getPlanetMaterial(planet.planet_type as PlanetType);
    const planetMesh = new THREE.Mesh(
      new THREE.SphereGeometry(size, 32, 32),
      material
    );
    planetMesh.position.copy(planetPos);
    planetMesh.userData = { 
      type: 'planet', 
      data: planet,
      distanceScale: planetDistanceScale
    };
    scene.add(planetMesh);

    planet.moons?.forEach((moon: any) => {
      this.createMoon(scene, moon, planetPos, moonScale, moonDistanceScale, wallpaperSettings);
    });
  }

  private createMoon(
    scene: THREE.Scene,
    moon: any, 
    planetPos: THREE.Vector3, 
    moonScale: number, 
    moonDistanceScale: number,
    wallpaperSettings: any
  ): void {
    const moonSize = this.clamp((moon.moon_diameter || 3000) * moonScale, 2, 20);

    let moonPos: THREE.Vector3;
    const moonId = moon.moon_id.toString();
    if (wallpaperSettings.orbitalPositions?.moons?.[moonId]) {
      const savedPos = wallpaperSettings.orbitalPositions.moons[moonId];
      moonPos = new THREE.Vector3(savedPos.x, savedPos.y, savedPos.z);
    } else {
      const initialAngle = (moon.moon_orbital_longitude || 0) * (Math.PI / 180);
      const currentAngle = this.calculateCurrentOrbitalAngle(moon, initialAngle);
      const moonRelativePos = this.getOrbitPosition(moon, moonDistanceScale, currentAngle);
      moonPos = planetPos.clone().add(moonRelativePos);
    }

    if (wallpaperSettings.visibility?.orbits) {
      const apogeeDistance = (moon.moon_apogee || 0) * moonDistanceScale;
      const perigeeDistance = (moon.moon_perigee || 0) * moonDistanceScale;
      if (apogeeDistance > 0 || perigeeDistance > 0) {
        this.createEllipse(
          scene, 
          moon,
          planetPos.x + apogeeDistance, planetPos.y, planetPos.z,
          planetPos.x - perigeeDistance, planetPos.y, planetPos.z,
          0x888888
        );
      }
    }

    const moonMesh = new THREE.Mesh(
      new THREE.SphereGeometry(moonSize, 16, 16),
      new THREE.MeshBasicMaterial({ color: 0xbbbbbb })
    );
    moonMesh.position.copy(moonPos);
    moonMesh.userData = { 
      type: 'moon', 
      data: moon,
      distanceScale: moonDistanceScale,
      planetPos: planetPos
    };
    scene.add(moonMesh);
  }

  private calculateCurrentOrbitalAngle(body: any, initialAngle: number): number {
    const orbitalPeriod = body.planet_orbital_period || body.moon_orbital_period || 365;
    const speed = 360 / orbitalPeriod;
    const timeProgress = 0.5;
    const currentLongitude = (body.planet_orbital_longitude || body.moon_orbital_longitude || 0) + speed * timeProgress;
    return (currentLongitude * Math.PI) / 180;
  }

  private createEllipse(
    scene: THREE.Scene,
    body: any, 
    x1: number, y1: number, z1: number, 
    x2: number, y2: number, z2: number, 
    color: number = 0x555555
  ): void {
    const geometry = this.createEllipseGeometry(body, x1, y1, z1, x2, y2, z2);
    const material = new THREE.LineBasicMaterial({ color, transparent: true, opacity: 0.3 });
    const ellipse = new THREE.LineLoop(geometry, material);
    scene.add(ellipse);
  }

  private createEllipseGeometry(
    body: any, 
    x1: number, y1: number, z1: number, 
    x2: number, y2: number, z2: number
  ): THREE.BufferGeometry {
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

  private calculateSizeScales(solarSystem: SolarSystem): { 
    starScale: number, 
    planetScale: number, 
    moonScale: number 
  } {
    const starSize = solarSystem.solar_system_diameter || 1000000;
    const planetSizes = solarSystem.planets.map(p => p.planet_diameter || 10000);
    const moonSizes = solarSystem.planets.flatMap(p => p.moons?.map(m => m.moon_diameter || 3000) || []);
    const planetAvg = planetSizes.reduce((a, b) => a + b, 0) / planetSizes.length || 1;
    const moonAvg = moonSizes.reduce((a, b) => a + b, 0) / moonSizes.length || 1;

    return {
      starScale: 150 / starSize,
      planetScale: 30 / planetAvg,
      moonScale: 10 / moonAvg
    };
  }

  private calculateDistanceScales(solarSystem: SolarSystem): { 
    planetDistanceScale: number, 
    moonDistanceScale: number 
  } {
    let maxPlanetDist = 0;
    let maxMoonDist = 0;
    solarSystem.planets.forEach(planet => {
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

  private async getPlanetMaterial(type: PlanetType): Promise<THREE.Material> {
    const texture = await this.loadPlanetTexture(type);
    if (texture) {
      return new THREE.MeshStandardMaterial({
        map: texture,
        roughness: 0.8,
        metalness: 0.3,
      });
    } else {
      const fallbackColors: { [key in PlanetType]: number } = {
        terrestrial: 0x8B4513,
        gas: 0xFFD700,
        ice: 0x87CEEB,
        super_earth: 0x228B22,
        sub_neptune: 0x4169E1,
        dwarf: 0x696969,
        lava: 0xFF4500,
        carbon: 0x2F4F4F,
        ocean: 0x0000CD,
      };
      const color = fallbackColors[type] || 0x808080;
      return new THREE.MeshBasicMaterial({ color: color });
    }
  }

  private async loadSkyboxTexture(): Promise<THREE.CubeTexture | null> {
    return new Promise((resolve) => {
      const loader = new THREE.CubeTextureLoader();
      const paths = [
        '/skybox/system/right.png', '/skybox/system/left.png',
        '/skybox/system/top.png', '/skybox/system/bottom.png',
        '/skybox/system/front.png', '/skybox/system/back.png'
      ];
      
      loader.load(
        paths,
        (texture) => resolve(texture),
        undefined,
        () => resolve(null)
      );
    });
  }

  private async loadPlanetTexture(type: PlanetType): Promise<THREE.Texture | null> {
    return new Promise((resolve) => {
      const loader = new THREE.TextureLoader();
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

      const texturePath = textureMap[type];
      loader.load(
        `/${texturePath}`,
        (texture) => resolve(texture),
        undefined,
        () => resolve(null)
      );
    });
  }

  private clamp(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), max);
  }
}