import { Component, ElementRef, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { AuthService } from '../../services/auth/auth.service';
import { FormsModule } from '@angular/forms';
import { ModalService } from '../../services/modal/modal.service';
import { NotificationService } from '../../services/notifications/notification.service';
import { NavigationService } from '../../services/navigation/navigation.service';
import { Router } from '@angular/router';
import { SearchService } from '../../services/search/search-service';

@Component({
  selector: 'app-navbar',
  imports: [FormsModule],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.css'
})
export class NavigationBarComponent implements OnInit, OnDestroy {
  isMenuOpen: boolean = false;
  isFiltersOpen: boolean = false;
  searchQuery: string = '';
  searchResults: any[] = [];
  isNavbarVisible = true;
  filters = { users: true, systems: true, planets: true, moons: true };
  lastScrollTop = 0;
  scrollListener?: () => void;
  clickListener?: (event: Event) => void;

  @ViewChild('filtersDropdown') filtersDropdown!: ElementRef;
  @ViewChild('userDropdown') userDropdown!: ElementRef;
  @ViewChild('searchContainer') searchContainer!: ElementRef;
  @ViewChild('rightPart') rightPart!: ElementRef;

  constructor(
    private searchService: SearchService,
    public authService: AuthService,
    private modalService: ModalService,
    private notificationService: NotificationService,
    private navigationService: NavigationService,
    private router: Router) { }

  ngOnInit() {
    this.scrollListener = this.onScroll.bind(this);
    this.clickListener = this.onDocumentClick.bind(this);

    window.addEventListener('scroll', this.scrollListener);
    document.addEventListener('click', this.clickListener);
  }

  ngOnDestroy() {
    this.scrollListener && window.removeEventListener('scroll', this.scrollListener);
    this.clickListener && document.removeEventListener('click', this.clickListener);
  }

  //User clicked on search
  onSearch(): void {
    this.isMenuOpen = false; //closing dropdown menu

    if (this.searchQuery.trim()) {
      this.searchService.search(this.searchQuery, this.filters).subscribe({
        next: (response) => {
          this.searchResults = response.data.results || [];
          this.showModal(this.searchResults);
        },
        error: () => {
          this.notificationService.showError('Something went wrong, please try again later.', 2500, '/home');
        }
      });
    }
  }

  //User scrolled
  private onScroll(): void {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollTop <= 0) {
      this.isNavbarVisible = true;
    } else {
      this.isNavbarVisible = false;
    }

    this.lastScrollTop = scrollTop;
  }

  //User clicked
  private onDocumentClick(event: Event): void {
    const target = event.target as HTMLElement;

    // Close filters dropdown if click outside OR on search input
    if (this.isFiltersOpen && this.searchContainer) {
      const isClickOnInput = target.classList.contains('search-input');
      const isClickOutsideContainer = !this.searchContainer.nativeElement.contains(target);

      if (isClickOutsideContainer || isClickOnInput) {
        this.isFiltersOpen = false;
      }
    }

    // Close user menu if click outside
    if (this.isMenuOpen && this.rightPart) {
      if (!this.rightPart.nativeElement.contains(target)) {
        this.isMenuOpen = false;
      }
    }
  }

  toggleFilters(): void {
    this.isFiltersOpen = !this.isFiltersOpen;
  }

  clearSearch(): void {
    this.searchQuery = '';
    this.searchResults = [];
  }

  navigateTo(url: string) {
    this.isMenuOpen = false;
    this.navigationService.navigateTo(url)
  }

  logout() {
    this.isMenuOpen = false;
    this.authService.logout().subscribe();
    this.navigationService.navigateTo('/home');
  }

  toggleMenu() {
    this.isMenuOpen = !this.isMenuOpen;
  }

  navigateToProfile(): void {
    this.navigateTo('/profile/' + localStorage.getItem('user_id'));
  }


  private showModal(searchResult: any): void {
    let htmlContent = '';

    // Generate sections
    htmlContent += this.createSection(searchResult.moons, 'Moons', 'view-system/', 'moon_name', 'moon_desc');
    htmlContent += this.createSection(searchResult.planets, 'Planets', 'view-system/', 'planet_name', 'planet_desc');
    htmlContent += this.createSection(searchResult.solar_systems, 'Solar Systems', 'view-system/', 'solar_system_name', 'solar_system_desc');
    htmlContent += this.createSection(searchResult.users, 'Users', 'profile/', 'user_login', '');

    if (htmlContent.trim() === '') {
      htmlContent = '<p>No results found.</p>';
    }

    this.modalService.show({
      title: 'Search Results',
      content: htmlContent,
      showConfirm: true,
      onConfirm: () => { },
      onCancel: () => { }
    });

    setTimeout(() => {
      document.querySelectorAll('[data-route]').forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          const route = (e.target as HTMLElement).getAttribute('data-route');
          if (route) {
            this.modalService.close();
            this.router.navigate([route]);
          }
        });
      });
    }, 200);
  }

    // Create a section of searchResult
    createSection (items: any[], title: string, routePrefix: string, nameField: string, descField: string) {
      if (!items || items.length === 0) return '';

      let section = `<div style="margin: 10px;"><h3 style="text-align: center; margin: 20px !important;">${title}</h3><ul>`;

      for (const item of items) {
        const route = routePrefix + item[routePrefix === 'profile/' ? 'user_id' : 'solar_system_id'];
        
        section += `
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
          <li style="width: 85%;">
            <a style="text-decoration: none;" data-route="${route}">${item[nameField]}</a>${descField ? ': ' + item[descField] : ''}
          </li>
          <button style="width: 10%; text-align: center; padding: 5px;" data-route="${route}">🔍</button>
        </div>`;
      }

      return section + '</ul></div>';
    };

}
