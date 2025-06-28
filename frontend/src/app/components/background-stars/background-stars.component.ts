// background-stars.component.ts
import { Component, OnInit, Input, ElementRef, ViewChild } from '@angular/core';

@Component({
  selector: 'app-background-stars',
  standalone: true,
  template: `
    <div class="stars-container-base" [class]="containerClass" #starsContainer>
      <!-- Les étoiles seront générées dynamiquement ici -->
    </div>
  `,
  styles: [`
    .stars-container-base {
      position: fixed; /* Position fixe pour couvrir tout l'écran */
      top: 0;
      left: 0;
      width: 100vw; /* Largeur de la viewport */
      height: 100vh; /* Hauteur de la viewport */
      background: transparent;
      overflow: hidden;
      pointer-events: none;
      z-index: -1; /* Derrière le contenu */
    }

    .star {
      position: absolute;
      border-radius: 50%;
      animation: twinkle infinite ease-in-out;
    }

    @keyframes twinkle {
      0%, 100% {
        opacity: 0.2;
        transform: scale(0.8);
      }
      50% {
        opacity: 1;
        transform: scale(1.2);
      }
    }

    .star:nth-child(3n) {
      background-color: #ffffff;
    }

    .star:nth-child(4n) {
      background-color: #ffeeaa;
    }

    .star:nth-child(5n) {
      background-color: #aaeeff;
    }

    .star:nth-child(7n) {
      background-color: #ffccdd;
    }
  `]
})
export class BackgroundStarsComponent implements OnInit {
  @Input() numberOfStars: number = 100;
  @Input() containerClass: string = '';
  @ViewChild('starsContainer', { static: true }) starsContainer!: ElementRef;

  ngOnInit(): void {
    setTimeout(() => {
      this.generateStars();
    }, 100);
  }

  generateStars(): void {
    const container = this.starsContainer.nativeElement;
    
    const existingStars = container.querySelectorAll('.star');
    existingStars.forEach((star: any) => star.remove());
    
    for (let i = 0; i < this.numberOfStars; i++) {
      const star = document.createElement('div');
      star.className = 'star';
      
      const left = Math.random() * 100; // % du container qui fait maintenant 100vw
      const top = Math.random() * 100;  // % du container qui fait maintenant 100vh
      const size = Math.random() * 2.5 + 0.5;
      const duration = Math.random() * 3 + 2;
      const delay = Math.random() * 4;
      
      star.style.cssText = `
        left: ${left}%;
        top: ${top}%;
        width: ${size}px;
        height: ${size}px;
        animation-duration: ${duration}s;
        animation-delay: ${delay}s;
        background-color: white;
      `;
      
      container.appendChild(star);
    }
  }
}
