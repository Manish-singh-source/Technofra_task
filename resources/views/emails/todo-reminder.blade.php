<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo Reminder</title>
</head>

<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif; color:#334155;">
    @php
        $recipientName = optional($todo->user)->name ?: 'Team Member';
        $taskTime = $todo->task_time
            ? \Carbon\Carbon::createFromFormat('H:i:s', $todo->task_time)->format('h:i A')
            : 'Any time';
        $reminderTime = $todo->reminder_time
            ? \Carbon\Carbon::createFromFormat(
                strlen($todo->reminder_time) === 5 ? 'H:i' : 'H:i:s',
                $todo->reminder_time,
            )->format('h:i A')
            : $taskTime;
        $startsOn = $todo->starts_on
            ? \Carbon\Carbon::parse($todo->starts_on)->format('d M Y')
            : $occurrenceDate->format('d M Y');
        $endsOn =
            $todo->ends_type === 'on' && $todo->ends_on
                ? \Carbon\Carbon::parse($todo->ends_on)->format('d M Y')
                : ($todo->ends_type === 'after' && $todo->ends_after_occurrences
                    ? $todo->ends_after_occurrences . ' occurrences'
                    : 'Never');
        $repeatDays = !empty($todo->repeat_days_list)
            ? collect($todo->repeat_days_list)->map(fn($day) => ucfirst(substr($day, 0, 3)))->implode(', ')
            : 'Not specified';
    @endphp

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
        style="background-color:#f3f4f6; margin:0; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="560"
                    style="width:560px; max-width:560px; background-color:#ffffff;">
                    <tr>
                        <td style="padding:18px 28px 8px 28px; font-size:14px; color:#64748b;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="font-size:14px; color:#64748b;">
                                        A todo reminder has been triggered from the Technofra dashboard.
                                    </td>
                                    <td align="right" style="font-size:14px; color:#64748b;">
                                        {{ now()->format('d M Y H:i') }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 48px 0 48px;">
                            <img src="{{ asset('https://technofra.com/logo.png') }}" alt="Technofra"
                                style="display:block; width:56px; height:auto; border:0;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 48px 0 48px;">
                            <h1 style="margin:0; font-size:24px; line-height:1.35; color:#2b3b52; font-weight:700;">Todo
                                Reminder Request</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 48px 0 48px; font-size:16px; line-height:1.7; color:#475569;">
                            {{ $recipientName }}, your scheduled todo is now ready for attention. Below is the complete
                            task summary captured from the todo list so you can review the reminder details quickly.
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 48px 0 48px;">
                            <a href="{{ route('to-do-list') }}"
                                style="display:inline-block; background-color:#0b3c74; color:#ffffff; text-decoration:none; padding:15px 34px; font-size:15px; font-weight:700;">Open
                                Todo List</a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:56px 48px 0 48px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="border:1px solid #b8c9de;">
                                <tr>
                                    <td style="padding:30px;">
                                        <h2 style="margin:0 0 24px 0; font-size:18px; color:#2b3b52; font-weight:700;">
                                            Todo Summary</h2>

                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%" style="border-collapse:collapse;">
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b; width:48%;">
                                                    Task Title</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ $todo->title }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Assigned To</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ $recipientName }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Description</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ $todo->description ?: 'No additional description added for this task.' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Occurrence Date</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ $occurrenceDate->format('d M Y') }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Task Time</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ $taskTime }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Reminder Time</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ $reminderTime }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Repeat Schedule</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ $todo->display_schedule }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Repeat Days</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ $repeatDays }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Starts On</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ $startsOn }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Ends</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ ucfirst($todo->ends_type ?? 'never') }}{{ $endsOn !== 'Never' ? ' - ' . $endsOn : '' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Quick Access</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    <a href="{{ route('to-do-list') }}"
                                                        style="color:#004aad; text-decoration:underline;">Open Todo
                                                        Dashboard</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; font-weight:700; color:#1e293b;">
                                                    Submitted At</td>
                                                <td
                                                    style="padding:14px 12px; border-bottom:1px solid #d9e3ef; font-size:15px; color:#334155;">
                                                    {{ now()->format('d M Y H:i') }}</td>
                                            </tr>
                                        </table>

                                        <div style="height:26px; border-bottom:1px solid #e2e8f0;"></div>

                                        <h3
                                            style="margin:24px 0 18px 0; font-size:18px; color:#2b3b52; font-weight:700;">
                                            Recommended Next Steps</h3>

                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td valign="top" style="padding:0 20px 20px 0; width:38px;">
                                                    <div
                                                        style="width:34px; height:34px; line-height:34px; text-align:center; border:1px solid #0b3c74; color:#0b3c74; font-size:14px; font-weight:700;">
                                                        1</div>
                                                </td>
                                                <td valign="top" style="padding:0 0 20px 0;">
                                                    <div
                                                        style="font-size:16px; font-weight:700; color:#26384f; margin-bottom:6px;">
                                                        Review the task details</div>
                                                    <div style="font-size:15px; line-height:1.7; color:#64748b;">Check
                                                        the title, description, and schedule so the todo is handled with
                                                        the right context.</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" style="padding:0 20px 20px 0; width:38px;">
                                                    <div
                                                        style="width:34px; height:34px; line-height:34px; text-align:center; border:1px solid #0b3c74; color:#0b3c74; font-size:14px; font-weight:700;">
                                                        2</div>
                                                </td>
                                                <td valign="top" style="padding:0 0 20px 0;">
                                                    <div
                                                        style="font-size:16px; font-weight:700; color:#26384f; margin-bottom:6px;">
                                                        Complete or update the todo</div>
                                                    <div style="font-size:15px; line-height:1.7; color:#64748b;">Mark
                                                        the task as done if finished, or edit the recurrence and
                                                        reminder if timing needs adjustment.</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" style="padding:0 20px 0 0; width:38px;">
                                                    <div
                                                        style="width:34px; height:34px; line-height:34px; text-align:center; border:1px solid #0b3c74; color:#0b3c74; font-size:14px; font-weight:700;">
                                                        3</div>
                                                </td>
                                                <td valign="top" style="padding:0;">
                                                    <div
                                                        style="font-size:16px; font-weight:700; color:#26384f; margin-bottom:6px;">
                                                        Stay on schedule</div>
                                                    <div style="font-size:15px; line-height:1.7; color:#64748b;">Use
                                                        the shared reminder details to stay on top of recurring work and
                                                        avoid missing future occurrences.</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px 48px 0 48px;">
                            <div style="font-size:16px; font-weight:700; color:#2b3b52; margin-bottom:16px;">Quick
                                Actions</div>
                            <div style="font-size:15px; line-height:1.9;">
                                <a href="{{ route('to-do-list') }}" style="color:#004aad; text-decoration:none;">Open
                                    Todo List</a>
                                <span style="color:#94a3b8;">&nbsp;|&nbsp;</span>
                                <a href="mailto:{{ config('mail.from.address', 'support@technofra.com') }}"
                                    style="color:#004aad; text-decoration:none;">Contact Support</a>
                                <span style="color:#94a3b8;">&nbsp;|&nbsp;</span>
                                <a href="{{ url('/') }}" style="color:#004aad; text-decoration:none;">Visit
                                    Dashboard</a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px 48px 40px 48px;">
                            <div
                                style="background-color:#f1f5f9; padding:28px; font-size:14px; line-height:1.8; color:#64748b;">
                                This notification was generated automatically after a todo reminder matched its
                                scheduled time in the Technofra dashboard.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
