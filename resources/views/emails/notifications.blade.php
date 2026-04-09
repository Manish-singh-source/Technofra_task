<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Renewal Notifications</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif; color:#334155;">
    @php
        $clientServices = $criticalServices ?? collect();
        $vendorServices = $criticalVendorServices ?? collect();
        $totalRenewals = $clientServices->count() + $vendorServices->count();
        $today = \Carbon\Carbon::today();
        $expiringTodayCount = $clientServices->filter(fn ($service) => $service->end_date && $service->end_date->isSameDay($today))->count()
            + $vendorServices->filter(fn ($service) => $service->end_date && $service->end_date->isSameDay($today))->count();
        $expiredCount = $clientServices->filter(fn ($service) => $service->end_date && $service->end_date->lt($today))->count()
            + $vendorServices->filter(fn ($service) => $service->end_date && $service->end_date->lt($today))->count();

        $describeRenewal = function ($service) use ($today) {
            if (!$service->end_date) {
                return 'Renewal date not available';
            }

            $daysLeft = $today->diffInDays($service->end_date, false);

            if ($daysLeft < 0) {
                return 'Expired ' . abs($daysLeft) . ' day(s) ago';
            }

            if ($daysLeft === 0) {
                return 'Expires today';
            }

            if ($daysLeft === 1) {
                return 'Expires tomorrow';
            }

            return 'Expires in ' . $daysLeft . ' day(s)';
        };
    @endphp

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f4f6; margin:0; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="560" style="width:560px; max-width:560px; background-color:#ffffff;">
                    <tr>
                        <td style="padding:18px 28px 8px 28px; font-size:14px; color:#64748b;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="font-size:14px; color:#64748b;">
                                        A daily renewal notification has been generated from the Technofra dashboard.
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
                            <img src="{{ asset('https://technofra.com/assets/image/favicon.png') }}" alt="Technofra" style="display:block; width:56px; height:auto; border:0;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 48px 0 48px;">
                            <h1 style="margin:0; font-size:24px; line-height:1.35; color:#2b3b52; font-weight:700;">Daily Renewal Email Notifications</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 48px 0 48px; font-size:16px; line-height:1.7; color:#475569;">
                            {{ $totalRenewals }} renewal item(s) need attention today. The summary below includes client and vendor renewals with their expiry status so your team can follow up quickly.
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 48px 0 48px;">
                            <a href="{{ url('/client') }}" style="display:inline-block; background-color:#0b3c74; color:#ffffff; text-decoration:none; padding:15px 34px; font-size:15px; font-weight:700;">Open Renewals</a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:56px 48px 0 48px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #b8c9de;">
                                <tr>
                                    <td style="padding:30px;">
                                        <h2 style="margin:0 0 24px 0; font-size:18px; color:#2b3b52; font-weight:700;">Renewal Summary</h2>

                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #d9e3ef; margin-bottom:16px; border-collapse:collapse;">
                                            <tr>
                                                <td colspan="2" style="padding:14px 16px; background-color:#f8fafc; border-bottom:1px solid #d9e3ef; font-size:16px; font-weight:700; color:#1e293b;">
                                                    Renewal Summary Overview
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155; width:42%;">Total Renewal Items</td>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $totalRenewals }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Client Renewals</td>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $clientServices->count() }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Vendor Renewals</td>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $vendorServices->count() }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Expiring Today</td>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $expiringTodayCount }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Already Expired</td>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $expiredCount }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Quick Access</td>
                                                <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">
                                                    <a href="{{ url('/client') }}" style="color:#004aad; text-decoration:underline;">Open Client Renewals</a>
                                                    &nbsp;|&nbsp;
                                                    <a href="{{ url('/vendor-services') }}" style="color:#004aad; text-decoration:underline;">Open Vendor Renewals</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px 16px; font-size:14px; font-weight:700; color:#334155;">Generated At</td>
                                                <td style="padding:12px 16px; font-size:14px; color:#475569;">{{ now()->format('d M Y H:i') }}</td>
                                            </tr>
                                        </table>

                                        @if($clientServices->count() > 0)
                                            <div style="height:26px; border-bottom:1px solid #e2e8f0;"></div>
                                            <h3 style="margin:24px 0 18px 0; font-size:18px; color:#2b3b52; font-weight:700;">Client Renewal Details</h3>

                                            @foreach($clientServices as $service)
                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #d9e3ef; margin-bottom:16px; border-collapse:collapse;">
                                                    <tr>
                                                        <td colspan="2" style="padding:14px 16px; background-color:#f8fafc; border-bottom:1px solid #d9e3ef; font-size:16px; font-weight:700; color:#1e293b;">
                                                            {{ $service->service_name ?: 'Client Service' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155; width:42%;">Client</td>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $service->client->cname ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Vendor</td>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $service->vendor->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Renewal Date</td>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ optional($service->end_date)->format('d M Y') ?: 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Renewal Status</td>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $describeRenewal($service) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Amount</td>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">Rs. {{ number_format((float) ($service->amount ?? 0), 2) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; font-size:14px; font-weight:700; color:#334155;">Current Status</td>
                                                        <td style="padding:12px 16px; font-size:14px; color:#475569;">{{ $service->status_label ?? ucfirst($service->status ?? 'N/A') }}</td>
                                                    </tr>
                                                </table>
                                            @endforeach
                                        @endif

                                        @if($vendorServices->count() > 0)
                                            <div style="height:10px;"></div>
                                            <h3 style="margin:24px 0 18px 0; font-size:18px; color:#2b3b52; font-weight:700;">Vendor Renewal Details</h3>

                                            @foreach($vendorServices as $service)
                                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #d9e3ef; margin-bottom:16px; border-collapse:collapse;">
                                                    <tr>
                                                        <td colspan="2" style="padding:14px 16px; background-color:#f8fafc; border-bottom:1px solid #d9e3ef; font-size:16px; font-weight:700; color:#1e293b;">
                                                            {{ $service->service_name ?: 'Vendor Service' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155; width:42%;">Vendor</td>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $service->vendor->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Plan Type</td>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $service->plan_type ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Renewal Date</td>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ optional($service->end_date)->format('d M Y') ?: 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; font-weight:700; color:#334155;">Renewal Status</td>
                                                        <td style="padding:12px 16px; border-bottom:1px solid #e2e8f0; font-size:14px; color:#475569;">{{ $describeRenewal($service) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding:12px 16px; font-size:14px; font-weight:700; color:#334155;">Current Status</td>
                                                        <td style="padding:12px 16px; font-size:14px; color:#475569;">{{ $service->status_label ?? ucfirst($service->status ?? 'N/A') }}</td>
                                                    </tr>
                                                </table>
                                            @endforeach
                                        @endif

                                        <div style="height:26px; border-bottom:1px solid #e2e8f0;"></div>

                                        <h3 style="margin:24px 0 18px 0; font-size:18px; color:#2b3b52; font-weight:700;">Recommended Next Steps</h3>

                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td valign="top" style="padding:0 20px 20px 0; width:38px;">
                                                    <div style="width:34px; height:34px; line-height:34px; text-align:center; border:1px solid #0b3c74; color:#0b3c74; font-size:14px; font-weight:700;">1</div>
                                                </td>
                                                <td valign="top" style="padding:0 0 20px 0;">
                                                    <div style="font-size:16px; font-weight:700; color:#26384f; margin-bottom:6px;">Review renewal dates</div>
                                                    <div style="font-size:15px; line-height:1.7; color:#64748b;">Check all listed client and vendor renewals and prioritize items that have already expired or are due today.</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" style="padding:0 20px 20px 0; width:38px;">
                                                    <div style="width:34px; height:34px; line-height:34px; text-align:center; border:1px solid #0b3c74; color:#0b3c74; font-size:14px; font-weight:700;">2</div>
                                                </td>
                                                <td valign="top" style="padding:0 0 20px 0;">
                                                    <div style="font-size:16px; font-weight:700; color:#26384f; margin-bottom:6px;">Coordinate renewals</div>
                                                    <div style="font-size:15px; line-height:1.7; color:#64748b;">Reach out to the relevant client, vendor, or internal owner so payment and continuation steps are completed on time.</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" style="padding:0 20px 0 0; width:38px;">
                                                    <div style="width:34px; height:34px; line-height:34px; text-align:center; border:1px solid #0b3c74; color:#0b3c74; font-size:14px; font-weight:700;">3</div>
                                                </td>
                                                <td valign="top" style="padding:0;">
                                                    <div style="font-size:16px; font-weight:700; color:#26384f; margin-bottom:6px;">Update the renewal records</div>
                                                    <div style="font-size:15px; line-height:1.7; color:#64748b;">After action is taken, update the service status and dates in the dashboard so future reminders stay accurate.</div>
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
                            <div style="font-size:16px; font-weight:700; color:#2b3b52; margin-bottom:16px;">Quick Actions</div>
                            <div style="font-size:15px; line-height:1.9;">
                                <a href="{{ url('/client') }}" style="color:#004aad; text-decoration:none;">Open Client Renewals</a>
                                <span style="color:#94a3b8;">&nbsp;|&nbsp;</span>
                                <a href="{{ url('/vendor-services') }}" style="color:#004aad; text-decoration:none;">Open Vendor Services</a>
                                <span style="color:#94a3b8;">&nbsp;|&nbsp;</span>
                                <a href="{{ url('/') }}" style="color:#004aad; text-decoration:none;">Visit Dashboard</a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px 48px 40px 48px;">
                            <div style="background-color:#f1f5f9; padding:28px; font-size:14px; line-height:1.8; color:#64748b;">
                                This notification was generated automatically from the daily renewal email notification settings in the Technofra dashboard.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
