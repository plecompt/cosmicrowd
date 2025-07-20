import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { AuthService } from '../auth/auth.service';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const authService = inject(AuthService);
  const token = localStorage.getItem('token');

  const authReq = token ? req.clone({ headers: req.headers.set('Authorization', `Bearer ${token}`) }) : req;

  return next(authReq).pipe(
    catchError((error: HttpErrorResponse) => {
      // Handle 401 errors
      if (error.status === 401) {
        authService.logout();
      }

      // Clean API errors for easier handling
      if (error.error && error.error.message) {
        const cleanError = {
          success: error.error.success || false,
          message: error.error.message,
          errors: error.error.errors || null,
          statusCode: error.status
        };
        return throwError(() => cleanError);
      }

      return throwError(() => error);
    })
  );
};

