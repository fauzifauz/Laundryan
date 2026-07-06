<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laundryan - Courier Salary Report</title>
    <style>
        @page {
            margin: 0.8cm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
            color: #000;
            line-height: 1.2;
        }
        .header-container {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 12px;
        }
        .logo-box { float: left; width: 20%; }
        .logo-box img { max-width: 100%; height: auto; max-height: 45px; filter: grayscale(100%); }
        .company-info { float: left; width: 50%; padding-left: 15px; }
        .report-title { float: right; width: 30%; text-align: right; }
        .report-title h1 { margin: 0; font-size: 13px; font-weight: 900; }
        .clear { clear: both; }
        
        /* Courier details section */
        .emp-details {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .emp-details-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        .emp-details-grid {
            width: 100%;
        }
        .emp-details-grid td {
            border: none;
            padding: 2px 0;
            font-size: 8px;
        }

        /* Summary Section */
        .summary-table {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .summary-cell {
            width: 25%;
            padding: 10px 5px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
        }
        .summary-label { 
            font-size: 8px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin-bottom: 4px;
            color: #555;
        }
        .summary-value { 
            font-size: 12px; 
            font-weight: 900; 
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            table-layout: fixed;
        }
        th {
            border: 1px solid #000;
            background-color: #f2f2f2;
            color: #000;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 7px;
            padding: 5px 3px;
            text-align: left;
        }
        td {
            border: 1px solid #000;
            padding: 5px 3px;
            vertical-align: middle;
            font-size: 7px;
            word-wrap: break-word;
            overflow: hidden;
        }
        
        .bold { font-weight: 900; }
        .small { font-size: 8px; color: #333; }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            border-top: 1px solid #000;
            padding-top: 8px;
        }
        .pagenum:before {
            content: counter(page);
        }
        .page-footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            font-size: 8px;
            color: #333;
        }
        
        /* Signature Section */
        .signature-container { margin-top: 40px; width: 100%; page-break-inside: avoid; }
        .signature-box { float: right; width: 220px; text-align: center; }
        .signature-space { height: 50px; }
    </style>
</head>
<body>
    @php
        $siteSettingsModel = \App\Models\LandingPageSetting::where('key', 'site')->first();
        $siteSettings = $siteSettingsModel ? $siteSettingsModel->content : [
            'name' => 'LAUNDRYAN',
            'logo_url' => ''
        ];
    @endphp

    <div class="header-container">
        <div class="logo-box">
            @php
                $logoUrl = isset($siteSettings['logo_url']) ? $siteSettings['logo_url'] : '';
                $logoBase64 = '';
                
                if ($logoUrl) {
                    $relative = str_replace(['/storage/', 'storage/', url('/')], '', $logoUrl);
                    $relative = ltrim($relative, '/');
                    
                    $paths = [
                        storage_path('app/public/' . $relative),
                        public_path('storage/' . $relative),
                        public_path($relative),
                    ];
                    
                    foreach ($paths as $path) {
                        if (file_exists($path) && !is_dir($path)) {
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            break;
                        }
                    }
                }
            @endphp

            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo">
            @else
                <div style="font-weight: 900; font-size: 16px;">LAUNDRYAN</div>
            @endif
        </div>
        <div class="company-info">
            <div class="bold" style="font-size: 12px;">{{ strtoupper($siteSettings['name']) }}</div>
            <div class="small">Courier Digital Salary Statement</div>
            <div class="small">Authorized Recipient Copies Only</div>
        </div>
        <div class="report-title">
            <h1>SALARY REPORT</h1>
            <div class="bold">PERIOD: 
                @if($month === 'all' && $year === 'all')
                    ALL TIME
                @elseif($month === 'all')
                    YEAR {{ strtoupper($year) }}
                @elseif($year === 'all')
                    {{ strtoupper(\Carbon\Carbon::create(2026, $month)->format('F')) }} (ALL YEARS)
                @else
                    {{ strtoupper(\Carbon\Carbon::create($year, $month)->format('F Y')) }}
                @endif
            </div>
            <div class="small">PRINTED: {{ now()->timezone('Asia/Jakarta')->format('l, d/m/Y H:i') }} WIB</div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Courier details card -->
    <div class="emp-details">
        <div class="emp-details-title">Courier Information</div>
        <table class="emp-details-grid">
            <tr>
                <td width="15%" class="bold">Courier ID:</td>
                <td width="35%">EMP-{{ sprintf('%04d', $user->id) }}</td>
                <td width="15%" class="bold">Email:</td>
                <td width="35%">{{ $user->email }}</td>
            </tr>
            <tr>
                <td class="bold">Full Name:</td>
                <td>{{ $user->name }}</td>
                <td class="bold">Phone:</td>
                <td>{{ $user->phone ?: '-' }}</td>
            </tr>
            <tr>
                <td class="bold">Role:</td>
                <td><span class="bold" style="text-transform: uppercase;">{{ $user->role }}</span></td>
                <td class="bold">Stripe Link:</td>
                <td>{{ $user->stripe_account_id ?: 'Not connected' }}</td>
            </tr>
        </table>
    </div>

    <table class="summary-table">
        <tr>
            <td class="summary-cell">
                <div class="summary-label">Total Net Earnings</div>
                <div class="summary-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Total Payslips</div>
                <div class="summary-value">{{ $payrolls->count() }} Periods</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Withdrawn / Paid</div>
                <div class="summary-value" style="color: #10b981;">{{ $payrolls->where('status', 'paid')->count() }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Unpaid / Pending</div>
                <div class="summary-value" style="color: #f59e0b;">{{ $payrolls->where('status', 'pending')->count() }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Salary Ledger Details</div>
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">Payroll ID</th>
                <th width="14%">Period</th>
                <th width="13%">Base Salary</th>
                <th width="10%">Bonus</th>
                <th width="10%">Deductions</th>
                <th width="13%">Net Salary</th>
                <th width="8%">Status</th>
                <th width="8%">Method</th>
                <th width="18%">Reference / Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payrolls as $pay)
                @php
                    $netSalary = $pay->amount + $pay->bonus - $pay->potongan;
                    $periodStr = \Carbon\Carbon::create($pay->year, $pay->month, 1)->format('M Y');
                @endphp
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td align="center"><span class="bold">PAY-{{ sprintf('%04d', $pay->id) }}</span></td>
                    <td align="center">{{ $periodStr }}</td>
                    <td>Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                    <td style="color: #059669;">+Rp {{ number_format($pay->bonus, 0, ',', '.') }}</td>
                    <td style="color: #dc2626;">-Rp {{ number_format($pay->potongan, 0, ',', '.') }}</td>
                    <td><span class="bold">Rp {{ number_format($netSalary, 0, ',', '.') }}</span></td>
                    <td align="center">
                        <span class="bold" style="color: {{ $pay->status === 'paid' ? '#059669' : ($pay->status === 'failed' ? '#dc2626' : '#d97706') }};">
                            {{ strtoupper($pay->status) }}
                        </span>
                    </td>
                    <td align="center"><span class="bold">{{ strtoupper($pay->payment_method ?: '-') }}</span></td>
                    <td>
                        <span class="bold" style="font-family: monospace; font-size: 6px;">{{ $pay->stripe_transfer_id ?: '-' }}</span>
                        @if($pay->payment_date)
                            <div style="font-size: 5px; color: #666; margin-top: 1px;">Date: {{ \Carbon\Carbon::parse($pay->payment_date)->format('d/m/Y H:i') }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" align="center">No salary data recorded for this period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" align="right">Accumulated Net Earnings</th>
                <th colspan="4" align="left" style="background-color: #f2f2f2; border: 1px solid #000;">
                    <span class="bold" style="font-size: 7.5px;">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                </th>
            </tr>
        </tfoot>
    </table>

    <div class="signature-container">
        <div class="signature-box" style="float: left; text-align: center; margin-left: 30px;">
            <div class="small">Received by,</div>
            <div class="bold">Courier Signature</div>
            <div class="signature-space"></div>
            <div class="bold">____________________</div>
            <div class="small">{{ $user->name }}</div>
        </div>
        <div class="signature-box" style="margin-right: 30px;">
            <div class="small">Issued by,</div>
            <div class="bold">Laundryan Finance Division</div>
            <div class="signature-space"></div>
            <div class="bold">____________________</div>
            <div class="small">Digital Authorized Signature</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="page-footer">
        Page <span class="pagenum"></span> - {{ $siteSettings['name'] }} Salary Statement - Printed by EMP-{{ sprintf('%04d', $user->id) }}
    </div>
</body>
</html>
