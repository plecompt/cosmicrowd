import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { BehaviorSubject, Observable } from 'rxjs';

export interface Notification {
  message: string;
  type: 'success' | 'error' | 'info';
  duration?: number;
  redirectTo?: string;
}

@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  private notificationSubject = new BehaviorSubject<Notification | null>(null);
  public notification$ = this.notificationSubject.asObservable();

  constructor(private router: Router) {}

  showSuccess(message: string, duration: number = 3000, redirectTo?: string): void {
    this.showNotification({ message, type: 'success', duration, redirectTo });
  }

  showError(message: string, duration: number = 5000, redirectTo?: string): void {
    this.showNotification({ message, type: 'error', duration, redirectTo });
  }

  private showNotification(notification: Notification): void {
    this.notificationSubject.next(notification);
    
    setTimeout(() => {
      this.notificationSubject.next(null);
      
      if (notification.redirectTo) {
        this.router.navigateByUrl(notification.redirectTo);
      }
    }, notification.duration);
  }
}