import { Routes } from '@angular/router';
import { HomeComponent } from './pages/home/home.component';
import { LoginComponent } from './pages/login/login.component';
import { ProfileComponent } from './pages/profile/profile.component';
import { SystemsComponent } from './pages/systems/systems.component';
import { ForgotPasswordComponent } from './pages/forgot-password/forgot-password.component';
import { ChangePasswordComponent } from './pages/change-password/change-password.component';
import { ChangeEmailComponent } from './pages/change-email/change-email.component';
import { SystemViewComponent } from './pages/system-view/system-view.component';
import { ResetPasswordComponent } from './pages/reset-password/reset-password.component';
import { RegisterComponent } from './pages/register/register.component';
import { DeleteAccountComponent } from './pages/delete-account/delete-account.component';
import { ContactComponent } from './pages/contact/contact.component';
import { StylizedSystemComponent } from './pages/poc/poc';

export const routes: Routes = [
    { path: '', component: HomeComponent, title: 'CosmiCrowd'},
    { path: 'home', redirectTo: '' }, // Redirect to home page
    { path: 'login', component: LoginComponent, title: 'CosmiCrowd - Login' },
    { path: 'register', component: RegisterComponent, title: 'CosmiCrowd - Register' },
    { path: 'forgot-password', component: ForgotPasswordComponent, title: 'CosmiCrowd - Recover Password' },
    { path: 'delete-account', component: DeleteAccountComponent, title: 'CosmiCrowd - Delete Account' },
    { path: 'change-password', component: ChangePasswordComponent, title: 'CosmiCrowd - Change Password' },
    { path: 'change-email', component: ChangeEmailComponent, title: 'CosmiCrowd - Change Email' },
    { path: 'contact', component: ContactComponent, title: 'CosmiCrowd - Contact' },
    { path: 'reset-password', component: ResetPasswordComponent, title: 'CosmiCrowd - Reset Password' },
    { path: 'profile', component: ProfileComponent, title: 'CosmiCrowd - Profile' },
    { path: 'systems', component: SystemsComponent, title: 'CosmiCrowd - Systems' },
    { path: 'system-view', component: SystemViewComponent, title: 'CosmiCrowd - System' },
    { path: 'poc', component: StylizedSystemComponent, title: 'CosmiCrowd - System' },
    { path: '**', redirectTo: '' }
];
