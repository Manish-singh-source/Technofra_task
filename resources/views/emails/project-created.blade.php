<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 640px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f6f8;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: #0d6efd;
            color: #fff;
            padding: 24px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 24px;
        }
        .details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }
        .details td {
            padding: 10px 12px;
            border: 1px solid #e9ecef;
            vertical-align: top;
        }
        .details td:first-child {
            width: 160px;
            font-weight: bold;
            background: #f8f9fa;
        }
        .footer {
            padding: 18px 24px;
            background: #f8f9fa;
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>{{ $recipientType === 'admin' ? 'New Project Created' : 'Project Assignment Notification' }}</h1>
        </div>
        <div class="content">
            <p>
                @if($recipientType === 'admin')
                    A new project has been created in the system.
                @else
                    You have been added as a member to a newly created project.
                @endif
            </p>

            <table class="details">
                <tr>
                    <td>Project Name</td>
                    <td>{{ $project->project_name }}</td>
                </tr>
                <tr>
                    <td>Client</td>
                    <td>{{ optional($project->customerUser)->name ?: optional($project->customerUser)->email ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>{{ ucwords(str_replace('_', ' ', $project->status ?? 'not_started')) }}</td>
                </tr>
                <tr>
                    <td>Priority</td>
                    <td>{{ ucfirst($project->priority ?? 'medium') }}</td>
                </tr>
                <tr>
                    <td>Start Date</td>
                    <td>{{ $project->start_date ? $project->start_date->format('d M Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Deadline</td>
                    <td>{{ $project->deadline ? $project->deadline->format('d M Y') : 'N/A' }}</td>
                </tr>
                @if(!empty($project->description))
                <tr>
                    <td>Description</td>
                    <td>{{ $project->description }}</td>
                </tr>
                @endif
            </table>
        </div>
        <div class="footer">
            <p>This is an automated notification from the project management system.</p>
        </div>
    </div>
</body>
</html>

