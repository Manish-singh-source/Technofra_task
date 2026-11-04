<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Salary Slip - {{ data_get($record->data, 'employee.name') }}</title>
    <style>
        * { box-sizing: border-box; } body { font: 14px Arial, sans-serif; color: #15233d; background: #f1f5f9; margin: 0; }
        .actions { max-width: 850px; margin: 18px auto; text-align: right; } button { background:#0b4e96; color:#fff; border:0; border-radius:5px; padding:10px 16px; cursor:pointer; }
        .slip { width:210mm; height:297mm; padding:18mm; margin:auto; background:#fff; display:flex; flex-direction:column; }
        .top { display:flex; align-items:center; gap:18px; }
        .company-logo { width:205px; max-height:64px; object-fit:contain; object-position:left center; flex:0 0 auto; } .tagline { color:#111; font-size:14px; }
        .slip-title { text-align:center; background:#f6c27f; border:1px solid #111; padding:6px; margin:21mm 0 0; font-size:20px; font-weight:700; }
        .title-accent { height:5mm; background:#8ccf4d; border:1px solid #111; border-top:0; }
        .muted { color:#52616f; font-size:12px; }
        table { border-collapse:collapse; width:100%; margin:14px 0; } td,th { border:1px solid #111; padding:6px; } th { background:#193b60; color:#fff; text-align:left; } .label { background:#193b60; color:#fff; font-weight:bold; width:20%; }
        .right { text-align:right; } .total td { background:#193b60; color:#fff; font-size:16px; font-weight:bold; } .signature { margin-top:auto; margin-bottom:12mm; width:230px; text-align:center; } .stamp-image { width:92px; height:92px; display:block; margin:0 auto 4px; object-fit:contain; } .sign-name { font-family:cursive; font-size:22px; color:#084b91; border-bottom:1px solid #64748b; padding-bottom:4px; }
        .page-footer { padding-top:10px; border-top:1px solid #263546; text-align:center; color:#111827; font-size:12px; line-height:1.55; }
        @media print { body { background:#fff; } .actions { display:none; } .slip { margin:0; width:210mm; height:297mm; padding:12mm; } @page { size:A4; margin:0; } }
    </style>
</head>
<body>
@php($data = $record->data ?? [])
@php($calculation = data_get($data, 'calculation', []))
@php($employee = data_get($data, 'employee', []))
<div class="actions"><button onclick="window.print()">Download / Save as PDF</button></div>
<main class="slip">
    <div class="top"><img class="company-logo" src="{{ route('salary-management.logo') }}" alt="Technofra"><div class="tagline">Web Site | Branding | Mobile APP | E-commerce | SMM | SEO</div></div>
    <div class="slip-title">EMPLOYEE SALARY SLIP</div>
    <div class="title-accent"></div>
    <table><tr><td class="label">Employee Name</td><td>{{ $employee['name'] ?? $record->staffUser?->name }}</td><td class="label">Employee ID</td><td>{{ $employee['employee_id'] ?? '-' }}</td></tr><tr><td class="label">Designation</td><td>{{ $employee['designation'] ?? '-' }}</td><td class="label">Salary Month</td><td>{{ $record->salary_month?->format('F Y') }}</td></tr><tr><td class="label">Department</td><td>{{ $employee['department'] ?? '-' }}</td><td class="label">Payable Days</td><td>{{ number_format((float) data_get($calculation, 'days_in_month', $record->salary_month?->daysInMonth ?? 30), 1) }}</td></tr><tr><td class="label">Status</td><td colspan="3">Approved</td></tr></table>
    <table><thead><tr><th>EARNING</th><th class="right">AMOUNT (₹)</th><th>DEDUCTIONS</th><th class="right">AMOUNT (₹)</th></tr></thead><tbody>
        <tr><td>Monthly Salary</td><td class="right">{{ number_format((float) data_get($calculation, 'monthly_salary', 0), 2) }}</td><td>Attendance / Salary Adjustment</td><td class="right">{{ number_format((float) data_get($calculation, 'absent_deduction', 0) + (float) data_get($calculation, 'unpaid_leave_deduction', 0) + (float) data_get($calculation, 'half_day_deduction', 0) + (float) data_get($calculation, 'salary_adjustment_deduction', 0), 2) }}</td></tr>
        <tr><td>Overtime</td><td class="right">{{ number_format((float) data_get($calculation, 'overtime_payment', 0), 2) }}</td><td>Advance</td><td class="right">{{ number_format((float) data_get($calculation, 'advance', 0), 2) }}</td></tr>
        <tr><td>Bonus / Incentive</td><td class="right">{{ number_format((float) data_get($calculation, 'bonus', 0), 2) }}</td><td>Other (TDS / PF)</td><td class="right">{{ number_format((float) data_get($calculation, 'other_deduction', 0), 2) }}</td></tr>
        <tr class="total"><td colspan="3">NET SALARY</td><td class="right">₹ {{ number_format((float) data_get($calculation, 'net_salary', 0), 2) }}</td></tr>
    </tbody></table>
    <div class="signature"><img class="stamp-image" src="{{ route('salary-management.stamp') }}" alt="Technofra authorised stamp"><div class="sign-name">Gopal Giri</div><small>Authorised Signatory</small></div>
    <footer class="page-footer">Office No. 501, 5th Floor, Ghanshyam Enclave, Link Road, Kandivali (West), Mumbai - 400067. Maharashtra.<br>☎ +91 8080 80 3374 / 8080 80 3375 &nbsp;|&nbsp; Email: info@technofra.com &nbsp;|&nbsp; Website: www.technofra.com</footer>
</main>
</body></html>
