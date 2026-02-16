<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Technofra</title>
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
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-text {
            font-size: 18px;
            margin-bottom: 25px;
        }
        .credentials-box {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
        }
        .credentials-box h3 {
            margin-top: 0;
            color: #007bff;
            font-size: 18px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .credential-item {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }
        .credential-label {
            font-weight: bold;
            color: #555;
            width: 100px;
            flex-shrink: 0;
        }
        .credential-value {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
            flex-grow: 1;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        .button:hover {
            background: linear-gradient(135deg, #0056b3 0%, #003d82 100%);
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 0 5px 5px 0;
        }
        .warning-box h4 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .warning-box p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
        .steps {
            background-color: #e7f3ff;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .steps h4 {
            margin-top: 0;
            color: #0056b3;
        }
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .steps li {
            margin-bottom: 8px;
            color: #333;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #666;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to Technofra!</h1>
            <p>Your account has been created successfully</p>
        </div>
        
        <div class="content">
            <p class="welcome-text">Dear <strong>{{ $staffName }}</strong>,</p>
            
            <p>Welcome to the Technofra team! We're excited to have you on board. Your staff account has been created and you can now access the dashboard to manage your tasks and projects.</p>
            
            <div class="credentials-box">
                <h3>🔐 Your Login Credentials</h3>
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">{{ $email }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
            </div>
            
            
            
            <div class="button-container">
                <a href="{{ $loginUrl }}" class="button">🚀 Login to Dashboard</a>
            </div>
            
           
            <p>If you have any questions or need assistance, please don't hesitate to reach out to your team lead or the administration department.</p>
            
            <p>Best regards,<br>
            <strong>The Technofra Team</strong></p>
        </div>
        
        <div class="footer">
           
            <p>&copy; {{ date('Y') }} Technofra. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
