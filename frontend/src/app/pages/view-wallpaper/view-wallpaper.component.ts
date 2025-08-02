import { Component } from '@angular/core';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { Wallpaper } from '../../interfaces/wallpaper/wallpaper.interface';
import { ActivatedRoute } from '@angular/router';
import { WallpaperService } from '../../services/wallpaper/wallpaper-service';
import { NotificationService } from '../../services/notifications/notification.service';
import { SystemAnimationComponent } from '../../components/system-animation/system-animation.component';
import { WallpaperSettings } from '../../interfaces/wallpaper/wallpaper.interface';
import { LikeableType, LikesService } from '../../services/likes/likes.service';
import { AuthService } from '../../services/auth/auth.service';
import { SolarSystemsService } from '../../services/solar-systems/solar-systems-service';

@Component({
  selector: 'app-view-wallpaper',
  imports: [SystemAnimationComponent],
  templateUrl: './view-wallpaper.component.html',
  styleUrl: './view-wallpaper.component.css'
})
export class ViewWallpaperComponent {
  currentGalaxy: number = 1; //atm there is only one galaxy, so hardcoding to 1, might change in the futur
  solarSystemId!: number;
  solarSystem!: SolarSystem;
  wallpaper!: Wallpaper;
  renderOptions!: WallpaperSettings;
  isLoading: boolean = true;
  showLikeButton: boolean = false;

  private likedObjects = new Map<LikeableType, Set<number>>([
    [LikeableType.WALLPAPER, new Set<number>()]
  ]);

  constructor(
    private route: ActivatedRoute,
    private wallpaperService: WallpaperService,
    private solarSystemsService: SolarSystemsService,
    private notificationService: NotificationService,
    private likesService: LikesService,
    public authService: AuthService
  ) {}
  
  ngOnInit() {
    this.solarSystemId = this.route.snapshot.params['id'];
    this.getSolarSystem();
  }

  getSolarSystem() {
      this.solarSystemsService.getSolarSystem(this.currentGalaxy, this.solarSystemId).subscribe({
        next: (solarSystem) => {
          this.solarSystem = solarSystem.data.solar_system;
          this.getWallpaper();
        },
        error: (error) => {
          this.notificationService.showError(error.message || 'Something went wrong, please try again later', 5000, '/home');
        }
      });
  }

  getWallpaper(){
    this.wallpaperService.getWallpaper(1, this.solarSystemId).subscribe({
      next: (success) => {
        this.wallpaper = success.data;
        this.renderOptions = JSON.parse(this.wallpaper.wallpaper_settings);
        
        if (this.authService.isLoggedIn()) {
          this.loadLikes();
          this.showLikeButton = true;
        } else {
          this.showLikeButton = false;
          this.isLoading = false;
        }
      },
      error: (error) => {
        this.notificationService.showError(error.message || 'Something went wrong, please try again later.', 2500, '/home');
      }
    })
  }

  private loadLikes(): void {
    if (!this.wallpaper) return;
    
    const ids = {
      wallpaper: [this.wallpaper.wallpaper_id],
    };

    const typeMapping = {
      wallpaper: LikeableType.WALLPAPER,
    };

    Object.entries(ids).forEach(([type, typeIds]) => {
      if (typeIds.length > 0) {
        this.likesService.getUserLikes(typeIds.join(','), type).subscribe({
          next: (response) => {
            const likeableType = typeMapping[type as keyof typeof typeMapping];
            response.data.forEach((id: number) => this.likedObjects.get(likeableType)?.add(id));
            this.isLoading = false;
          }
        });
      }
    });
  }

  isSystemLiked(): boolean {
    return this.likedObjects.get(LikeableType.WALLPAPER)?.has(this.wallpaper.wallpaper_id) || false;
  }

  toggleSystemLike(): void {
    this.likesService.like(
      LikeableType.WALLPAPER,
      this.currentGalaxy,
      this.solarSystemId,
      undefined,
      undefined,
      this.wallpaper.wallpaper_id
    ).subscribe({
      next: (success) => {
        this.toggleLikeCache(LikeableType.WALLPAPER, this.wallpaper.wallpaper_id);
        this.notificationService.showSuccess(success.message, 2000);
      },
      error: () => this.notificationService.showError('Something went wrong, please try again later.', 2500)
    });
  }

  private toggleLikeCache(type: LikeableType, id: number): void {
    const likedSet = this.likedObjects.get(type);
    if (likedSet?.has(id)) {
      likedSet.delete(id);
    } else {
      likedSet?.add(id);
    }
  }
}