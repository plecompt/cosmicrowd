import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';

import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { FormValidatorService, ValidationError } from '../../services/form-validators/form-validator-service';
import { FormErrorsComponent } from '../../components/form-error/form-error.component';
import { CustomValidatorsService } from '../../services/custom-validators/custom-validators.service';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-register',
  imports: [BackgroundStarsComponent, ReactiveFormsModule, FormErrorsComponent],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {
  registerForm!: FormGroup;
  formErrors: ValidationError[] = [];

  constructor(private router: Router, public authService: AuthService, private fb: FormBuilder, private formValidator: FormValidatorService, private customValidators: CustomValidatorsService) {}

  ngOnInit(): void {
    this.initRegisterForm();
  }

  initRegisterForm() {
    this.registerForm = this.fb.group({
      login: ['', [
        Validators.required,
        CustomValidatorsService.username(),
        CustomValidatorsService.noSpaces(),
        // this.customValidators.checkLoginAvailability()
      ]],
      email: ['', [
        Validators.required,
        CustomValidatorsService.strictEmail(),
        // this.customValidators.checkEmailAvailability()
      ]],
      password: ['', [
        Validators.required,
        CustomValidatorsService.strongPassword(),
        CustomValidatorsService.noSpaces()
      ]],
      passwordBis: ['', [
        Validators.required,
        CustomValidatorsService.strongPassword(),
        CustomValidatorsService.noSpaces()
      ]]
    }, { 
      validators: [CustomValidatorsService.passwordsMatch('password', 'passwordBis')] 
    });
  }

  onRegisterSubmit() {

    this.formErrors = this.formValidator.validateForm(this.registerForm);
    
    if (!this.formValidator.canSubmit(this.registerForm)) {
      return;
    }

    this.authService.register(
      this.registerForm.value.login, 
      this.registerForm.value.password, 
      this.registerForm.value.email
    ).subscribe({
      next: () => {
        this.router.navigateByUrl('/home');
      },
      error: () => {
        this.formErrors = [{
          field: 'form',
          message: 'Registration failed. Please try again.',
          type: 'server'
        }];
      }
    });
  }

  navigateTo(url: string) {
    this.router.navigateByUrl(url);
  }
}
