import { AfterViewInit, Component, OnDestroy, OnInit } from '@angular/core';
import { AuthService } from '../../services/auth-service/auth-service';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { BackgroundStarsComponent } from '../../components/background-stars/background-stars.component';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule, BackgroundStarsComponent],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent implements OnInit, AfterViewInit, OnDestroy {
  loginForm!: FormGroup;

  constructor(private router: Router, public authService: AuthService, private fb: FormBuilder) { 
  }

  ngOnDestroy(): void {
  }

  ngAfterViewInit(): void {
  }

  ngOnInit(): void {
    this.initLoginForm();
  }


  initLoginForm(){
    this.loginForm = this.fb.group({
      email: [''],
      password: ['']
    });
  }

  onLoginSubmit(){
    //Here we need to check form, wont do it here, because i'll have to redo it
    //let's just check inputs are not empty
    if (this.loginForm.value.email && this.loginForm.value.email.length > 0 && this.loginForm.value.password && this.loginForm.value.password.length > 0){

      this.authService.login(this.loginForm.value.email , this.loginForm.value.password).subscribe({
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
