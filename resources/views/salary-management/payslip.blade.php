<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Salary Slip - {{ data_get($record->data, 'employee.name') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f1f5f9; color: #102446; font: 12px Arial, sans-serif; }
        .actions { width: 210mm; margin: 18px auto; text-align: right; }
        .actions button { padding: 10px 16px; border: 0; border-radius: 5px; background: #0b4e96; color: #fff; cursor: pointer; }
        .slip { width: 210mm; min-height: 297mm; margin: auto; padding: 15mm 11mm 10mm; background: #fff; display: flex; flex-direction: column; }
        .top { min-height: 18mm; display: flex; align-items: center; gap: 5mm; }
        .company-logo { width: 75mm; max-height: 18mm; object-fit: contain; object-position: left center; flex: 0 0 auto; }
        .tagline { padding-left: 4mm; border-left: 1px solid #8794a8; color: #161b24; font-size: 9pt; white-space: nowrap; }
        .slip-title { position: relative; margin-top: 17mm; padding: 2.5mm 5mm; background: linear-gradient(105deg, #123f70, #00264f); color: #fff; font-size: 16pt; font-weight: 700; letter-spacing: .4px; }
        .slip-title:after { position: absolute; top: 0; right: 0; width: 8mm; height: 100%; background: #00b8e4; content: ""; clip-path: polygon(55% 0, 100% 0, 45% 100%, 0 100%); }
        .details { display: grid; grid-template-columns: 1.25fr .95fr; gap: 4mm; margin-top: 12mm; }
        .detail-card { min-height: 46mm; padding: 4mm 5mm; border-radius: 3mm; background: #edf6fd; display: grid; grid-template-columns: 39% 61%; align-content: center; row-gap: 3mm; }
        .detail-card.right-card { grid-template-columns: 46% 54%; }
        .detail-label { color: #46515e; }
        .detail-value { color: #102d5c; font-size: 10pt; font-weight: 700; }
        .payroll-table { width: 100%; margin-top: 13mm; border-collapse: collapse; table-layout: fixed; }
        .payroll-table th, .payroll-table td { border: 1px solid #c5d0dc; padding: 2.3mm 3mm; }
        .payroll-table th { background: #123f70; color: #fff; font-size: 9pt; font-weight: 700; text-align: left; }
        .payroll-table th.amount, .payroll-table td.amount { text-align: right; }
        .payroll-table tbody tr:nth-child(even) { background: #edf6fd; }
        .payroll-table td.amount { font-size: 9pt; }
        .payroll-table .strong { font-weight: 700; }
        .net-salary { display: flex; align-items: center; justify-content: space-between; gap: 6mm; margin-top: 10mm; padding: 3mm 6mm; background: linear-gradient(105deg, #123f70, #00264f); color: #fff; font-size: 18pt; font-weight: 700; }
        .net-salary-label { letter-spacing: .4px; }
        .net-salary-amount { white-space: nowrap; }
        .signature { width: 48mm; margin-top: auto; padding-top: 10mm; text-align: center; }
        .stamp-image { display: block; width: 23mm; height: 23mm; margin: 0 auto; object-fit: contain; }
        .page-footer { margin-top: 14mm; padding-top: 3mm; border-top: 1px solid #aeb8c5; color: #111827; font-size: 8pt; line-height: 1.55; text-align: center; }
        @media print {
            @page { size: A4; margin: 0; }
            body { background: #fff; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .actions { display: none; }
            .slip { width: 210mm; height: 297mm; min-height: 297mm; padding: 15mm 11mm 10mm; }
        }
    </style>
</head>
<body>
@php($data = $record->data ?? [])
@php($calculation = data_get($data, 'calculation', []))
@php($employee = data_get($data, 'employee', []))
<div class="actions"><button onclick="window.print()">Download / Save as PDF</button></div>
<main class="slip">
    <header class="top">
        <img class="company-logo" src="{{ route('salary-management.logo') }}" alt="Technofra">
        <div class="tagline">Web Site | Branding | Mobile APP | E-commerce | SMM | SEO</div>
    </header>

    <div class="slip-title">EMPLOYEE SALARY SLIP</div>

    <section class="details" aria-label="Employee and salary details">
        <div class="detail-card">
            <div class="detail-label">Employee Name</div><div class="detail-value">{{ $employee['name'] ?? $record->staffUser?->name }}</div>
            <div class="detail-label">Designation</div><div class="detail-value">{{ $employee['designation'] ?? '-' }}</div>
            <div class="detail-label">Department</div><div class="detail-value">{{ $employee['department'] ?? '-' }}</div>
            <div class="detail-label">Status</div><div class="detail-value">Approved</div>
        </div>
        <div class="detail-card right-card">
            <div class="detail-label">Employee ID</div><div class="detail-value">{{ $employee['employee_id'] ?? '-' }}</div>
            <div class="detail-label">Salary Month</div><div class="detail-value">{{ $record->salary_month?->format('F Y') }}</div>
            <div class="detail-label">Payable Days</div><div class="detail-value">{{ number_format((float) data_get($calculation, 'days_in_month', $record->salary_month?->daysInMonth ?? 30), 1) }}</div>
        </div>
    </section>

    <table class="payroll-table">
        <colgroup><col style="width:29%"><col style="width:21%"><col style="width:29%"><col style="width:21%"></colgroup>
        <thead><tr><th>EARNING</th><th class="amount">AMOUNT (₹)</th><th>DEDUCTIONS</th><th class="amount">AMOUNT (₹)</th></tr></thead>
        <tbody>
            <tr><td>Monthly Salary</td><td class="amount strong">{{ number_format((float) data_get($calculation, 'monthly_salary', 0), 2) }}</td><td>Advance</td><td class="amount">{{ number_format((float) data_get($calculation, 'advance', 0), 2) }}</td></tr>
            <tr><td>Overtime</td><td class="amount">{{ number_format((float) data_get($calculation, 'overtime_payment', 0), 2) }}</td><td>Other (TDS / PF)</td><td class="amount">{{ number_format((float) data_get($calculation, 'other_deduction', 0), 2) }}</td></tr>
            <tr><td>Bonus / Incentive</td><td class="amount">{{ number_format((float) data_get($calculation, 'bonus', 0), 2) }}</td><td></td><td></td></tr>
            <tr><td>Extra Payment</td><td class="amount">{{ number_format((float) data_get($calculation, 'extra_payment', 0), 2) }}</td><td></td><td></td></tr>
        </tbody>
    </table>

    <div class="net-salary"><span class="net-salary-label">NET SALARY</span><span class="net-salary-amount">₹ {{ number_format((float) data_get($calculation, 'net_salary', 0), 2) }}</span></div>

    <div class="signature">
        <img class="stamp-image" src="{{ route('salary-management.stamp') }}" alt="Technofra company stamp">
    </div>

    <footer class="page-footer">Office No. 501, 5th Floor, Ghanshyam Enclave, Link Road, Kandivali (West), Mumbai - 400067. Maharashtra.<br>☎ +91 8080 80 3374 / 8080 80 3375 &nbsp;|&nbsp; Email: info@technofra.com &nbsp;|&nbsp; Website: www.technofra.com</footer>
</main>
</body>
</html>
