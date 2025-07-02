<!DOCTYPE html>
<html>
<head>
    <title>New Contact Message - CosmiCrowd</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4a90e2;">🌌 New Contact Message</h2>
        
        <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Name:</strong> {{ $user_name }}</p>
            <p><strong>Email:</strong> {{ $user_email }}</p>
            <p><strong>IP Address:</strong> {{ $user_ip }}</p>
            <p><strong>Date:</strong> {{ $sent_at->format('m/d/Y H:i:s') }}</p>
            @if(isset($subject))
            <p><strong>Subject:</strong> {{ $subject }}</p>
            @endif
        </div>
        
        <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3>Message:</h3>
            <p style="white-space: pre-line;">{{ $user_message }}</p>
        </div>
        
        <div style="background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>🚀 Quick Actions:</strong></p>
            <p>Reply directly to: <a href="mailto:{{ $user_email }}">{{ $user_email }}</a></p>
        </div>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        
        <p style="margin-top: 20px; font-size: 12px; color: #666;">
            This message was sent from the CosmiCrowd contact form.<br>
            CosmiCrowd Admin Panel
        </p>
    </div>
</body>
</html>