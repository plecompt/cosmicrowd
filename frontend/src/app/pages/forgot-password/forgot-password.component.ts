import { Component } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth/auth.service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';

@Component({
  selector: 'app-forgot-password',
  imports: [ReactiveFormsModule, BackgroundStarsComponent],
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.css', '../../shared/styles/form.template.css']
})
export class ForgotPasswordComponent {
  forgotPasswordForm!: any;

  constructor(private fb: FormBuilder, public authService: AuthService){}

  ngAfterViewInit(): void {
  }

  ngOnInit(): void {
    this.initForgotPasswordForm();
  }

  //init the form with validators
  initForgotPasswordForm() {
    this.forgotPasswordForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });
  }

  //user submit the password recovery form
  onForgotPasswordSubmit(){
    if (this.forgotPasswordForm.valid) {
    const { email } = this.forgotPasswordForm.value;

      this.authService.forgotPassword(email).subscribe({
        next: () => {
          //succesfully sended email
          //some feedback in the view
        },
        error: () => {
          //error, incorrect old password or missmatch
          alert('Something went wrong...');
        }
      })
    }
  }

}
