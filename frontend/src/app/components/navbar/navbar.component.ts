import { Component } from '@angular/core';
import { GalaxiesService } from '../../services/galaxies-service/galaxies.service';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-navbar',
  imports: [],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.css'
})
export class NavigationBarComponent {
  searchQuery: string = '';
  searchResults: any[] = [];

  constructor(private galaxiesService: GalaxiesService, private router: Router, public authService: AuthService){}

  onSearch(): void {
    if (this.searchQuery.trim()) {
      this.galaxiesService.searchStars(this.searchQuery).subscribe({
        next: (response) => {
          this.searchResults = response.results || [];
        },
        error: (error) => {
          console.error('Erreur lors de la recherche:', error);
        }
      });
    }
  }

  clearSearch(): void {
    this.searchQuery = '';
    this.searchResults = [];
  }

   navigateTo(url: string){
    this.router.navigateByUrl(url)
   }
}
