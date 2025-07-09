import { Component, OnInit } from '@angular/core';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { AuthService } from '../../services/auth/auth.service';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { FormValidatorService } from '../../services/form-validators/form-validator-service';
import { CustomValidatorsService } from '../../services/custom-validators/custom-validators.service';
import { ModalService } from '../../services/modal/modal.service';
import { NotificationService } from '../../services/notifications/notification.service';

@Component({
  selector: 'app-delete-account',
  imports: [BackgroundStarsComponent, ReactiveFormsModule],
  templateUrl: './delete-account.component.html',
  styleUrls: ['./delete-account.component.css', '../../shared/styles/form.template.css']
})
export class DeleteAccountComponent implements OnInit {
  deleteAccountForm!: FormGroup;
  errorMessage: string = "";

  constructor(
    private modalService: ModalService,
    public authService: AuthService, 
    private notificationService: NotificationService,
    private fb: FormBuilder, 
    public formValidator: FormValidatorService,
    public customValidators: CustomValidatorsService
  ) {}

  ngOnInit(): void {
    // If user is not logged in
    if (!this.authService.isLoggedIn()) {
        this.notificationService.showError('You can\'t access this page', 3000, '/home');
        return;
    }
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
    
    if (!this.formValidator.canSubmit(this.deleteAccountForm)) {
      return;
    }
    this.showModal();
  }


  private showModal(): void {
    this.modalService.show({
      title: 'Delete Account Forever',
      content: 'This action cannot be undone. Your account and all associated data will be permanently deleted. Your claimed solar systems will become available for other explorers.',
      showCancel: true,
      onConfirm: () => {
        this.authService.deleteAccount(
          this.deleteAccountForm.value.currentPassword
        ).subscribe({
          next: () => {
            this.authService.clearSession();
            this.notificationService.showSuccess('Account successfully deleted !', 3000, '/home');
          },
          error: () => {
            this.errorMessage = 'Wrong password';
            this.deleteAccountForm.markAllAsTouched();
          }
        });
      },
      onCancel: () => {
        return;
      }
    });
  }

}
