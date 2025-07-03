import { Component } from '@angular/core';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { AuthService } from '../../services/auth/auth.service';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { FormValidatorService } from '../../services/form-validators/form-validator-service';
import { Router } from '@angular/router';
import { CustomValidatorsService } from '../../services/custom-validators/custom-validators.service';

@Component({
  selector: 'app-delete-account',
  imports: [BackgroundStarsComponent, ReactiveFormsModule],
  templateUrl: './delete-account.component.html',
  styleUrl: './delete-account.component.css'
})
export class DeleteAccountComponent {
  deleteAccountForm!: FormGroup;

  constructor(
    private router: Router, 
    public authService: AuthService, 
    private fb: FormBuilder, 
    private formValidator: FormValidatorService
  ) {}

  ngOnInit(): void {
    this.initDeleteAccountForm();
  }

  initDeleteAccountForm() {
    this.deleteAccountForm = this.fb.group({
      confirmationText: ['', [
        Validators.required,
        CustomValidatorsService.exactMatch('DELETE')
      ]],
      currentPassword: ['', [
        Validators.required,
        CustomValidatorsService.noSpaces()
      ]]
    });
  }

  onAccountDeletionSubmit() {
    // this.formErrors = this.formValidator.validateForm(this.deleteAccountForm);
    
    if (!this.formValidator.canSubmit(this.deleteAccountForm)) {
      return;
    }

    // Final confirmation
    if (!confirm('Are you absolutely sure? This action cannot be undone!')) {
      return;
    }

    this.authService.deleteAccount(
      this.deleteAccountForm.value.currentPassword
    ).subscribe({
      next: () => {
        this.authService.clearSession();
        this.router.navigateByUrl('/');
      },
      error: () => {
        alert('Something went wrong');
      }
    });
  }

  onCancel() {
    this.router.navigateByUrl('/profile');
  }

  navigateTo(url: string) {
    this.router.navigateByUrl(url);
  }
}
