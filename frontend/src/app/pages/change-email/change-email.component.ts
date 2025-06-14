import { AfterViewInit, Component, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth-service/auth-service';

@Component({
  selector: 'app-change-email',
  imports: [ReactiveFormsModule],
  templateUrl: './change-email.component.html',
  styleUrl: './change-email.component.css'
})
export class ChangeEmailComponent implements OnInit, AfterViewInit {
  changeEmailForm!: any;

  constructor(private fb: FormBuilder, public authService: AuthService){}

  ngAfterViewInit(): void {
    this.generateStars(30);
    this.createTwinkleAnimation();
  }

  ngOnInit(): void {
    this.initEmailForm();
  }

  //init the form with validators
  initEmailForm() {
    this.changeEmailForm = this.fb.group({
      currentPassword: ['', Validators.required],
      currentPasswordBis: ['', Validators.required],
      newEmail: ['', [Validators.required, Validators.email]],
      newEmailBis: ['', [Validators.required, Validators.email]]
    }, { validators: [this.passwordsMatchValidator, this.emailsMatchValidator] });
  }

  //custom math validators
  private passwordsMatchValidator(group: any) {
    const pass = group.get('currentPassword')?.value;
    const passBis = group.get('currentPasswordBis')?.value;
    return pass === passBis ? null : { passwordsMismatch: true };
  }
  //same for mails
  private emailsMatchValidator(group: any) {
    const email = group.get('newEmail')?.value;
    const emailBis = group.get('newEmailBis')?.value;
    return email === emailBis ? null : { emailsMismatch: true };
  }

  onEmailChangeSubmit(){
    if (this.changeEmailForm.valid) {
      const { currentPassword, newEmail } = this.changeEmailForm.value;

      this.authService.changeEmail(currentPassword, newEmail).subscribe({
        next: () => {
          //succesfully changed email, logout
          this.authService.logout().subscribe();
        },
        error: () => {
          //error, incorrect password or email
          alert('Invalid email or password');
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
}
