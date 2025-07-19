import { Component } from '@angular/core';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth/auth.service';
import { FormsModule } from '@angular/forms';
import { ModalService } from '../../services/modal/modal.service';
import { NotificationService } from '../../services/notifications/notification.service';

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

  constructor(private galaxiesService: GalaxiesService, private router: Router, public authService: AuthService, private modalService: ModalService, private notificationService: NotificationService){}

  onSearch(): void {
    this.isMenuOpen = false; //closing dropdown menu

    if (this.searchQuery.trim()) {
      this.galaxiesService.searchStars(this.searchQuery).subscribe({
        next: (response) => {
          this.searchResults = response.data.results || [];
          this.showModal(this.searchResults);
        },
        error: (error) => {
          this.notificationService.showError(error.error.message || 'Something went wrong, please try again later', 5000, '/home');
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
    this.authService.navigateTo('/home');
   }

   toggleMenu(){
    this.isMenuOpen = !this.isMenuOpen;
   }

  private showModal(searchResult: any): void {
    let formattedMessage = '';

    // Moons
    if (searchResult.moons && searchResult.moons.length > 0) {
      formattedMessage += 'Moons:\n';
      for (const moon of searchResult.moons) {
        formattedMessage += `  - ${moon.moon_name}: ${moon.moon_desc}\n`;
      }
      formattedMessage += '\n';
    }

    // Planets
    if (searchResult.planets && searchResult.planets.length > 0) {
      formattedMessage += 'Planets:\n';
      for (const planet of searchResult.planets) {
        formattedMessage += `  - ${planet.planet_name}: ${planet.planet_desc}\n`;
      }
      formattedMessage += '\n';
    }

    // Solar Systems
    if (searchResult.solar_systems && searchResult.solar_systems.length > 0) {
      formattedMessage += 'Solar Systems:\n';
      for (const system of searchResult.solar_systems) {
        formattedMessage += `  - ${system.solar_system_name}: ${system.solar_system_desc}\n`;
      }
      formattedMessage += '\n';
    }

    // Users
    if (searchResult.users && searchResult.users.length > 0) {
      formattedMessage += 'Users:\n';
      for (const user of searchResult.users) {
        formattedMessage += `  - ${user.user_login}\n`;
      }
      formattedMessage += '\n';
    }

    // Fallback if empty
    if (formattedMessage.trim() === '') {
      formattedMessage = 'No results found.';
    }

    this.modalService.show({
      title: 'Search Results',
      content: formattedMessage,
      showCancel: true,
      onConfirm: () => {
      },
      onCancel: () => {
      }
    });
  }

}
