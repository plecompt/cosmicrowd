import { Component } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth/auth.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';

@Component({
  selector: 'app-change-password',
  imports: [ReactiveFormsModule, BackgroundStarsComponent],
  templateUrl: './change-password.component.html',
  styleUrl: './change-password.component.css'
})
export class ChangePasswordComponent {
  changePasswordForm!: any;

  constructor(private fb: FormBuilder, public authService: AuthService){}

  ngAfterViewInit(): void {
  }

  ngOnInit(): void {
    this.initPasswordForm();
  }

  //init the form with validators
  initPasswordForm() {
    this.changePasswordForm = this.fb.group({
      currentPassword: ['', Validators.required],
      currentPasswordBis: ['', Validators.required],
      newPassword: ['', [Validators.required, this.strongPasswordValidator]],
      newPasswordBis: ['', [Validators.required, this.strongPasswordValidator]]
    }, { validators: [this.passwordsMatchValidator, this.newPasswordMatchValidator] });
  }

  //custom math validators
  private passwordsMatchValidator(group: any) {
    const pass = group.get('currentPassword')?.value;
    const passBis = group.get('currentPasswordBis')?.value;
    return pass === passBis ? null : { passwordsMismatch: true };
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

  onPasswordChangeSubmit(){
    if (this.changePasswordForm.valid) {
      const { currentPassword, newPassword } = this.changePasswordForm.value;

      this.authService.changePassword(currentPassword, newPassword).subscribe({
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
}
