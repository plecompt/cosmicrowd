import { Injectable } from '@angular/core';
import { FormGroup, AbstractControl } from '@angular/forms';

export interface ValidationError {
  field: string;
  message: string;
  type: string;
}

@Injectable({
  providedIn: 'root'
})
export class FormValidatorService {
  
  private errorMessages: { [key: string]: string } = {
    required: 'This field is required',
    email: 'Please enter a valid email address',
    invalidEmail: 'Please enter a valid email address',
    weakPassword: 'Password must be at least 12 characters with uppercase, lowercase, number and special character',
    passwordsMismatch: 'Passwords do not match',
    minlength: 'This field is too short',
    maxlength: 'This field is too long',
    invalidUsername: 'Username must be 3-20 characters, start with a letter, and contain only letters, numbers, - or _',
    hasSpaces: 'This field cannot contain spaces',
    invalidStarSystemName: 'Star system name must be 2-50 characters with valid characters',
  };

  validateForm(form: FormGroup): ValidationError[] {
    const errors: ValidationError[] = [];
    
    Object.keys(form.controls).forEach(key => {
      const control = form.get(key);
      if (control && control.invalid && (control.dirty || control.touched)) {
        const controlErrors = this.getControlErrors(key, control);
        errors.push(...controlErrors);
      }
    });

    // Form-level errors
    if (form.errors) {
      Object.keys(form.errors).forEach(errorKey => {
        errors.push({
          field: 'form',
          message: this.errorMessages[errorKey] || 'Form validation error',
          type: errorKey
        });
      });
    }

    return errors;
  }

  private getControlErrors(fieldName: string, control: AbstractControl): ValidationError[] {
    const errors: ValidationError[] = [];
    
    if (control.errors) {
      Object.keys(control.errors).forEach(errorKey => {
        errors.push({
          field: fieldName,
          message: this.errorMessages[errorKey] || `${fieldName} is invalid`,
          type: errorKey
        });
      });
    }
    
    return errors;
  }

  canSubmit(form: FormGroup): boolean {
    return form.valid;
  }
}
