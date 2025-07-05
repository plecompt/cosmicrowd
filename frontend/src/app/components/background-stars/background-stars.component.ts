import { Component, OnInit, Input, ElementRef, ViewChild, OnDestroy } from '@angular/core';

@Component({
  selector: 'app-background-stars',
  standalone: true,
  templateUrl: './background-stars.component.html',
  styleUrls: ['./background-stars.component.css']
})
export class BackgroundStarsComponent implements OnInit, OnDestroy {
  @Input() numberOfStars: number = 15;
  @Input() containerClass: string = 'stars-container';
  @ViewChild('starsContainer', { static: true }) starsContainer!: ElementRef;

  private readonly colors: string[] = ['#ffffff', '#b3d9ff', '#ffffcc', '#ffccdd', '#ccffcc'];

  ngOnInit(): void {
    this.createTwinkleAnimation();
    setTimeout(() => {
      this.generateStars();
    }, 100);
  }

  ngOnDestroy(): void {
    this.clearStars();
  }

  private generateStars(): void {
    const container: HTMLElement = this.starsContainer.nativeElement;
    
    if (!container) return;

    this.clearStars();

    // Container dimension
    const containerRect: DOMRect = container.getBoundingClientRect();
    const containerWidth: number = containerRect.width || container.clientWidth;
    const containerHeight: number = containerRect.height || container.clientHeight;

    for (let i: number = 0; i < this.numberOfStars; i++) {
      const star: HTMLDivElement = document.createElement('div');
      star.className = 'star';
      
      // Properties
      const size: number = Math.random() * 3 + 1;
      const x: number = Math.random() * Math.max(0, containerWidth - size);
      const y: number = Math.random() * Math.max(0, containerHeight - size);
      const delay: number = Math.random() * 3;
      const duration: number = Math.random() * 2 + 2;
      const color: string = this.colors[Math.floor(Math.random() * this.colors.length)];

      star.style.cssText = `
        left: ${x}px;
        top: ${y}px;
        width: ${size}px;
        height: ${size}px;
        background: ${color};
        box-shadow: 0 0 ${size + 2}px ${color};
        animation-delay: ${delay}s;
        animation-duration: ${duration}s;
        position: absolute;
        animation: twinkle ${duration}s infinite ease-in-out ${delay}s;
      `;
      
      container.appendChild(star);
    }
  }

  private clearStars(): void {
    const container: HTMLElement = this.starsContainer.nativeElement;
    if (container) {
      container.innerHTML = '';
    }
  }

  private createTwinkleAnimation(): void {
    const styleId = 'twinkle-animation';
    
    // If animation already exist
    if (document.getElementById(styleId)) {
      return;
    }
    
    const style = document.createElement('style');
    style.id = styleId;
    style.textContent = `
      @keyframes twinkle {
        0%, 100% {
          opacity: 1;
          transform: scale(1);
        }
        25% {
          opacity: 0.2;
          transform: scale(0.8);
        }
        50% {
          opacity: 0.05;
          transform: scale(0.6);
        }
        75% {
          opacity: 0.3;
          transform: scale(0.9);
        }
      }
    `;
    
    document.head.appendChild(style);
  }
}
