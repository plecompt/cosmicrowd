import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth-service/auth-service';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';

@Component({
  selector: 'app-register',
  imports: [BackgroundStarsComponent, ReactiveFormsModule],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {
  registerForm!: FormGroup;

  constructor(private router: Router, public authService: AuthService, private fb: FormBuilder) { 
  }

  ngOnDestroy(): void {
  }

  ngAfterViewInit(): void {
  }

  ngOnInit(): void {
    this.initRegisterForm();
  }


  initRegisterForm(){
    this.registerForm = this.fb.group({
      login: [''],
      email: [''],
      password: [''],
      passwordBis: ['']
    });
  }

  onLoginSubmit(){
    //Here we need to check form, wont do it here, because i'll have to redo it
    //let's just check inputs are not empty
    if (this.registerForm.value.email && this.registerForm.value.email.length > 0 && this.registerForm.value.password && this.registerForm.value.password.length > 0){

      this.authService.login(this.registerForm.value.email , this.registerForm.value.password).subscribe({
        next: () => {
          this.router.navigateByUrl('/home');
        },
        error: () => {
          alert("Invalid email/password");
        }
      })
    }
  }

  navigateTo(url: string) {
    this.router.navigateByUrl(url)
  }
}
