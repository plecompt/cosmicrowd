import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth/auth.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { User } from '../../models/user/user.model';
import { NotificationService } from '../../services/notifications/notification.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { Wallpaper } from '../../interfaces/solar-system/wallpaper.interface';
import { LikeableType, LikesService } from '../../services/likes/likes.service';
import { NavigationService } from '../../services/navigation/navigation.service';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { DatePipe } from '@angular/common';

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

  constructor(public authService: AuthService, private notificationService: NotificationService, public likesService: LikesService, public navigationService: NavigationService, private galaxiesService: GalaxiesService) { }

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
        console.log(this.user);
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
        console.log(this.mostLikedSolarSystems);
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
        console.log(this.mostLikedWallpapers);
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
        console.log(this.mostRecentSolarSystems);
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
        console.log(this.mostRecentWallpapers);
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
    // wallpaper id or solarSystemId ???
    // redirect to vue wallpaper view or something
  }

}
