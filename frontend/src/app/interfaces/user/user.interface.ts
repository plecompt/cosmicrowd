import { UserRole } from '../../enums/user-roles.enum';

export interface User {
  user_id: number;
  user_login: string;
  user_password?: string; // For security
  user_email: string;
  user_active: boolean;
  user_role: UserRole;
  user_last_login?: Date;
  user_date_inscription: Date;
}

// Interface for display
export interface UserSimple {
  user_id: number;
  user_login: string;
  user_role: UserRole;
}