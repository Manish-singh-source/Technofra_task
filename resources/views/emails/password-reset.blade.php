<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 25px;
            text-align: center;
            border-radius: 6px 6px 0 0;
            margin: 0 -20px 20px -20px;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 6px 6px;
            border: 1px solid #e9ecef;
        }
        .button {
            display: inline-block;
            background-color: #28a745;
            color: white !important;
            padding: 14px 40px;
            text-decoration: none !important;
            border-radius: 6px;
            margin: 25px 0;
            font-weight: bold;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .button:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .button:active {
            transform: translateY(0);
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 14px;
        }
        .highlight {
            background-color: #e9ecef;
            padding: 12px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
            display: inline-block;
            margin: 10px 0;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>Password Reset Request</h2>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        
        <p>We received a request to reset your password for your {{ $companyName }} account. If you made this request, please click the button below to reset your password:</p>
        
        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Reset Your Password</a>
        </div>
        
        <p>If the button above doesn't work, you can copy and paste the following link into your browser:</p>
        <div class="highlight">
            {{ $resetUrl }}
        </div>
        
        <div class="warning">
            <strong>Important:</strong> This password reset link will expire in 24 hours for security reasons. If you don't reset your password within this time, you'll need to request a new reset link.
        </div>
        
        <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
        
        <p>For security reasons, please don't share this email or the reset link with anyone.</p>
        
        <p>Best regards,<br>
        The {{ $companyName }} Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>If you're having trouble with the reset link, please contact our support team.</p>
    </div>
</body>
</html>
