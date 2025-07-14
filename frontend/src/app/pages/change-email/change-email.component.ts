import { AfterViewInit, Component, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth/auth.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { NotificationService } from '../../services/notifications/notification.service';
import { FormValidatorService } from '../../services/form-validators/form-validator-service';
import { CustomValidatorsService } from '../../services/custom-validators/custom-validators.service';

@Component({
  selector: 'app-change-email',
  imports: [ReactiveFormsModule, BackgroundStarsComponent],
  templateUrl: './change-email.component.html',
  styleUrls: ['./change-email.component.css', '../../shared/styles/form.template.css']
})
export class ChangeEmailComponent implements OnInit, AfterViewInit {
  changeEmailForm!: any;
  changeEmailErrorMessage: string = "";

  constructor(private fb: FormBuilder, public authService: AuthService, private notificationService: NotificationService, public formValidator: FormValidatorService, private customValidators: CustomValidatorsService){}

  ngAfterViewInit(): void {
  }

  ngOnInit(): void {
    // If user is not logged in
    if (!this.authService.isLoggedIn()) {
        this.notificationService.showError('You can\'t access this page', 3000, '/home');
        return;
    }
    this.initEmailForm();
  }

  //init the form with validators
  initEmailForm() {
    this.changeEmailForm = this.fb.group({
      password: ['', Validators.required],
      passwordBis: ['', Validators.required],
      email: ['', [Validators.required, Validators.email, CustomValidatorsService.strictEmail()], [this.customValidators.checkEmailAvailability()]],
      emailBis: ['', [Validators.required, Validators.email, CustomValidatorsService.strictEmail()], [this.customValidators.checkEmailAvailability()]]
    }, { validators: [CustomValidatorsService.passwordsMatch(), CustomValidatorsService.emailsMatch()] });
  }


  onEmailChangeSubmit(){
    if (this.changeEmailForm.valid) {
      const { email, password } = this.changeEmailForm.value;

      this.authService.changeEmail(password, email).subscribe({
        next: () => {
          this.notificationService.showSuccess('Email successfully modified !', 3000, '/home');
          this.authService.logout().subscribe();
        },
        error: () => {
          this.changeEmailErrorMessage = "Invalid password, or email is allready taken";
        }
      })
    } else {
      this.changeEmailForm.markAllAsTouched();
    }
  }
}
