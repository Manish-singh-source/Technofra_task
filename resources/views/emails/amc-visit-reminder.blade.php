<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMC Maintenance Visit Reminders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
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
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .section {
            margin: 24px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
        }
        .section h3 {
            margin-top: 0;
            color: #0d6efd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #eef5ff;
        }
        .muted {
            color: #6c757d;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-warning {
            background: #fff3cd;
            color: #664d03;
        }
        .badge-info {
            background: #cff4fc;
            color: #055160;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">Technofra Renewal Master</div>
            <p>AMC Maintenance Visit Reminder</p>
        </div>

        <p>Hello Admin,</p>
        <p class="muted">
            This summary includes only <strong>pending</strong> AMC visits.
        </p>

        <div class="section">
            <h3>Visits Due Tomorrow <span class="badge badge-warning">{{ $beforeVisitReminders->count() }}</span></h3>
            <p class="muted">Visit date: {{ \Carbon\Carbon::parse($tomorrowDate)->format('d M Y') }}</p>

            @if($beforeVisitReminders->isEmpty())
                <p>No pending AMC visits are due tomorrow.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Client</th>
                            <th>Vendor</th>
                            <th>Visit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($beforeVisitReminders as $detail)
                            @php
                                $service = $detail->amcService?->service;
                            @endphp
                            <tr>
                                <td>{{ $detail->visit_number }}</td>
                                <td>{{ $service?->service_name ?? 'N/A' }}</td>
                                <td>
                                    {{ $service?->client?->cname ?? 'N/A' }}<br>
                                    <small class="muted">{{ $service?->client?->email ?? '' }}</small>
                                </td>
                                <td>{{ $service?->vendor?->name ?? 'N/A' }}</td>
                                <td>{{ optional($detail->visit_date)->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="section">
            <h3>Visits Due Today <span class="badge badge-info">{{ $sameDayReminders->count() }}</span></h3>
            <p class="muted">Visit date: {{ \Carbon\Carbon::parse($todayDate)->format('d M Y') }}</p>

            @if($sameDayReminders->isEmpty())
                <p>No pending AMC visits are due today.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Client</th>
                            <th>Vendor</th>
                            <th>Visit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sameDayReminders as $detail)
                            @php
                                $service = $detail->amcService?->service;
                            @endphp
                            <tr>
                                <td>{{ $detail->visit_number }}</td>
                                <td>{{ $service?->service_name ?? 'N/A' }}</td>
                                <td>
                                    {{ $service?->client?->cname ?? 'N/A' }}<br>
                                    <small class="muted">{{ $service?->client?->email ?? '' }}</small>
                                </td>
                                <td>{{ $service?->vendor?->name ?? 'N/A' }}</td>
                                <td>{{ optional($detail->visit_date)->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="footer">
            <p><strong>Technofra Renewal Master</strong></p>
            <p>This is an automated AMC maintenance visit reminder email.</p>
        </div>
    </div>
</body>
</html>
