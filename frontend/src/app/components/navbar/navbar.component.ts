import { Component } from '@angular/core';
import { GalaxiesService } from '../../services/galaxies-service/galaxies.service';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth/auth.service';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-navbar',
  imports: [FormsModule],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.css'
})
export class NavigationBarComponent {
  isMenuOpen = false;
  searchQuery: string = '';
  searchResults: any[] = [];

  constructor(private galaxiesService: GalaxiesService, private router: Router, public authService: AuthService){}

  onSearch(): void {
    this.isMenuOpen = false; //closing dropdown menu

    if (this.searchQuery.trim()) {
      this.galaxiesService.searchStars(this.searchQuery).subscribe({
        next: (response) => {
          this.searchResults = response.results || [];
          //here show something beautifull
          alert(JSON.stringify(this.searchResults));
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
    this.isMenuOpen = false; //closing dropdown menu
    this.router.navigateByUrl(url)
   }

   logout(){
    this.isMenuOpen = false; //closing dropdown menu
    this.authService.logout().subscribe();
   }

   toggleMenu(){
    this.isMenuOpen = !this.isMenuOpen;
   }
}
