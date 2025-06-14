<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset Request</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="background-color: #007BFF; padding: 20px; color: #ffffff; text-align: center;">
                            <h1 style="margin: 0; font-size: 24px;">Password Reset Request</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <p style="font-size: 16px; color: #333333;">
                                Dear User,
                            </p>
                            <p style="font-size: 16px; color: #333333;">
                                You have requested to reset your password. Please click the button below to proceed:
                            </p>
                            <p style="text-align: center; margin: 30px 0;">
                                <a href="{{ env('FRONTEND_URL') }}/reset-password?token={{ $token }}" 
                                   style="background-color: #007BFF; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 4px; display: inline-block;">
                                    Reset My Password
                                </a>
                            </p>
                            <p style="font-size: 14px; color: #777777;">
                                If you did not request this password reset, you can safely ignore this email.
                            </p>
                            <p style="font-size: 16px; color: #333333;">
                                Best regards,<br>
                                <strong>The Support Team</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 12px; color: #777777;">
                            © {{ date('Y') }} CosmiCrowd. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
