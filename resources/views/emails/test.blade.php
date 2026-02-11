<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .content {
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .success-box {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">{{ $companyName }}</div>
            <p>Test Email</p>
        </div>

        <!-- Predefined Header -->
        @if($predefinedHeader)
        <div class="content">
            {!! $predefinedHeader !!}
        </div>
        @endif

        <!-- Success Message -->
        <div class="success-box">
            <h2 style="margin: 0;">✓ Test Email Successful</h2>
            <p style="margin: 10px 0 0 0;">Your email settings are configured correctly!</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p>This is a test email to verify that your SMTP settings are working properly.</p>
            <p><strong>Sent from:</strong> {{ $companyName }}</p>
            <p><strong>Date:</strong> {{ date('d M Y, h:i A') }}</p>
        </div>

        <!-- Email Signature -->
        @if($emailSignature)
        <div class="content">
            <strong>Email Signature:</strong><br>
            {!! $emailSignature !!}
        </div>
        @endif

        <!-- Predefined Footer -->
        @if($predefinedFooter)
        <div class="content">
            {!! $predefinedFooter !!}
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $companyName }}</strong></p>
            <p>This is an automated test email.</p>
            <p style="margin-top: 20px;">
                <small>© {{ date('Y') }} {{ $companyName }}. All rights reserved.</small>
            </p>
        </div>
    </div>
</body>
</html>
