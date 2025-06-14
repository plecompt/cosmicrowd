import { Component } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-systems',
  imports: [],
  templateUrl: './systems.component.html',
  styleUrl: './systems.component.css'
})
export class SystemsComponent {

  constructor(private router: Router){}


  navigateTo(url: string) {
    this.router.navigateByUrl(url)
  }

}
