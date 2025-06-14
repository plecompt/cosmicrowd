import { UserRole } from '../../enums/user-roles.enum';

export class User {
        user_id: number = -1;
        user_login: string = '';
        user_password: string = '';
        user_email: string = '';
        user_active: boolean = true;
        user_role: UserRole = UserRole.MEMBER;
        user_last_login?: Date;
        user_date_inscription: Date = new Date;
}