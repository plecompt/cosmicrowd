<!DOCTYPE html>
<html>
<head>
    <title>Message Received - CosmiCrowd</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #4a90e2; margin: 0;">🌌 CosmiCrowd</h1>
            <p style="color: #666; margin: 5px 0;">Your Collaborative Galaxy</p>
        </div>
        
        <h2 style="color: #4a90e2;">Message Received Successfully!</h2>
        
        <p>Hello {{ $user_name }},</p>
        
        <p>Thank you for contacting CosmiCrowd! We have successfully received your message and appreciate you reaching out to us.</p>
        
        <!-- Message Summary -->
        <div style="background: #f0f8ff; padding: 20px; border-left: 4px solid #4a90e2; margin: 25px 0; border-radius: 0 5px 5px 0;">
            <h3 style="margin-top: 0; color: #4a90e2;">Your Message:</h3>
            @if(isset($subject))
            <p><strong>Subject:</strong> {{ $subject }}</p>
            @endif
            <div style="background: white; padding: 15px; border-radius: 3px; margin-top: 10px;">
                <p style="white-space: pre-line; font-style: italic; margin: 0;">{{ $user_message }}</p>
            </div>
        </div>
        
        <!-- Response Info -->
        <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0;"><strong>📧 What happens next?</strong></p>
            <p style="margin: 5px 0 0 0;">Our team will respond to your message at <strong>{{ $user_email }}</strong> as soon as possible.</p>
        </div>
        
        <!-- CTA Section -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px; margin: 25px 0; text-align: center;">
            <h3 style="color: white; margin: 0 0 10px 0;">🚀 Explore the Galaxy!</h3>
            <p style="color: #f0f0f0; margin: 0 0 15px 0;">While you wait, discover amazing solar systems created by our community!</p>
            <a href="{{ config('app.frontend_url', 'http://localhost:4200') }}" 
               style="background: white; color: #667eea; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block;">
               Visit CosmiCrowd 🌟
            </a>
        </div>
        
        <!-- Footer -->
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        
        <div style="text-align: center;">
            <p style="font-size: 12px; color: #666; margin: 0;">
                This is an automated email, please do not reply directly to this message.
            </p>
            <p style="font-size: 12px; color: #666; margin: 5px 0 0 0;">
                © {{ date('Y') }} CosmiCrowd. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>