<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar Event Reminder</title>
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
        .event-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .event-title {
            font-size: 22px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 15px;
        }
        .detail-row {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 120px;
        }
        .detail-value {
            color: #212529;
        }
        .description-box {
            background-color: #fff;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 15px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .alert-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">Technofra Renewal Master</div>
            <p>Calendar Event Reminder</p>
        </div>

        <!-- Alert Box -->
        <div class="alert-box">
            <strong>⏰ Event Reminder</strong><br>
            This is a reminder for your scheduled event
        </div>

        <!-- Event Details -->
        <div class="event-details">
            <div class="event-title">{{ $event->title }}</div>
            
            <div class="detail-row">
                <span class="detail-label">📅 Date:</span>
                <span class="detail-value">{{ $event->event_date->format('l, F d, Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">🕐 Time:</span>
                <span class="detail-value">{{ $event->event_time->format('h:i A') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">👤 Created By:</span>
                <span class="detail-value">{{ $event->creator->name ?? 'System' }}</span>
            </div>

            @if($event->description)
            <div class="description-box">
                <strong>📝 Description:</strong><br>
                <p style="margin: 10px 0 0 0;">{{ $event->description }}</p>
            </div>
            @endif
        </div>

        <!-- Call to Action -->
        <div style="text-align: center; margin: 30px 0;">
            <p>Please take note of this event and mark your calendar accordingly.</p>
            <a href="{{ url('/dashboard') }}" class="btn" style="color: #ffffff;">View Dashboard</a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Technofra Renewal Master</strong></p>
            <p>This is an automated calendar event notification.</p>
            <p>If you have any questions, please contact our support team.</p>
            <p style="margin-top: 20px;">
                <small>© {{ date('Y') }} Technofra. All rights reserved.</small>
            </p>
        </div>
    </div>
</body>
</html>

