import { AfterViewInit, Component, OnDestroy, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth/auth.service';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { FormValidatorService } from '../../services/form-validators/form-validator-service';
import { NotificationService } from '../../services/notifications/notification.service';
import { User } from '../../models/user/user.model';
import { TitleCasePipe } from '@angular/common';
import { CustomValidatorsService } from '../../services/custom-validators/custom-validators.service';
import { NavigationService } from '../../services/navigation/navigation.service';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule, BackgroundStarsComponent],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css', '../../shared/styles/form.template.css']
})
export class LoginComponent implements OnInit, AfterViewInit, OnDestroy {
  loginForm!: FormGroup;
  loginError: String = "";
  user!: User;
  private titleCasePipe = new TitleCasePipe();


  constructor(
    private router: Router, 
    public authService: AuthService, 
    private fb: FormBuilder, 
    public formValidator: FormValidatorService,
    private notificationService: NotificationService,
    private customFormValidator: CustomValidatorsService,
    public navigationService: NavigationService
  ) { }

  ngOnDestroy(): void {}

  ngAfterViewInit(): void {}

  ngOnInit(): void {
    // If user is allready logged in
    if (this.authService.isLoggedIn()) {
        this.notificationService.showError('You can\'t access this page', 3000, '/home');
        return;
    }
    this.initLoginForm();
  }

  initLoginForm(){
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email, CustomValidatorsService.strictEmail()]],
      password: ['', [Validators.required, Validators.minLength(6)]]
    });
  }

  onLoginSubmit(){
    if (this.loginForm.valid) {
      this.authService.login(this.loginForm.value.email, this.loginForm.value.password).subscribe({
        next: () => {
          this.authService.me().subscribe({
            next: (response: any) => {
              this.user = response.data.user;
              const capitalizedLogin = this.titleCasePipe.transform(this.user.user_login);
              this.notificationService.showSuccess(`Welcome back, ${capitalizedLogin} !`, 1500, '/home');
            }
          });
        },
        error: () => {
          this.loginError = 'Invalid email/password combination';
        }
      });
    } else {
      this.loginForm.markAllAsTouched();
    }
  }
}
