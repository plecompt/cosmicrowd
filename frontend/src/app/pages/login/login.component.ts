import { AfterViewInit, Component, OnDestroy, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth-service/auth-service';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent implements OnInit, AfterViewInit, OnDestroy {
  loginForm!: FormGroup;

  constructor(private router: Router, public authService: AuthService, private fb: FormBuilder) { 
  }

  ngOnDestroy(): void {
  }

  ngAfterViewInit(): void {
    this.generateStars(30);
    this.createTwinkleAnimation();
  }

  ngOnInit(): void {
    this.initLoginForm();
  }


  initLoginForm(){
    this.loginForm = this.fb.group({
      email: [''],
      password: ['']
    });
  }

  onLoginSubmit(form: any){
    //Here we need to check form, wont do it here, because i'll have to redo it
    //let's just check inputs are not empty
    if (this.loginForm.value.email && this.loginForm.value.email.length > 0 && this.loginForm.value.password && this.loginForm.value.password.length > 0){

      this.authService.login(this.loginForm.value.email , this.loginForm.value.password).subscribe({
        next: () => {
          this.router.navigateByUrl('/home');
        },
        error: () => {
          alert("Invalid email/password");
        }
      })
    }
  }


  generateStars(numberOfStars: number = 15, containerSelector: string = '.stars-container'): void {
    const container: HTMLElement | null = document.querySelector(containerSelector);
    
    if (container) {
      container.innerHTML = '';

      // Types pour les propriétés
      const colors: string[] = ['#ffffff', '#b3d9ff', '#ffffcc', '#ffccdd', '#ccffcc'];
      
      // Dimensions du conteneur
      const containerRect: DOMRect = container.getBoundingClientRect();
      const containerWidth: number = containerRect.width || container.clientWidth;
      const containerHeight: number = containerRect.height || container.clientHeight;

      for (let i: number = 0; i < numberOfStars; i++) {
        const star: HTMLDivElement = document.createElement('div');
        star.className = 'star';
        
        // Propriétés typées
        const size: number = Math.random() * 3 + 1;
        const x: number = Math.random() * Math.max(0, containerWidth - size);
        const y: number = Math.random() * Math.max(0, containerHeight - size);
        const delay: number = Math.random() * 3;
        const duration: number = Math.random() * 2 + 2;
        const color: string = colors[Math.floor(Math.random() * colors.length)];

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
  }

  private createTwinkleAnimation(): void {
    const styleId = 'twinkle-animation';
    
    // Vérifier si l'animation existe déjà
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

  navigateTo(url: string) {
    this.router.navigateByUrl(url)
  }
}
