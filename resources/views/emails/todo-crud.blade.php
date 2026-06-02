<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo Notification</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif; color:#334155;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f4f6; margin:0; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="560" style="width:560px; max-width:560px; background-color:#ffffff;">
                    <tr>
                        <td style="padding:24px 36px 8px 36px;">
                            <h1 style="margin:0; font-size:24px; color:#1e293b;">Todo {{ $actionLabel }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 36px; font-size:16px; line-height:1.7; color:#475569;">
                            Hello {{ $recipient->name ?: $recipient->email }},
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 36px 0 36px; font-size:16px; line-height:1.7; color:#475569;">
                            {{ $message }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 36px 0 36px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #dbe4f0;">
                                <tr>
                                    <td style="padding:12px 14px; font-weight:700; width:40%; border-bottom:1px solid #dbe4f0;">Title</td>
                                    <td style="padding:12px 14px; border-bottom:1px solid #dbe4f0;">{{ $todo->title }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px; font-weight:700; border-bottom:1px solid #dbe4f0;">Task Date</td>
                                    <td style="padding:12px 14px; border-bottom:1px solid #dbe4f0;">{{ optional($todo->task_date)?->format('d M Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px; font-weight:700; border-bottom:1px solid #dbe4f0;">Status</td>
                                    <td style="padding:12px 14px; border-bottom:1px solid #dbe4f0;">{{ $todo->is_completed ? 'Completed' : 'Open' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px; font-weight:700;">Action Time</td>
                                    <td style="padding:12px 14px;">{{ now()->format('d M Y H:i') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 36px 36px 36px;">
                            <a href="{{ route('to-do-list') }}" style="display:inline-block; background-color:#0b3c74; color:#ffffff; text-decoration:none; padding:12px 20px; font-size:14px; font-weight:700;">Open Todo List</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
