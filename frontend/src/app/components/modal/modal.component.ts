import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModalService, ModalData } from '../../services/modal/modal.service';
import { ReactiveFormsModule } from '@angular/forms';

@Component({
  selector: 'app-modal',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './modal.component.html',
  styleUrls: ['./modal.component.css']
})
export class ModalComponent implements OnInit {
  modalData: ModalData | null = null;

  constructor(private modalService: ModalService) {}

  ngOnInit(): void {
    this.modalService.modal$.subscribe(data => {
      this.modalData = data;
    });
  }

  onConfirm(): void {
    this.modalData?.onConfirm?.();
    this.modalService.close();
  }

  onCancel(): void {
    this.modalData?.onCancel?.();
    this.modalService.close();
  }

  onClaim(): void {
    this.modalData?.onClaim?.();
    this.modalService.close();
  }
}
