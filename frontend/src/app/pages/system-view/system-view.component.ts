import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { NotificationService } from '../../services/notifications/notification.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { SystemAnimationComponent } from '../../components/system-animation/system-animation.component';

@Component({
  selector: 'app-system-view',
  imports: [SystemAnimationComponent],
  templateUrl: './system-view.component.html',
  styleUrl: './system-view.component.css'
})
export class SystemViewComponent implements OnInit {
  currentGalaxy: number = 1;
  solarSystemId!: number;
  solarSystem!: SolarSystem;
  isLoading: boolean = true;

  constructor(
    private route: ActivatedRoute,
    private galaxiesService: GalaxiesService,
    private notificationService: NotificationService
  ) { }

  ngOnInit(): void {
    this.solarSystemId = this.route.snapshot.params['id'];
    this.getSolarSystem();
  }

  getSolarSystem() {
    this.galaxiesService.getSolarSystem(this.currentGalaxy, this.solarSystemId).subscribe({
      next: (data) => {
        this.solarSystem = data.solar_system;
        this.isLoading = false;

        if (!this.solarSystem) {
          this.notificationService.showError('Something went wrong, please try again later', 5000, '/home');
          return;
        }
      },
      error: (error) => {
        this.isLoading = false;
        this.notificationService.showError(error.error?.message || 'Something went wrong, please try again later', 5000, '/home');
      }
    });
  }
}