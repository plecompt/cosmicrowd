import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { AuthService } from '../../services/auth/auth.service';
import { NotificationService } from '../../services/notifications/notification.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { DatePipe, TitleCasePipe } from '@angular/common';
import { UserService } from '../../services/user/user-service';
import { User } from '../../interfaces/user/user.interface';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { forkJoin } from 'rxjs';
import { NavigationService } from '../../services/navigation/navigation.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { LikeableType, LikesService } from '../../services/likes/likes.service';

@Component({
  selector: 'app-profile',
  imports: [DatePipe, TitleCasePipe, BackgroundStarsComponent],
  templateUrl: './profile.component.html',
  styleUrl: './profile.component.css'
})
export class ProfileComponent implements OnInit {
  currentGalaxy: number = 1;
  user!: User;
  canLike: boolean = false;
  profileUserId!: number;
  solarSystems!: SolarSystem[];
  topLikedSystem: any = null;
  topLikedPlanet: any = null;
  topLikedMoon: any = null;
  topLikedWallpaper: any = null;

  LikeableType = LikeableType;

  constructor(
    public authService: AuthService,
    private userService: UserService,
    private notificationService: NotificationService,
    private galaxiesService: GalaxiesService,
    private route: ActivatedRoute,
    private navigationService: NavigationService,
    public likesService: LikesService,
  ) { }

  ngOnInit(): void {
    // If user is not logged in
    if (!this.authService.isLoggedIn()) {
        this.notificationService.showError('You can\'t access this page', 3000, '/home');
        return;
    }

    this.route.params.subscribe(params => {
      this.profileUserId = params['id'];
      this.loadUserData();
    });
  }

  loadUserData(): void {
    forkJoin({
      user: this.userService.getUserById(this.profileUserId),
      solarSystems: this.galaxiesService.getSolarSystemsForUser(this.profileUserId, this.currentGalaxy),
    }).subscribe({
      next: (responses: any) => {
        this.user = responses.user.data.user;
        this.solarSystems = responses.solarSystems.data.solar_systems;
        
        this.calculateTopLiked();
        this.isViewerOwner();
      },
      error: () => {
        this.notificationService.showError('Failed to load user data', 5000, '/home');
      }
    });
  }

  getTotalMoons(solarSystem: any): number {
    return solarSystem.planets.reduce((total: number, planet: any) => {
      return total + (planet.moons ? planet.moons.length : 0);
    }, 0);
  }

  private calculateTopLiked(): void {
    // Find top liked system
    this.topLikedSystem = this.solarSystems.length > 0 
      ? this.solarSystems.reduce((max, system) => 
          ((system.likes_count || 0) > (max.likes_count || 0)) ? system : max) 
      : null;

    // Find top liked planet
    let allPlanets: any[] = [];
    this.solarSystems.forEach(system => {
      allPlanets = [...allPlanets, ...system.planets];
    });

    this.topLikedPlanet = allPlanets.length > 0 
      ? allPlanets.reduce((max, planet) => 
          ((planet.likes_count || 0) > (max.likes_count || 0)) ? planet : max) 
      : null;

    // Find top liked moon
    let allMoons: any[] = [];
    allPlanets.forEach(planet => {
      if (planet.moons) {
        allMoons = [...allMoons, ...planet.moons];
      }
    });

    this.topLikedMoon = allMoons.length > 0 
      ? allMoons.reduce((max, moon) => 
          ((moon.likes_count || 0) > (max.likes_count || 0)) ? moon : max) 
      : null;

    // Find top liked wallpaper
    const allWallpapers = this.solarSystems
      .filter(system => system.wallpaper)
      .map(system => system.wallpaper);
      
    this.topLikedWallpaper = allWallpapers.length > 0 
      ? allWallpapers.reduce((max, wallpaper) => 
          ((wallpaper?.likes_count || 0) > (max?.likes_count || 0)) ? wallpaper : max) 
      : null;
  }

  toggleLike(type: LikeableType, object: any): void {
    this.likesService.like(type, this.currentGalaxy, object.solar_system_id, object.planet_id, object.moon_id, object.wallpaper_id)
      .subscribe(() => {
        object.is_liked = !object.is_liked;
        object.likes_count += object.is_liked ? 1 : -1;
      });
  }

  getSystemNameByWallpaperId(wallpaperId: number): string | null {
    const system = this.solarSystems.find(system => system.wallpaper?.wallpaper_id === wallpaperId);
    return system ? system.solar_system_name : null;
  }

  isViewerOwner() {
    this.canLike = (localStorage.getItem('user_id') != this.solarSystems[0].user_id) && this.authService.isLoggedIn();
  }

  viewSystem(solarSystemId: number): void {
    this.navigationService.navigateTo(`/view-system/${solarSystemId}`);
  }

  viewWallpaper(solarSystemId: number): void {
    // redirect to vue wallpaper view or something
  }
}