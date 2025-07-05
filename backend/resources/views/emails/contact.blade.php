<!DOCTYPE html>
<html>
<head>
    <title>New Contact - CosmiCrowd</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #121212; color: #f0f0f0; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 40px auto; padding: 30px; background-color: #1e1e1e; border-radius: 8px;">
        <h2 style="color: #61dafb;">New Contact Message</h2>

        <div style="background-color: #2a2a2a; padding: 20px; border-radius: 6px;">
            <p><strong>Name:</strong> {{ $user_name }}</p>
            <p><strong>Email:</strong> {{ $user_email }}</p>
            <p><strong>IP Address:</strong> {{ $user_ip }}</p>
            <p><strong>Date:</strong> {{ $sent_at->format('m/d/Y H:i:s') }}</p>
            @if(isset($subject))
                <p><strong>Subject:</strong> {{ $subject }}</p>
            @endif
        </div>

        <div style="margin-top: 20px; background-color: #2f2f2f; padding: 20px; border-radius: 6px;">
            <h3 style="margin-top: 0;">Message:</h3>
            <p style="white-space: pre-line;">{{ $user_message }}</p>
        </div>

        <div style="margin-top: 20px;">
            <p>Reply at: <a href="mailto:{{ $user_email }}" style="color: #61dafb;">{{ $user_email }}</a></p>
        </div>

        <p style="font-size: 12px; color: #888; margin-top: 40px;">
            Message from the CosmiCrowd contact form.<br>
        </p>
    </div>
</body>
</html>
