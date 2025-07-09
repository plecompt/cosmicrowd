import { Injectable } from "@angular/core";
import { FormGroup } from "@angular/forms";

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
    stringMatch: 'You didn\'t type the same word',
    loginNotAvailable: 'This login is not available',
    emailNotAvailable: 'This email is not available',
    passwordMatch: 'Passwords do not match',
    newPasswordMatch: 'New passwords do not match',
    emailMatch: 'Emails do not match',
  };

  // Check if field is valid
  isValid(form: FormGroup, fieldName: string): boolean {
    const control = form.get(fieldName);
    return !!(control && control.valid && control.value.length > 0);
  }

  // Check if field is invalid
  isInvalid(form: FormGroup, fieldName: string): boolean {
    const control = form.get(fieldName);
    return !!(control && control.invalid && control.value.length > 0);
  }

  // Get error message for field
  getErrorMessage(form: FormGroup, fieldName: string): string {
    const control = form.get(fieldName);
    if (!control || !control.errors) return '';

    const firstErrorKey = Object.keys(control.errors)[0];
    return this.errorMessages[firstErrorKey] || `${fieldName} is invalid`;
  }

  // Get error message for form-level errors (like passwordMatch, emailMatch)
  getFormErrorMessage(form: FormGroup, errorKey: string): string {
    if (!form.hasError(errorKey)) return '';
    return this.errorMessages[errorKey] || `Form validation error: ${errorKey}`;
  }

  // Check if form can be submitted
  canSubmit(form: FormGroup): boolean {
    return form.valid;
  }
}