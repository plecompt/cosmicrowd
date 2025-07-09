import { Component } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth/auth.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { FormValidatorService } from '../../services/form-validators/form-validator-service';
import { CustomValidatorsService } from '../../services/custom-validators/custom-validators.service';
import { NotificationService } from '../../services/notifications/notification.service';

@Component({
  selector: 'app-change-password',
  imports: [ReactiveFormsModule, BackgroundStarsComponent],
  templateUrl: './change-password.component.html',
  styleUrls: ['./change-password.component.css', '../../shared/styles/form.template.css']
})
export class ChangePasswordComponent {
  changePasswordForm!: any;
  changePasswordErrorMessage: string = "";

  constructor(private fb: FormBuilder, public authService: AuthService, private notificationService: NotificationService, public formValidator: FormValidatorService, customValidator: CustomValidatorsService){}

  ngAfterViewInit(): void {
  }

  ngOnInit(): void {
    // If user is not logged in
    if (!this.authService.isLoggedIn()) {
        this.authService.navigateTo('/home');
        return;
    }
    this.initPasswordForm();
  }

  //init the form with validators
  initPasswordForm() {
    this.changePasswordForm = this.fb.group({
      currentPassword: ['', Validators.required],
      currentPasswordBis: ['', Validators.required],
      newPassword: ['', [Validators.required, CustomValidatorsService.strongPassword()]],
      newPasswordBis: ['', [Validators.required, CustomValidatorsService.strongPassword()]]
    }, { validators: [CustomValidatorsService.passwordsMatch('currentPassword', 'currentPasswordBis'), CustomValidatorsService.passwordsMatch('newPassword', 'newPasswordBis', 'newPasswordMatch')] });
  }

  onPasswordChangeSubmit(){
    if (this.changePasswordForm.valid) {
      const { currentPassword, newPassword } = this.changePasswordForm.value;

      this.authService.changePassword(currentPassword, newPassword).subscribe({
        next: () => {
          this.notificationService.showSuccess('Password successfully modified !');
          this.authService.logout().subscribe();
        },
        error: () => {
          this.changePasswordErrorMessage = "Something went wrong, please try again later";
        }
      })
    } else {
      this.changePasswordForm.markAllAsTouched();
    }
  }
}
