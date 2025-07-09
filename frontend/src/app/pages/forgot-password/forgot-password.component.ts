import { Component } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth/auth.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { NotificationService } from '../../services/notifications/notification.service';

@Component({
  selector: 'app-forgot-password',
  imports: [ReactiveFormsModule, BackgroundStarsComponent],
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.css', '../../shared/styles/form.template.css']
})
export class ForgotPasswordComponent {
  forgotPasswordForm!: any;

  constructor(private fb: FormBuilder, public authService: AuthService, private notificationService: NotificationService){}

  ngAfterViewInit(): void {
  }

  ngOnInit(): void {
    // If user is allready logged in
    if (this.authService.isLoggedIn()) {
        this.authService.navigateTo('/home');
        return;
    }
    this.initForgotPasswordForm();
  }

  //init the form with validators
  initForgotPasswordForm() {
    this.forgotPasswordForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });
  }

  //user submit the password recovery form
  onForgotPasswordSubmit(){
    if (this.forgotPasswordForm.valid) {
      const { email } = this.forgotPasswordForm.value;

      this.authService.forgotPassword(email).subscribe({
        next: () => {
          this.notificationService.showSuccess('Password reset instructions have been sent to your email. Please check your inbox and spam folder.');
          this.authService.navigateTo('/home');
        },
        error: () => {
          this.notificationService.showError('Unable to send password reset email. Please verify your email address and try again.', 5000);
        }
      });
    }
  }


}
