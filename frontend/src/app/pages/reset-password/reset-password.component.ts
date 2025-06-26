import { Component } from '@angular/core';
import { AuthService } from '../../services/auth-service/auth-service';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-reset-password',
  imports: [ReactiveFormsModule],
  templateUrl: './reset-password.component.html',
  styleUrl: './reset-password.component.css'
})
export class ResetPasswordComponent {
  resetPasswordForm!: any;
  token!: string;
  isValidToken: boolean = false;

  constructor(private fb: FormBuilder, public authService: AuthService, private route: ActivatedRoute, private router: Router){}

  ngAfterViewInit(): void {
    this.generateStars(30);
    this.createTwinkleAnimation();
  }

  ngOnInit(): void {
    this.initResetPasswordForm();
    this.verifyToken();
  }

  verifyToken(){
    this.route.queryParamMap.subscribe(params => {
      const token = params.get('token');
      if (token) {
        this.authService.verifyResetToken(token.toString()).subscribe({
          next: () => {
            this.token = token;
            this.isValidToken = true;
          },
          error: (error) => {
            //redirection to home
            this.navigateTo('/');
          }
        }
        );
      }
    });
  }

  //init the form with validators
  initResetPasswordForm() {
    this.resetPasswordForm = this.fb.group({
      newPassword: ['', [Validators.required, this.strongPasswordValidator]],
      newPasswordBis: ['', [Validators.required, this.strongPasswordValidator]]
    }, { validators: [this.newPasswordMatchValidator] });
  }

  //same for new password
  private newPasswordMatchValidator(group: any) {
    const newPass = group.get('newPassword')?.value;
    const newPassBis = group.get('newPasswordBis')?.value;
    return newPass === newPassBis ? null : { newPasswordMismatch: true };
  }

  // Strong password validator
  private strongPasswordValidator(control: any) {
    const value = control.value;
    if (!value) return null;

    const hasMinLength = value.length >= 12;
    const hasUpperCase = /[A-Z]/.test(value);
    const hasNumber = /[0-9]/.test(value);
    const hasSpecialChar = /[^A-Za-z0-9]/.test(value);

    const isValid = hasMinLength && hasUpperCase && hasNumber && hasSpecialChar;

    return isValid ? null : { weakPassword: true };
  }

  onPasswordResetSubmit(){
    if (this.resetPasswordForm.valid) {
      const { newPassword } = this.resetPasswordForm.value;

      this.authService.changePassword(newPassword, this.token).subscribe({
        next: () => {
          //succesfully changed password, logout
          this.authService.logout().subscribe();
        },
        error: () => {
          //error, incorrect old password or missmatch
          alert('Invalid password');
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
