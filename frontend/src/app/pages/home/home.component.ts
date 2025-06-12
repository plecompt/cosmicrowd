import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { GalaxyAnimationComponent } from '../../components/galaxy-animation/galaxy-animation.component';
import { GalaxiesService } from '../../services/galaxies-service/galaxies.service';
import { StarAnimation } from '../../interfaces/stars/star.interface';
import { GalaxyStats } from '../../interfaces/galaxies/galaxy.interface'
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
  selectedStar: StarAnimation | null = null;
  recentStars: StarAnimation[] = [];
  mostLikedStars: StarAnimation[] = [];

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

  loadRecentStars(): void {
    this.galaxiesService.getRecentStars(5).subscribe({
      next: (response) => {
        if (response.success) {
          this.recentStars = response.recent_stars;
        }
      },
      error: (error) => {
        console.error('Erreur lors du chargement des étoiles récentes:', error);
      }
    });
  }

  onStarClick(star: StarAnimation): void {
    this.selectedStar = star;
    console.log('Étoile cliquée:', star);
  }

  loadMostLikedStars(): void {
    this.galaxiesService.getMostLikedStars(5).subscribe({
      next: (response) => {
        if (response.success) {
          this.mostLikedStars = response.most_liked_stars;
        }
      },
      error: (error) => {
        console.error('Erreur lors du chargement des étoiles populaires:', error);
      }
    });
  }

}
