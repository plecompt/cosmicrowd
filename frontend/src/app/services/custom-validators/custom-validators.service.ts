import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { AbstractControl, ValidationErrors, ValidatorFn, FormGroup, AsyncValidatorFn } from '@angular/forms';
import { catchError, debounceTime, map, Observable, of } from 'rxjs';


@Injectable({
  providedIn: 'root'
})
export class CustomValidatorsService {

  constructor(private http: HttpClient) { }

  // Strong password validator
  static strongPassword(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value;
      if (!value) return null;

      const hasMinLength = value.length >= 12;
      const hasUpperCase = /[A-Z]/.test(value);
      const hasLowerCase = /[a-z]/.test(value);
      const hasNumber = /[0-9]/.test(value);
      const hasSpecialChar = /[^A-Za-z0-9]/.test(value);

      const isValid = hasMinLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar;

      if (!isValid) {
        return {
          weakPassword: {
            hasMinLength,
            hasUpperCase,
            hasLowerCase,
            hasNumber,
            hasSpecialChar
          }
        };
      }

      return null;
    };
  }

  // Match passwords validator
static passwordsMatch(passwordField: string = 'password', confirmPasswordField: string = 'passwordBis'): ValidatorFn {
  return (group: AbstractControl): ValidationErrors | null => {
    if (!(group instanceof FormGroup)) return null;

    const password = group.get(passwordField);
    const confirmPassword = group.get(confirmPasswordField);

    if (!password || !confirmPassword) return null;

    return password.value === confirmPassword.value ? null : { passwordMatch: { expected: password.value, actual: confirmPassword.value } };
  };
}

  // Username validator
  static username(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value;
      if (!value) return null;

      const hasValidLength = value.length >= 3 && value.length <= 20;
      const hasValidChars = /^[a-zA-Z0-9_-]+$/.test(value);
      const startsWithLetter = /^[a-zA-Z]/.test(value);

      if (!hasValidLength || !hasValidChars || !startsWithLetter) {
        return {
          invalidUsername: {
            hasValidLength,
            hasValidChars,
            startsWithLetter
          }
        };
      }

      return null;
    };
  }

  // Custom email validator
  static strictEmail(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value;
      if (!value) return null;

      const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
      const isValid = emailRegex.test(value);

      return isValid ? null : { invalidEmail: true };
    };
  }

  // No spaces validator
  static noSpaces(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value;
      if (!value) return null;

      const hasSpaces = /\s/.test(value);
      return hasSpaces ? { hasSpaces: true } : null;
    };
  }

  // Star system name validator
  static starSystemName(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value;
      if (!value) return null;

      const hasValidLength = value.length >= 2 && value.length <= 50;
      const hasValidChars = /^[a-zA-Z0-9\s\-']+$/.test(value);
      const notOnlySpaces = value.trim().length > 0;

      if (!hasValidLength || !hasValidChars || !notOnlySpaces) {
        return {
          invalidStarSystemName: {
            hasValidLength,
            hasValidChars,
            notOnlySpaces
          }
        };
      }

      return null;
    };
  }

  // Check if login is available
  checkLoginAvailability(): AsyncValidatorFn {
    return (control: AbstractControl): Observable<ValidationErrors | null> => {
      // Return null if no value to validate
      if (!control.value) return of(null);

        console.log('hereLOgin');
      // Call API to check login availability
      return this.http.post<{available: boolean}>('http://localhost:8000/api/v1/auth/check-login', { login: control.value })
        .pipe(
          // Debounce to avoid too many API calls
          debounceTime(200),
          // Return error if login is taken, null if available
          map(response => response.available ? null : { loginTaken: true }),
          // Return null on API error
          catchError(() => of(null))
        );
    };
  }

  // Check if email is available
  checkEmailAvailability(): AsyncValidatorFn {
    return (control: AbstractControl): Observable<ValidationErrors | null> => {
      // Return null if no value to validate
      if (!control.value) return of(null);

      // Call API to check email availability
      return this.http.post<{available: boolean}>('http://localhost:8000/api/v1/auth/check-email', { email: control.value })
        .pipe(
          // Debounce to avoid too many API calls
          debounceTime(200),
          // Return error if email is taken, null if available
          map(response => response.available ? null : { emailTaken: true }),
          // Return null on API error
          catchError(() => of(null))
        );
    };
  }
}
