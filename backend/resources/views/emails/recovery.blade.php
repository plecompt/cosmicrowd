<!DOCTYPE html>
<html>
<head>
    <title>Password Reset - CosmiCrowd</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #121212; color: #f0f0f0; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 40px auto; background-color: #1e1e1e; border-radius: 8px; padding: 30px;">
        <h2 style="text-align: center; color: #61dafb;">Password Reset Request</h2>

        <p>Hello,</p>
        <p>You requested to reset your password. Click the button below to continue:</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}/reset-password?token={{ $token }}"
               style="background-color: #61dafb; color: #121212; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                Reset Password
            </a>
        </div>

        <p>If you didn't request this, you can safely ignore this email.</p>

        <p style="margin-top: 30px;">Best regards,<br><strong>CosmiCrowd Support</strong></p>

        <p style="font-size: 12px; color: #888; text-align: center; margin-top: 40px;">
            © {{ date('Y') }} CosmiCrowd. All rights reserved.
        </p>
    </div>
</body>
</html>
