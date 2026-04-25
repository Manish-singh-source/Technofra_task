<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Daily Renewal Notifications</title>

    <style>
        :root {
            color-scheme: light;
            supported-color-schemes: light;
        }

        body,
        table,
        td,
        div,
        p,
        h1,
        h2,
        h3,
        span {
            color-scheme: light;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f3f4f6 !important;
            color: #334155 !important;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        table {
            border-collapse: collapse;
        }

        a {
            color: #2563eb !important;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
        }

        .email-bg {
            background-color: #f3f4f6 !important;
        }

        .email-card {
            background-color: #ffffff !important;
            color: #334155 !important;
        }

        .logo-box {
            background-color: #ffffff !important;
        }

        .section-box {
            background-color: #ffffff !important;
            border: 1px solid #d1d5db !important;
        }

        .table-header {
            background-color: #f8fafc !important;
            color: #1f2937 !important;
        }

        .table-cell {
            background-color: #ffffff !important;
            color: #475569 !important;
        }

        .footer-box {
            background-color: #f1f5f9 !important;
            color: #64748b !important;
        }
    </style>
</head>

<body class="email-bg"
    style="margin:0; padding:0; background-color:#f3f4f6 !important; font-family:Arial, Helvetica, sans-serif; color:#334155 !important;">
    @php
        $clientServices = $criticalServices ?? collect();
        $vendorServices = $criticalVendorServices ?? collect();

        $totalRenewals = $clientServices->count() + $vendorServices->count();
        $today = \Carbon\Carbon::today();

        $expiringTodayCount =
            $clientServices
                ->filter(fn($service) => $service->end_date && $service->end_date->isSameDay($today))
                ->count() +
            $vendorServices
                ->filter(fn($service) => $service->end_date && $service->end_date->isSameDay($today))
                ->count();

        $expiredCount =
            $clientServices->filter(fn($service) => $service->end_date && $service->end_date->lt($today))->count() +
            $vendorServices->filter(fn($service) => $service->end_date && $service->end_date->lt($today))->count();

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

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="email-bg"
        style="background-color:#f3f4f6 !important; margin:0; padding:24px 0;">
        <tr>
            <td align="center" style="padding:0 12px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="560"
                    class="email-card"
                    style="width:560px; max-width:560px; background-color:#ffffff !important; color:#334155 !important; border:1px solid #e5e7eb;">

                    <tr>
                        <td
                            style="padding:18px 28px 8px 28px; font-size:14px; color:#64748b !important; background-color:#ffffff !important;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td
                                        style="font-size:14px; color:#64748b !important; background-color:#ffffff !important;">
                                        A daily renewal notification has been generated from the Technofra dashboard.
                                    </td>
                                    <td align="right"
                                        style="font-size:14px; color:#64748b !important; background-color:#ffffff !important;">
                                        {{ now()->format('d M Y H:i') }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td class="logo-box" style="padding:22px 48px 0 48px; background-color:#ffffff !important;">
                            <div style="display:inline-block; background-color:#ffffff !important; padding:10px 12px;">
                                <img src="https://mycrm.technofra.com/assets/images/logo-black.png" alt="Technofra"
                                    width="200"
                                    style="display:block; width:200px; max-width:200px; height:auto; border:0;">
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 48px 0 48px; background-color:#ffffff !important;">
                            <h1
                                style="margin:0; font-size:24px; line-height:1.35; color:#1f2937 !important; font-weight:700;">
                                Daily Renewal Email Notifications
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="padding:18px 48px 0 48px; font-size:16px; line-height:1.7; color:#475569 !important; background-color:#ffffff !important;">
                            {{ $totalRenewals }} renewal item(s) need attention today. The summary below includes client
                            and vendor renewals with their expiry status so your team can follow up quickly.
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 48px 0 48px; background-color:#ffffff !important;">
                            <a href="{{ url('/services') }}"
                                style="display:inline-block; background-color:#374151 !important; color:#ffffff !important; text-decoration:none; padding:15px 34px; font-size:15px; font-weight:700;">
                                Open Renewals
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:44px 48px 0 48px; background-color:#ffffff !important;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                class="section-box"
                                style="border:1px solid #d1d5db !important; background-color:#ffffff !important;">
                                <tr>
                                    <td style="padding:30px; background-color:#ffffff !important;">
                                        <h2
                                            style="margin:0 0 24px 0; font-size:18px; color:#1f2937 !important; font-weight:700;">
                                            Renewal Summary
                                        </h2>

                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%"
                                            style="border:1px solid #d1d5db !important; margin-bottom:16px; border-collapse:collapse; background-color:#ffffff !important;">
                                            <tr>
                                                <td colspan="2" class="table-header"
                                                    style="padding:14px 16px; background-color:#f8fafc !important; border-bottom:1px solid #d1d5db !important; font-size:16px; font-weight:700; color:#1f2937 !important;">
                                                    Renewal Summary Overview
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; width:42%; background-color:#ffffff !important;">
                                                    Total Renewal Items
                                                </td>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                    {{ $totalRenewals }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                    Client Renewals
                                                </td>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                    {{ $clientServices->count() }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                    Vendor Renewals
                                                </td>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                    {{ $vendorServices->count() }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                    Expiring Today
                                                </td>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                    {{ $expiringTodayCount }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                    Already Expired
                                                </td>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                    {{ $expiredCount }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                    Quick Access
                                                </td>
                                                <td
                                                    style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                    <a href="{{ url('/client') }}"
                                                        style="color:#2563eb !important; text-decoration:underline;">
                                                        Open Client Renewals
                                                    </a>
                                                    &nbsp;|&nbsp;
                                                    <a href="{{ url('/vendor-services') }}"
                                                        style="color:#2563eb !important; text-decoration:underline;">
                                                        Open Vendor Renewals
                                                    </a>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="padding:12px 16px; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                    Generated At
                                                </td>
                                                <td
                                                    style="padding:12px 16px; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                    {{ now()->format('d M Y H:i') }}
                                                </td>
                                            </tr>
                                        </table>

                                        @if ($clientServices->count() > 0)
                                            <div style="height:26px; border-bottom:1px solid #e5e7eb !important;"></div>

                                            <h3
                                                style="margin:24px 0 18px 0; font-size:18px; color:#1f2937 !important; font-weight:700;">
                                                Client Renewal Details
                                            </h3>

                                            @foreach ($clientServices as $service)
                                                <table role="presentation" cellpadding="0" cellspacing="0"
                                                    border="0" width="100%"
                                                    style="border:1px solid #d1d5db !important; margin-bottom:16px; border-collapse:collapse; background-color:#ffffff !important;">
                                                    <tr>
                                                        <td colspan="2"
                                                            style="padding:14px 16px; background-color:#f8fafc !important; border-bottom:1px solid #d1d5db !important; font-size:16px; font-weight:700; color:#1f2937 !important;">
                                                            {{ $service->service_name ?: 'Client Service' }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; width:42%; background-color:#ffffff !important;">
                                                            Client
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            {{ $service->client->cname ?? 'N/A' }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                            Vendor
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            {{ $service->vendor->name ?? 'N/A' }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                            Renewal Date
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            {{ optional($service->end_date)->format('d M Y') ?: 'N/A' }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                            Renewal Status
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            {{ $describeRenewal($service) }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                            Amount
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            Rs. {{ number_format((float) ($service->amount ?? 0), 2) }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                            Current Status
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            {{ $service->status_label ?? ucfirst($service->status ?? 'N/A') }}
                                                        </td>
                                                    </tr>
                                                </table>
                                            @endforeach
                                        @endif

                                        @if ($vendorServices->count() > 0)
                                            <div style="height:10px;"></div>

                                            <h3
                                                style="margin:24px 0 18px 0; font-size:18px; color:#1f2937 !important; font-weight:700;">
                                                Vendor Renewal Details
                                            </h3>

                                            @foreach ($vendorServices as $service)
                                                <table role="presentation" cellpadding="0" cellspacing="0"
                                                    border="0" width="100%"
                                                    style="border:1px solid #d1d5db !important; margin-bottom:16px; border-collapse:collapse; background-color:#ffffff !important;">
                                                    <tr>
                                                        <td colspan="2"
                                                            style="padding:14px 16px; background-color:#f8fafc !important; border-bottom:1px solid #d1d5db !important; font-size:16px; font-weight:700; color:#1f2937 !important;">
                                                            {{ $service->service_name ?: 'Vendor Service' }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; width:42%; background-color:#ffffff !important;">
                                                            Vendor
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            {{ $service->vendor->name ?? 'N/A' }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                            Plan Type
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            {{ $service->plan_type ?? 'N/A' }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                            Renewal Date
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            {{ optional($service->end_date)->format('d M Y') ?: 'N/A' }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                            Renewal Status
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; border-bottom:1px solid #e5e7eb !important; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            {{ $describeRenewal($service) }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="padding:12px 16px; font-size:14px; font-weight:700; color:#334155 !important; background-color:#ffffff !important;">
                                                            Current Status
                                                        </td>
                                                        <td
                                                            style="padding:12px 16px; font-size:14px; color:#475569 !important; background-color:#ffffff !important;">
                                                            {{ $service->status_label ?? ucfirst($service->status ?? 'N/A') }}
                                                        </td>
                                                    </tr>
                                                </table>
                                            @endforeach
                                        @endif

                                        <div style="height:26px; border-bottom:1px solid #e5e7eb !important;"></div>

                                        <h3
                                            style="margin:24px 0 18px 0; font-size:18px; color:#1f2937 !important; font-weight:700;">
                                            Recommended Next Steps
                                        </h3>

                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%">
                                            <tr>
                                                <td valign="top"
                                                    style="padding:0 20px 20px 0; width:38px; background-color:#ffffff !important;">
                                                    <div
                                                        style="width:34px; height:34px; line-height:34px; text-align:center; border:1px solid #6b7280 !important; color:#374151 !important; font-size:14px; font-weight:700; background-color:#ffffff !important;">
                                                        1
                                                    </div>
                                                </td>
                                                <td valign="top"
                                                    style="padding:0 0 20px 0; background-color:#ffffff !important;">
                                                    <div
                                                        style="font-size:16px; font-weight:700; color:#1f2937 !important; margin-bottom:6px;">
                                                        Review renewal dates
                                                    </div>
                                                    <div
                                                        style="font-size:15px; line-height:1.7; color:#64748b !important;">
                                                        Check all listed client and vendor renewals and prioritize items
                                                        that have already expired or are due today.
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td valign="top"
                                                    style="padding:0 20px 20px 0; width:38px; background-color:#ffffff !important;">
                                                    <div
                                                        style="width:34px; height:34px; line-height:34px; text-align:center; border:1px solid #6b7280 !important; color:#374151 !important; font-size:14px; font-weight:700; background-color:#ffffff !important;">
                                                        2
                                                    </div>
                                                </td>
                                                <td valign="top"
                                                    style="padding:0 0 20px 0; background-color:#ffffff !important;">
                                                    <div
                                                        style="font-size:16px; font-weight:700; color:#1f2937 !important; margin-bottom:6px;">
                                                        Coordinate renewals
                                                    </div>
                                                    <div
                                                        style="font-size:15px; line-height:1.7; color:#64748b !important;">
                                                        Reach out to the relevant client, vendor, or internal owner so
                                                        payment and continuation steps are completed on time.
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td valign="top"
                                                    style="padding:0 20px 0 0; width:38px; background-color:#ffffff !important;">
                                                    <div
                                                        style="width:34px; height:34px; line-height:34px; text-align:center; border:1px solid #6b7280 !important; color:#374151 !important; font-size:14px; font-weight:700; background-color:#ffffff !important;">
                                                        3
                                                    </div>
                                                </td>
                                                <td valign="top"
                                                    style="padding:0; background-color:#ffffff !important;">
                                                    <div
                                                        style="font-size:16px; font-weight:700; color:#1f2937 !important; margin-bottom:6px;">
                                                        Update the renewal records
                                                    </div>
                                                    <div
                                                        style="font-size:15px; line-height:1.7; color:#64748b !important;">
                                                        After action is taken, update the service status and dates in
                                                        the dashboard so future reminders stay accurate.
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px 48px 0 48px; background-color:#ffffff !important;">
                            <div
                                style="font-size:16px; font-weight:700; color:#1f2937 !important; margin-bottom:16px;">
                                Quick Actions
                            </div>

                            <div style="font-size:15px; line-height:1.9; color:#475569 !important;">
                                <a href="{{ url('/client') }}"
                                    style="color:#2563eb !important; text-decoration:none;">
                                    Open Client Renewals
                                </a>
                                <span style="color:#94a3b8 !important;">&nbsp;|&nbsp;</span>
                                <a href="{{ url('/vendor-services') }}"
                                    style="color:#2563eb !important; text-decoration:none;">
                                    Open Vendor Services
                                </a>
                                <span style="color:#94a3b8 !important;">&nbsp;|&nbsp;</span>
                                <a href="{{ url('/') }}"
                                    style="color:#2563eb !important; text-decoration:none;">
                                    Visit Dashboard
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px 48px 40px 48px; background-color:#ffffff !important;">
                            <div class="footer-box"
                                style="background-color:#f1f5f9 !important; padding:28px; font-size:14px; line-height:1.8; color:#64748b !important; border:1px solid #e5e7eb;">
                                This notification was generated automatically from the daily renewal email notification
                                settings in the Technofra dashboard.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
