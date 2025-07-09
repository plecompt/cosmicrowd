import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

export interface Notification {
  message: string;
  type: 'success' | 'error' | 'info';
  duration?: number;
}

@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  private notificationSubject = new BehaviorSubject<Notification | null>(null);
  public notification$ = this.notificationSubject.asObservable();

  showSuccess(message: string, duration: number = 3000): void {
    this.showNotification({ message, type: 'success', duration });
  }

  showError(message: string, duration: number = 5000): void {
    this.showNotification({ message, type: 'error', duration });
  }

  private showNotification(notification: Notification): void {
    this.notificationSubject.next(notification);
    
    setTimeout(() => {
      this.notificationSubject.next(null);
    }, notification.duration);
  }
}