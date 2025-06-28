import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ValidationError } from '../../services/form-validators/form-validator-service';

@Component({
  selector: 'app-form-errors',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="errors-container" *ngIf="errors.length > 0">
      <div 
        class="error-message" 
        *ngFor="let error of errors"
        [class]="'error-' + error.type">
        <i class="error-icon">⚠️</i>
        {{ error.message }}
      </div>
    </div>
  `,
  styles: [`
    .errors-container {
      margin: 15px 0;
    }
    
    .error-message {
      display: flex;
      align-items: center;
      padding: 8px 12px;
      margin: 5px 0;
      background: rgba(255, 0, 0, 0.1);
      border: 1px solid rgba(255, 0, 0, 0.3);
      border-radius: 4px;
      color: #d32f2f;
      font-size: 14px;
    }
    
    .error-icon {
      margin-right: 8px;
    }
    
    .error-form {
      border-color: rgba(255, 165, 0, 0.5);
      background: rgba(255, 165, 0, 0.1);
      color: #f57c00;
    }
  `]
})
export class FormErrorsComponent {
  @Input() errors: ValidationError[] = [];
}