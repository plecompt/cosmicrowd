import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

export interface ModalData {
  title: string;
  content: string;
  showCancel?: boolean; //optionnal
  onConfirm?: () => void; //optionnal
  onCancel?: () => void; //optionnal
}

@Injectable({
  providedIn: 'root'
})
export class ModalService {
  private modalSubject = new BehaviorSubject<ModalData | null>(null);
  modal$ = this.modalSubject.asObservable();

  show(data: ModalData): void {
    this.modalSubject.next(data);
  }

  close(): void {
    this.modalSubject.next(null);
  }
}
