import { Component, OnInit, AfterViewInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { AuthService } from '../../services/auth/auth.service';
import { FormValidatorService } from '../../services/form-validators/form-validator-service';
import { CustomValidatorsService } from '../../services/custom-validators/custom-validators.service';
import { NotificationService } from '../../services/notifications/notification.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-contact',
  imports: [BackgroundStarsComponent, ReactiveFormsModule],
  templateUrl: './contact.component.html',
  styleUrls: ['./contact.component.css', '../../shared/styles/form.template.css']
})
export class ContactComponent implements OnInit, AfterViewInit {
  contactForm!: FormGroup;
  contactError: string = "";

  constructor(
    private fb: FormBuilder, 
    public authService: AuthService, 
    public formValidator: FormValidatorService,
    private notificationService: NotificationService
  ){}

  ngAfterViewInit(): void {}

  ngOnInit(): void {
    this.initContactForm();
  }

  initContactForm() {
    this.contactForm = this.fb.group({
      email: ['', [Validators.required, Validators.email, CustomValidatorsService.strictEmail()]],
      message: ['', [Validators.required, Validators.minLength(10)]],
      subject: [''],
      login: ['']
    });
  }

  onContactFormSubmit(){
    if (this.contactForm.valid) {
      const { email, message, subject, login } = this.contactForm.value;

      this.authService.contact(email, message, login, subject).subscribe({
        next: () => {
          this.notificationService.showSuccess('Message sent successfully!');
          this.authService.navigateTo('/home');
        },
        error: () => {
          this.contactError = 'Something went wrong. Please, try again later';
        }
      });
    } else {
      // Mark all fields as touched to show validation errors
      this.contactForm.markAllAsTouched();
    }
  }
}

