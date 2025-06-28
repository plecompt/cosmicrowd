import { AfterViewInit, Component, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth/auth.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';

@Component({
  selector: 'app-change-email',
  imports: [ReactiveFormsModule, BackgroundStarsComponent],
  templateUrl: './change-email.component.html',
  styleUrl: './change-email.component.css'
})
export class ChangeEmailComponent implements OnInit, AfterViewInit {
  changeEmailForm!: any;

  constructor(private fb: FormBuilder, public authService: AuthService){}

  ngAfterViewInit(): void {
  }

  ngOnInit(): void {
    this.initEmailForm();
  }

  //init the form with validators
  initEmailForm() {
    this.changeEmailForm = this.fb.group({
      currentPassword: ['', Validators.required],
      currentPasswordBis: ['', Validators.required],
      newEmail: ['', [Validators.required, Validators.email]],
      newEmailBis: ['', [Validators.required, Validators.email]]
    }, { validators: [this.passwordsMatchValidator, this.emailsMatchValidator] });
  }

  //custom math validators
  private passwordsMatchValidator(group: any) {
    const pass = group.get('currentPassword')?.value;
    const passBis = group.get('currentPasswordBis')?.value;
    return pass === passBis ? null : { passwordsMismatch: true };
  }
  //same for mails
  private emailsMatchValidator(group: any) {
    const email = group.get('newEmail')?.value;
    const emailBis = group.get('newEmailBis')?.value;
    return email === emailBis ? null : { emailsMismatch: true };
  }

  onEmailChangeSubmit(){
    if (this.changeEmailForm.valid) {
      const { currentPassword, newEmail } = this.changeEmailForm.value;

      this.authService.changeEmail(currentPassword, newEmail).subscribe({
        next: () => {
          //succesfully changed email, logout
          this.authService.logout().subscribe();
        },
        error: () => {
          //error, incorrect password or email
          alert('Invalid email or password');
        }
      })
    }
  }
}
