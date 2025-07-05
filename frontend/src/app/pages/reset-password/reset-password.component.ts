import { Component } from '@angular/core';
import { AuthService } from '../../services/auth/auth.service';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';

@Component({
  selector: 'app-reset-password',
  imports: [ReactiveFormsModule, BackgroundStarsComponent],
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.css', '../../shared/styles/form.template.css']
})
export class ResetPasswordComponent {
  resetPasswordForm!: any;
  token!: string;
  isValidToken: boolean = false;

  constructor(private fb: FormBuilder, public authService: AuthService, private route: ActivatedRoute, private router: Router){}

  ngAfterViewInit(): void {
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

      this.authService.setNewPassword(newPassword, this.token).subscribe({
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

  navigateTo(url: string) {
    this.router.navigateByUrl(url)
  }
}
