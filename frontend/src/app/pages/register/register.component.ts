import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';

import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { FormValidatorService } from '../../services/form-validators/form-validator-service';
import { CustomValidatorsService } from '../../services/custom-validators/custom-validators.service';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-register',
  imports: [BackgroundStarsComponent, ReactiveFormsModule],
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css', '../../shared/styles/form.template.css']
})
export class RegisterComponent implements OnInit{
  registerForm!: FormGroup;
  submitted: boolean = false;

  constructor(
    private router: Router, 
    public authService: AuthService, 
    private fb: FormBuilder, 
    public formValidator: FormValidatorService, 
    private customValidators: CustomValidatorsService
  ) {}

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
    // Check if form is valid and set submitted to true, so we enable real-time feedback on input
    if (!this.formValidator.canSubmit(this.registerForm)) {
      this.submitted = true;
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
        alert('Registration failed. Please try again.');
      }
    });
  }

  navigateTo(url: string) {
    this.router.navigateByUrl(url);
  }
}
