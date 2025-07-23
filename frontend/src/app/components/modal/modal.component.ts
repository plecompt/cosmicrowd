import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ModalService, ModalData } from '../../services/modal/modal.service';
import { ReactiveFormsModule } from '@angular/forms';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';

@Component({
  selector: 'app-modal',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './modal.component.html',
  styleUrls: ['./modal.component.css']
})
export class ModalComponent implements OnInit {
  modalData: ModalData | null = null;
  private cachedContent: SafeHtml = '';

  constructor(
    private modalService: ModalService,
    private sanitizer: DomSanitizer
  ) {}

  ngOnInit(): void {
    this.modalService.modal$.subscribe(data => {
      this.modalData = data;
      this.cachedContent = this.modalData?.content ? this.sanitizer.bypassSecurityTrustHtml(this.modalData.content) : '';
    });
  }

  getSanitizedContent(): SafeHtml {
    return this.cachedContent;
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

  onView(): void {
    this.modalData?.onView?.();
    this.modalService.close();
  }
}
