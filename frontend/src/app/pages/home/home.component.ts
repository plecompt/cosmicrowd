import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { GalaxyAnimationComponent } from '../../components/galaxy-animation/galaxy-animation.component';
import { GalaxiesService } from '../../services/galaxies/galaxies.service';
import { SolarSystem } from '../../interfaces/solar-system/solar-system.interface';
import { GalaxyStats } from '../../interfaces/galaxy/galaxy.interface'

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, FormsModule, GalaxyAnimationComponent],
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css'],
})
export class HomeComponent implements OnInit {
  stats: GalaxyStats | null = null;
  selectedSolarSystem: SolarSystem | null = null;
  recentSolarSystems: SolarSystem[] = [];
  mostLikedSolarSystems: SolarSystem[] = [];

  constructor(private galaxiesService: GalaxiesService) { }

  ngOnInit(): void {
  }

  ngOnDestroy(): void {
  }

  onSolarSystemClick(solarSystem: SolarSystem): void {
    this.selectedSolarSystem = solarSystem;
  }
}
