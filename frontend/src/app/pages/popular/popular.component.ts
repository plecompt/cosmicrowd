import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth/auth.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { NotificationService } from '../../services/notifications/notification.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { Wallpaper } from '../../interfaces/wallpaper/wallpaper.interface';
import { LikeableType, LikesService } from '../../services/likes/likes.service';
import { NavigationService } from '../../services/navigation/navigation.service';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { DatePipe } from '@angular/common';
import { ThumbnailService } from '../../services/thumbnail/thumbnail-service';
import { User } from '../../interfaces/user/user.interface';

@Component({
  selector: 'app-popular',
  imports: [BackgroundStarsComponent, DatePipe],
  templateUrl: './popular.component.html',
  styleUrl: './popular.component.css'
})
export class PopularComponent implements OnInit {
  user!: User;
  mostLikedSolarSystems!: SolarSystem[];
  mostRecentSolarSystems!: SolarSystem[];
  mostLikedWallpapers!: Wallpaper[];
  mostRecentWallpapers!: Wallpaper[];
  LikeableType = LikeableType;
  currentGalaxy: number = 1; //atm there is only 1 galaxy, so hard coding id = 1, might evolve in the futur
  thumbnails: { [key: number]: string } = {};

  constructor(public authService: AuthService, private notificationService: NotificationService, public likesService: LikesService, public navigationService: NavigationService, private galaxiesService: GalaxiesService, private thumbnailService: ThumbnailService) { }

  ngOnInit(): void {
    // If user is logged in
    if (this.authService.isLoggedIn()) {
      this.getUser();
    }
    this.getMostLikedSolarSystems();
    this.getMostRecentSolarSystems();
    this.getMostLikedWallpapers();
    this.getMostRecentWallpapers();
  }

  //get current user
  getUser() {
    this.authService.me().subscribe({
      next: (response: any) => {
        this.user = response.data.user;
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later', 5000, '/systems');
      }
    })
  }

  getTotalMoons(solarSystem: SolarSystem): number {
    return solarSystem.planets.reduce((total: number, planet: any) => {
      return total + (planet.moons ? planet.moons.length : 0);
    }, 0);
  }

  getMostLikedSolarSystems() {
    this.galaxiesService.getMostLikedSolarSystems(this.currentGalaxy).subscribe({
      next: (success) => {
        this.mostLikedSolarSystems = success.data;
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later.', 5000, '/home');
      }
    })
  }

  getMostLikedWallpapers() {
    this.galaxiesService.getMostLikedWallpapers(this.currentGalaxy).subscribe({
      next: (success) => {
        this.mostLikedWallpapers = success.data;
        this.generateAllThumbnails(this.mostLikedWallpapers, 960, 540);
        
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later.', 5000, '/home');
      }
    })
  }

  getMostRecentSolarSystems() {
    this.galaxiesService.getMostRecentSolarSystems(this.currentGalaxy).subscribe({
      next: (success) => {
        this.mostRecentSolarSystems = success.data;
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later.', 5000, '/home');
      }
    })
  }

  getMostRecentWallpapers() {
    this.galaxiesService.getMostRecentWallpapers(this.currentGalaxy).subscribe({
      next: (success) => {
        this.mostRecentWallpapers = success.data;
        this.generateAllThumbnails(this.mostRecentWallpapers, 960, 540);
      },
      error: () => {
        this.notificationService.showError('Something went wrong, please try again later.', 5000, '/home');
      }
    })
  }

  toggleLike(type: LikeableType, object: any): void {
    this.likesService.like(type, this.currentGalaxy, object.solar_system_id, object.planet_id, object.moon_id, object.wallpaper_id)
      .subscribe(() => {
        object.is_liked = !object.is_liked;
        object.likes_count += object.is_liked ? 1 : -1;
      });
  }

  viewSystem(solarSystemId: number): void {
    this.navigationService.navigateTo(`/view-system/${solarSystemId}`);
  }

  viewWallpaper(solarSystemId: number): void {
    this.navigationService.navigateTo(`/view-wallpaper/${solarSystemId}`);
  }

  async generateAllThumbnails(wallpapers: Wallpaper[], width: number, height: number) {
    try {
      //get all systems
      const systems: SolarSystem[] = await Promise.all(
        wallpapers.map(wallpaper =>
          this.galaxiesService.getSolarSystem(1, wallpaper.solar_system_id).toPromise().then(
            (res) => res.data.solar_system
          )
        )
      );

      // Gerate thumbnails
      for (const system of systems) {
        try {
          const thumbnail = await this.thumbnailService.generateThumbnail(1, system.solar_system_id, width, height);
          this.thumbnails[system.solar_system_id] = thumbnail;
        } catch (error) {
          this.notificationService.showError('Something went wrong, please try again later.', 2500, '/home');
        }
      }

    } catch (error) {
      this.notificationService.showError('Something went wrong, please try again later.', 5000, '/home');
    }
  }
}
