import { Routes } from '@angular/router';
import { HomeComponent } from './pages/home/home.component';
import { LoginComponent } from './pages/login/login.component';

export const routes: Routes = [
    { path: '', component: HomeComponent, title: 'CosmiCrowd'},
    { path: 'home', redirectTo: '' }, // Redirect vers la route principale
    { path: 'login', component: LoginComponent, title: 'CosmiCrowd - Login'  },
    // { path: 'profile', component: ProfileComponent, title: 'CosmiCrowd - Profile' },
    { path: '**', redirectTo: '' } // Toujours en dernier
];
