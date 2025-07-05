<!DOCTYPE html>
<html>
<head>
    <title>Message Received - CosmiCrowd</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #121212; color: #f0f0f0; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 40px auto; padding: 30px; background-color: #1e1e1e; border-radius: 8px;">
        <h1 style="text-align: center; color: #61dafb;">CosmiCrowd</h1>
        <h2 style="color: #ffffff;">Your message has been received</h2>

        <p>Hello {{ $user_name }},</p>
        <p>Thank you for contacting CosmiCrowd. We've received your message and will get back to you shortly.</p>

        <div style="background-color: #2a2a2a; padding: 20px; border-radius: 6px; margin: 20px 0;">
            @if(isset($subject))
                <p><strong>Subject:</strong> {{ $subject }}</p>
            @endif
            <p style="white-space: pre-line;">{{ $user_message }}</p>
        </div>

        <p>We'll respond to: <strong>{{ $user_email }}</strong></p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}"
               style="background-color: #61dafb; color: #121212; padding: 12px 24px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                Visit CosmiCrowd
            </a>
        </div>

        <p style="font-size: 12px; color: #888; text-align: center; margin-top: 40px;">
            This is an automated email. Do not reply.<br>
            © {{ date('Y') }} CosmiCrowd. All rights reserved.
        </p>
    </div>
</body>
</html>
