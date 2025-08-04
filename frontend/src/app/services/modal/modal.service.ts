import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

export interface ModalData {
  title: string;
  content: string;
  showView?: boolean;
  showCancel?: boolean;
  showClaim?: boolean;
  showConfirm?: boolean;
  showLike?: boolean;
  showUnlike?: boolean;
  onView?: () => void;
  onCancel?: () => void;
  onClaim?: () => void;
  onConfirm?: () => void;
  onLike?: () => void;
  onUnlike?: () => void;
}

@Injectable({
  providedIn: 'root'
})
export class ModalService {
  private modalSubject = new Subject<ModalData | null>();
  modal = this.modalSubject.asObservable(); //parent component can only subscribe, cant modify.

  show(data: ModalData): void {
    this.modalSubject.next(data);
  }

  close(): void {
    this.modalSubject.next(null);
  }
}