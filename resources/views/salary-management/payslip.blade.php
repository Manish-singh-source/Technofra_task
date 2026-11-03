<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Salary Slip - {{ data_get($record->data, 'employee.name') }}</title>
    <style>
        * { box-sizing: border-box; } body { font: 14px Arial, sans-serif; color: #15233d; background: #f1f5f9; margin: 0; }
        .actions { max-width: 850px; margin: 18px auto; text-align: right; } button { background:#0b4e96; color:#fff; border:0; border-radius:5px; padding:10px 16px; cursor:pointer; }
        .slip { width: 210mm; min-height: 297mm; padding: 18mm; margin: auto; background: #fff; }
        .top { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:4px solid #67b92d; padding-bottom:14px; }
        h1 { margin:0; color:#084b91; font-size:28px; } h2 { text-align:center; background:#f6c27f; padding:9px; margin:20px 0 14px; font-size:20px; }
        .company-logo { width:205px; max-height:64px; object-fit:contain; object-position:left center; } .muted { color:#52616f; font-size:12px; }
        table { border-collapse:collapse; width:100%; margin:14px 0; } td,th { border:1px solid #aeb8c5; padding:8px; } th { background:#073d75; color:#fff; text-align:left; } .label { background:#e8edf4; font-weight:bold; width:24%; }
        .right { text-align:right; } .total td { background:#e9f5df; font-size:16px; font-weight:bold; } .signature { margin-top:48px; width:230px; text-align:center; margin-left:auto; } .stamp-image { width:92px; height:92px; display:block; margin:0 auto 4px; object-fit:contain; } .sign-name { font-family:cursive; font-size:22px; color:#084b91; border-bottom:1px solid #64748b; padding-bottom:4px; }
        .page-footer { margin-top:54px; padding-top:14px; border-top:1px solid #263546; text-align:center; color:#111827; font-size:12px; line-height:1.55; }
        @media print { body { background:#fff; } .actions { display:none; } .slip { margin:0; width:auto; min-height:auto; padding:12mm; } @page { size:A4; margin:0; } }
    </style>
</head>
<body>
@php($data = $record->data ?? [])
@php($calculation = data_get($data, 'calculation', []))
@php($employee = data_get($data, 'employee', []))
<div class="actions"><button onclick="window.print()">Download / Save as PDF</button></div>
<main class="slip">
    <div class="top"><div><img class="company-logo" src="{{ route('salary-management.logo') }}" alt="Technofra"><div class="muted mt-1">Web Site | Branding | Mobile APP | E-commerce | SMM | SEO</div></div><div class="right"><strong>Salary Month</strong><br>{{ $record->salary_month?->format('F Y') }}</div></div>
    <h2>EMPLOYEE SALARY SLIP</h2>
    <table><tr><td class="label">Employee Name</td><td>{{ $employee['name'] ?? $record->staffUser?->name }}</td><td class="label">Employee ID</td><td>{{ $employee['employee_id'] ?? '-' }}</td></tr><tr><td class="label">Designation</td><td>{{ $employee['designation'] ?? '-' }}</td><td class="label">Payable Days</td><td>{{ number_format((float) data_get($calculation, 'days_in_month', $record->salary_month?->daysInMonth ?? 30), 1) }}</td></tr><tr><td class="label">Department</td><td>{{ $employee['department'] ?? '-' }}</td><td class="label">Status</td><td>Approved</td></tr></table>
    <table><thead><tr><th>EARNINGS</th><th class="right">AMOUNT (₹)</th><th>DEDUCTIONS</th><th class="right">AMOUNT (₹)</th></tr></thead><tbody>
        <tr><td>Monthly Salary</td><td class="right">{{ number_format((float) data_get($calculation, 'monthly_salary', 0), 2) }}</td><td>Absent / Unpaid / Half-day</td><td class="right">{{ number_format((float) data_get($calculation, 'absent_deduction', 0) + (float) data_get($calculation, 'unpaid_leave_deduction', 0) + (float) data_get($calculation, 'half_day_deduction', 0), 2) }}</td></tr>
        <tr><td>Overtime</td><td class="right">{{ number_format((float) data_get($calculation, 'overtime_payment', 0), 2) }}</td><td>Advance</td><td class="right">{{ number_format((float) data_get($calculation, 'advance', 0), 2) }}</td></tr>
        <tr><td>Bonus / Incentive</td><td class="right">{{ number_format((float) data_get($calculation, 'bonus', 0), 2) }}</td><td>Other (TDS / PF)</td><td class="right">{{ number_format((float) data_get($calculation, 'other_deduction', 0), 2) }}</td></tr>
        <tr class="total"><td colspan="3">NET SALARY</td><td class="right">₹ {{ number_format((float) data_get($calculation, 'net_salary', 0), 2) }}</td></tr>
    </tbody></table>
    <p class="muted">This is a system-generated salary slip. Payment status: {{ ucfirst(data_get($data, 'payment_status', 'pending')) }}.</p>
    <div class="signature"><img class="stamp-image" src="{{ route('salary-management.stamp') }}" alt="Technofra authorised stamp"><div class="sign-name">Gopal Giri</div><small>Authorised Signatory</small></div>
    <footer class="page-footer">Office No. 501, 5th Floor, Ghanshyam Enclave, Link Road, Kandivali (West), Mumbai - 400067. Maharashtra.<br>☎ +91 8080 80 3374 / 8080 80 3375 &nbsp;|&nbsp; Email: info@technofra.com &nbsp;|&nbsp; Website: www.technofra.com</footer>
</main>
</body></html>
