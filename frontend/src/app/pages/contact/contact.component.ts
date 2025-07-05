import { Component } from '@angular/core';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth/auth.service';

@Component({
  selector: 'app-contact',
  imports: [BackgroundStarsComponent, ReactiveFormsModule],
  templateUrl: './contact.component.html',
  styleUrls: ['./contact.component.css', '../../shared/styles/form.template.css']
})
export class ContactComponent {
  contactForm!: any;

  constructor(private fb: FormBuilder, public authService: AuthService){}

  ngAfterViewInit(): void {}

  ngOnInit(): void {
    this.initContactForm();
  }

  //init the form with validators, and we need to use strong validators
  initContactForm() {
    this.contactForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      message: ['', [Validators.required]],
      subject: [''],
      login: ['']
    });
  }

  //user submit the contact form
  onContactFormSubmit(){
    if (this.contactForm.valid) {
    const { email } = this.contactForm.value;
    const { message } = this.contactForm.value;
    const { subject } = this.contactForm.value;
    const { login } = this.contactForm.value;

      this.authService.contact(email, message, login, subject).subscribe({
        next: () => {
          //succesfully sended email
          //some feedback in the view
        },
        error: () => {
          //error, something terrible happened
          alert('Something went wrong...');
        }
      })
    }
  }
}
