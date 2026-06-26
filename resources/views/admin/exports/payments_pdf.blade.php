<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laundryan - Payment Transaction Report</title>
    <style>
        @page {
            margin: 1cm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
            color: #000;
            line-height: 1.25;
        }
        .header-container {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .logo-box { float: left; width: 25%; }
        .logo-box img { max-width: 100%; height: auto; max-height: 40px; }
        .company-info { float: left; width: 45%; padding-left: 10px; }
        .report-title { float: right; width: 30%; text-align: right; }
        .report-title h1 { margin: 0; font-size: 13px; font-weight: 900; }
        .clear { clear: both; }
        
        /* Summary Section */
        .summary-table {
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .summary-cell {
            width: 20%;
            padding: 8px 4px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
        }
        .summary-label { 
            font-size: 7.5px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin-bottom: 3px;
            color: #444;
        }
        .summary-value { 
            font-size: 11px; 
            font-weight: 900; 
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            border-bottom: 1.5px solid #000;
            padding-bottom: 3px;
            margin-top: 15px;
            color: #005bc0;
            page-break-after: avoid;
        }

        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; 
            margin-top: 6px; 
            margin-bottom: 12px; 
            page-break-inside: auto;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        table.data-table th {
            border: 1px solid #000;
            background-color: #f2f2f2;
            color: #000;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 5px 3px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.data-table td {
            border: 1px solid #000;
            padding: 5px 3px;
            vertical-align: middle;
            font-size: 7.5px;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        
        .bold { font-weight: 900; }
        .small { font-size: 7.5px; color: #333; }
        
        .page-footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            height: 15px;
            text-align: center;
            font-size: 7.5px;
            color: #333;
        }
        .pagenum:before {
            content: counter(page);
        }
        
        /* Signature Section */
        .signature-container { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .signature-box { float: right; width: 180px; text-align: center; }
        .signature-space { height: 40px; }
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
                <div style="font-weight: 900; font-size: 15px; letter-spacing: -1px; color: #005bc0;">LAUNDRYAN</div>
            @endif
        </div>
        <div class="company-info">
            <div class="bold" style="font-size: 11px;">{{ strtoupper($siteSettings['name']) }}</div>
            <div class="small">Official Financial Transaction Report</div>
            <div class="small">Payment & Settlement Ledger</div>
        </div>
        <div class="report-title">
            <h1>PAYMENT REPORT</h1>
            <div class="bold">PERIOD: {{ strtoupper($periodLabel) }}</div>
            <div class="small">PRINTED: {{ now()->timezone('Asia/Jakarta')->format('l, F j, Y H:i') }} WIB</div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Summary Statistics Table -->
    <table class="summary-table">
        <tr>
            <td class="summary-cell">
                <div class="summary-label">Total Earnings (Success)</div>
                <div class="summary-value" style="color: #059669;">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Total Transactions</div>
                <div class="summary-value" style="color: #005bc0;">{{ $totalTransactions }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Successful Payments</div>
                <div class="summary-value" style="color: #059669;">{{ $successCount }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Pending Verification</div>
                <div class="summary-value" style="color: #d97706;">{{ $pendingCount }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Failed Payments</div>
                <div class="summary-value" style="color: #dc2626;">{{ $failedCount }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Payment Transaction Ledger ({{ $totalTransactions }} Records)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 15%;">Payment Date</th>
                <th style="width: 14%;">Payment Code</th>
                <th style="width: 12%;">Order Code</th>
                <th style="width: 18%;">Customer Name</th>
                <th style="width: 12%;">Method</th>
                <th style="width: 12%;">Amount</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $index => $payment)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td>
                        {{ $payment->payment_date ? $payment->payment_date->timezone('Asia/Jakarta')->format('Y-m-d H:i') : '-' }}
                    </td>
                    <td>
                        <span class="bold">{{ $payment->payment_code }}</span>
                    </td>
                    <td>
                        {{ $payment->order->order_code }}
                    </td>
                    <td>
                        <span class="bold">{{ $payment->order->customer ? $payment->order->customer->name : 'Walk-In Guest' }}</span><br>
                        <span class="small">{{ $payment->order->customer ? $payment->order->customer->phone : '-' }}</span>
                    </td>
                    <td>
                        {{ strtoupper($payment->payment_method) }}
                    </td>
                    <td>
                        <span class="bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        <span class="bold" style="color: {{ $payment->status === 'success' ? '#059669' : ($payment->status === 'pending' ? '#d97706' : '#dc2626') }};">
                            {{ strtoupper($payment->status) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-container">
        <div class="signature-box" style="float: left; text-align: center; margin-left: 20px;">
            <div class="small">{{ now()->timezone('Asia/Jakarta')->format('l, F j, Y') }}</div>
            <div class="bold">Acknowledged By,</div>
            <div class="bold">Finance Manager</div>
            <div class="signature-space"></div>
            <div class="bold">____________________</div>
        </div>
        <div class="signature-box" style="margin-right: 20px;">
            <div class="small">{{ now()->timezone('Asia/Jakarta')->format('l, F j, Y') }}</div>
            <div class="bold">Prepared By,</div>
            <div class="bold">{{ auth()->user()->name ?? 'Admin Laundryan' }}</div>
            <div class="signature-space"></div>
            <div class="bold">____________________</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="page-footer">
        Page <span class="pagenum"></span> - {{ $siteSettings['name'] }} Payment Transaction Report - {{ now()->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
    </div>
</body>
</html>
