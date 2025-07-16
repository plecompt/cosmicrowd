import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Moon } from '../../interfaces/solar-system/moon.interface';

@Component({
  selector: 'app-moon-edit',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './moon-edit.component.html',
  styleUrls: ['./moon-edit.component.css']
})
export class MoonEditComponent {
  @Input() moon: Moon | null = null;
  @Input() isVisible: boolean = false;
  @Output() save = new EventEmitter<Moon>();
  @Output() close = new EventEmitter<void>();

  closeModal(): void {
    this.close.emit();
  }

  saveMoon(): void {
    if (!this.moon) {
      return;
    }

    // Validate perigee <= apogee
    if (this.moon.moon_perigee > this.moon.moon_apogee) {
      alert('Perigee must be less than or equal to apogee');
      return;
    }

    this.save.emit(this.moon);
  }
}
