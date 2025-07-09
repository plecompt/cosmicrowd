import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth/auth.service';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute } from '@angular/router';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { NotificationService } from '../../services/notifications/notification.service';
import { FormValidatorService } from '../../services/form-validators/form-validator-service';
import { CustomValidatorsService } from '../../services/custom-validators/custom-validators.service';

@Component({
  selector: 'app-reset-password',
  imports: [ReactiveFormsModule, BackgroundStarsComponent],
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.css', '../../shared/styles/form.template.css']
})
export class ResetPasswordComponent implements OnInit {
  resetPasswordForm!: FormGroup;
  token!: string;
  isValidToken: boolean = false;
  errorMessage: string = "";

  constructor(private fb: FormBuilder, public authService: AuthService, private route: ActivatedRoute, private notificationService: NotificationService, public formValidator: FormValidatorService, private customValidators: CustomValidatorsService){}


  ngOnInit(): void {
    this.initResetPasswordForm();
    this.verifyToken();
  }

    //init the form with validators
  initResetPasswordForm() {
    this.resetPasswordForm = this.fb.group({
      newPassword: ['', [Validators.required, CustomValidatorsService.strongPassword(), CustomValidatorsService.noSpaces()]],
      newPasswordBis: ['', [Validators.required, CustomValidatorsService.strongPassword(), CustomValidatorsService.noSpaces()]]
    }, {validators: [CustomValidatorsService.passwordsMatch('newPassword', 'newPasswordBis')] });
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
          error:() => {
            this.notificationService.showError('This link is not valid or expired');
          }
        }
        );
      }
    });
  }

  onPasswordResetSubmit(){
    if (this.resetPasswordForm.valid) {
      const { newPassword } = this.resetPasswordForm.value;

      this.authService.setNewPassword(newPassword, this.token).subscribe({
        next: () => {
          this.notificationService.showSuccess('Password successfully changed !');
          this.authService.logout().subscribe();
        },
        error: () => {
          this.errorMessage = 'Something went wrong, please try again later';
        }
      })
    }
  }

}
