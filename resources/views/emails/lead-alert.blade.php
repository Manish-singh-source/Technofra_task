<!doctype html>
<html lang="en">
<body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#243653;padding:28px 12px">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border:1px solid #e4eaf2;border-radius:14px;overflow:hidden">
            <tr><td style="background:#243c69;color:#fff;padding:24px 28px"><h1 style="margin:0;font-size:22px">Lead Alert</h1><p style="margin:7px 0 0;color:#dbe6ff">{{ $alertLabel }}</p></td></tr>
            <tr><td style="padding:28px">
                <p style="font-size:16px;margin-top:0">A lead needs your attention.</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5eaf2;border-radius:10px">
                    <tr><td style="padding:11px 14px;font-weight:bold;width:35%;border-bottom:1px solid #edf0f5">Lead</td><td style="padding:11px 14px;border-bottom:1px solid #edf0f5">{{ $lead->name ?: 'Unnamed lead' }}</td></tr>
                    <tr><td style="padding:11px 14px;font-weight:bold;border-bottom:1px solid #edf0f5">Priority</td><td style="padding:11px 14px;border-bottom:1px solid #edf0f5;text-transform:capitalize">{{ $lead->priority ?: 'medium' }}</td></tr>
                    <tr><td style="padding:11px 14px;font-weight:bold;border-bottom:1px solid #edf0f5">Status</td><td style="padding:11px 14px;border-bottom:1px solid #edf0f5;text-transform:capitalize">{{ str_replace('_',' ', $lead->status ?: 'new') }}</td></tr>
                    <tr><td style="padding:11px 14px;font-weight:bold">Next follow-up</td><td style="padding:11px 14px">{{ $lead->next_followup_at?->format('d M Y, h:i A') ?: 'No next action' }}</td></tr>
                </table>
                <p style="margin:24px 0"><a href="{{ $leadUrl }}" style="display:inline-block;background:#4161bc;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px">Open lead in CRM</a></p>
                <p style="font-size:12px;color:#7b8798;margin-bottom:0">This alert was generated automatically by MyCRM.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
