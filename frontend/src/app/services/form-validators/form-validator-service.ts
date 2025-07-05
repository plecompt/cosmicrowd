import { Injectable } from '@angular/core';
import { FormGroup } from '@angular/forms';

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

  // Check if a specific field has validation errors and has been touched
  hasError(form: FormGroup, fieldName: string, submitted: boolean = false): boolean {
    const control = form.get(fieldName);
        console.log(`Fieldname: ${fieldName}, submitted: ${submitted}, control: ${control}`);
        console.log(control);
    return !!(control && control.invalid && submitted);
  }

  // Check if a field is valid
  isFieldValid(form: FormGroup, fieldName: string): boolean {
    const control = form.get(fieldName);
    return !!(control && control.valid && control.value && control.value.length > 0);
  }

  // Get the first error message for a specific field
  getErrorMessage(form: FormGroup, fieldName: string): string {
    const control = form.get(fieldName);
    
    console.log(`Field: ${fieldName}`);

    if (!control || !control.errors) return '';

    const firstErrorKey = Object.keys(control.errors)[0];
    return this.errorMessages[firstErrorKey] || `${fieldName} is invalid`;
  }

  // Check if the form can be submitted (all fields valid)
  canSubmit(form: FormGroup): boolean {

    console.log(form);
    return form.valid;
  }
}
