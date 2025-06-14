import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { GalaxyAnimationComponent } from '../../components/galaxy-animation/galaxy-animation.component';
import { GalaxiesService } from '../../services/galaxies-service/galaxies.service';
import { SolarSystemAnimation } from '../../interfaces/solar-system/solar-system.interface';
import { GalaxyStats } from '../../interfaces/galaxy/galaxy.interface'
import { WelcomeOverlayComponent } from '../../components/welcome-overlay/welcome-overlay.component';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, FormsModule, GalaxyAnimationComponent, WelcomeOverlayComponent],
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css'],
})
export class HomeComponent implements OnInit {
  stats: GalaxyStats | null = null;
  selectedSolarSystem: SolarSystemAnimation | null = null;
  recentSolarSystems: SolarSystemAnimation[] = [];
  mostLikedSolarSystems: SolarSystemAnimation[] = [];

  constructor(private galaxiesService: GalaxiesService) { }

  ngOnInit(): void {
    //this.loadStats();
    //this.loadRecentStars();
    //this.loadMostLikedStars();
  }

  // Nettoyage au destroy
  ngOnDestroy(): void {
  }


  loadStats(): void {
    this.galaxiesService.getStats().subscribe({
      next: (response) => {
        if (response.success) {
          this.stats = response.stats;
        }
      },
      error: (error) => {
        console.error('Erreur lors du chargement des statistiques:', error);
      }
    });
  }

  loadRecentSolarSystems(): void {
    this.galaxiesService.getRecentSolarSystems().subscribe({
      next: (response) => {
        if (response) {
          this.recentSolarSystems = response.recent_solar_systems;
        }
      },
      error: (error) => {
        console.error('Erreur lors du chargement des system solaires récents:', error);
      }
    });
  }

  onSolarSystemClick(solarSystem: SolarSystemAnimation): void {
    this.selectedSolarSystem = solarSystem;
    console.log('Solar System clicked:', solarSystem);
  }

  loadMostLikedSolarSytems(): void {
    this.galaxiesService.getMostLikedSolarSystems().subscribe({
      next: (response) => {
        if (response) {
          this.mostLikedSolarSystems = response.most_liked_solar_systems;
        }
      },
      error: (error) => {
        console.error('Erreur lors du chargement des étoiles populaires:', error);
      }
    });
  }

}
