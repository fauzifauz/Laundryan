<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laundryan - Payslip PAY-{{ sprintf('%04d', $payroll->id) }}</title>
    <style>
        @page {
            margin: 1cm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
        }
        .header {
            border-bottom: 3px solid #1e40af;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 900;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .meta-grid {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }
        .meta-grid td {
            width: 50%;
            vertical-align: top;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        .label {
            font-size: 8px;
            font-weight: 900;
            text-transform: uppercase;
            color: #9ca3af;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
        }
        .value {
            font-size: 11px;
            font-weight: 900;
            color: #111827;
        }
        .value-blue {
            color: #2563eb;
        }
        .section-title {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }
        table.breakdown {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        table.breakdown th,
        table.breakdown td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            font-size: 10px;
        }
        table.breakdown th {
            background: #f3f4f6;
            text-transform: uppercase;
            font-size: 8px;
            font-weight: 900;
            color: #6b7280;
        }
        table.breakdown tfoot td {
            background: #eff6ff;
            font-weight: 900;
            font-size: 11px;
            color: #1d4ed8;
        }
        .text-emerald { color: #059669; }
        .text-rose { color: #dc2626; }
        .audit-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .audit-grid td {
            width: 25%;
            border: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
            font-size: 8px;
            color: #9ca3af;
        }
        .signature {
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>
<body>
    @php
        $periodLabel = \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('F Y');
        $netSalary = $payroll->amount + $payroll->bonus - $payroll->potongan;
        $otherDeductions = max(0, ($payroll->potongan ?? 0) - ($payroll->alpha_deduction ?? 0));
    @endphp

    <div class="header">
        <h1>LAUNDRYAN</h1>
        <p>Official Digital Payslip &mdash; {{ $periodLabel }}</p>
    </div>

    <table class="meta-grid">
        <tr>
            <td>
                <div class="label">Recipient</div>
                <div class="value">{{ $user->name }}</div>
                <div style="font-size: 9px; color: #6b7280; margin-top: 4px;">Role: {{ strtoupper($user->role) }}</div>
            </td>
            <td style="text-align: right;">
                <div class="label">Payroll ID</div>
                <div class="value value-blue">PAY-{{ sprintf('%04d', $payroll->id) }}</div>
                <div style="font-size: 9px; color: #6b7280; margin-top: 4px;">Period: {{ $periodLabel }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Earnings Breakdown</div>
    <table class="breakdown">
        <thead>
            <tr>
                <th>Component</th>
                <th style="width: 35%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Base Salary</td>
                <td style="text-align: right; font-weight: 900;">Rp {{ number_format($payroll->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-emerald">Bonuses &amp; Incentives</td>
                <td style="text-align: right; font-weight: 900;" class="text-emerald">+Rp {{ number_format($payroll->bonus, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-rose">Other Deductions</td>
                <td style="text-align: right; font-weight: 900;" class="text-rose">-Rp {{ number_format($otherDeductions, 0, ',', '.') }}</td>
            </tr>
            @if(($payroll->alpha_deduction ?? 0) > 0)
            <tr>
                <td class="text-rose">
                    Alpha Penalty (5%)
                    <div style="font-size: 8px; font-weight: normal; margin-top: 2px;">{{ $payroll->alpha_count }} unexcused absences</div>
                </td>
                <td style="text-align: right; font-weight: 900;" class="text-rose">-Rp {{ number_format($payroll->alpha_deduction, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>Net Salary</td>
                <td style="text-align: right;">Rp {{ number_format($netSalary, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">Payment Details</div>
    <table class="audit-grid">
        <tr>
            <td>
                <div class="label">Withdrawal Status</div>
                <div class="value" style="text-transform: uppercase; color: {{ $payroll->status === 'paid' ? '#059669' : '#d97706' }};">{{ $payroll->status }}</div>
            </td>
            <td>
                <div class="label">Payout Method</div>
                <div class="value" style="text-transform: uppercase;">{{ $payroll->payment_method ?: '-' }}</div>
            </td>
            <td>
                <div class="label">Payment Date</div>
                <div class="value">{{ $payroll->payment_date ? \Carbon\Carbon::parse($payroll->payment_date)->format('d F Y, H:i') : '-' }}</div>
            </td>
            <td>
                <div class="label">Reference Code</div>
                <div class="value" style="font-size: 8px;">{{ $payroll->stripe_transfer_id ?: '-' }}</div>
            </td>
        </tr>
    </table>

    <div class="signature">
        <div class="label">Authorized by</div>
        <div class="value">Laundryan Finance Division</div>
    </div>

    <div class="footer">
        Generated on {{ now()->timezone('Asia/Jakarta')->format('l, d F Y H:i') }} WIB &mdash; This is an official digital payslip issued by Laundryan.
    </div>
</body>
</html>
