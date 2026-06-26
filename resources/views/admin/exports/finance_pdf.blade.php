<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laundryan - Financial Report</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.4;
        }
        .header-container {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }
        .logo-box { float: left; width: 25%; }
        .logo-box img { max-width: 100%; height: auto; max-height: 50px; filter: grayscale(100%); }
        .company-info { float: left; width: 45%; padding-left: 20px; }
        .report-title { float: right; width: 30%; text-align: right; }
        .report-title h1 { margin: 0; font-size: 16px; font-weight: 900; }
        .clear { clear: both; }
        
        /* Summary Section - Stable Table Style */
        .summary-table {
            width: 100%;
            margin-bottom: 25px;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .summary-cell {
            width: 25%;
            padding: 12px 5px;
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
            font-size: 13px; 
            font-weight: 900; 
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-top: 20px;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th {
            border: 1px solid #000;
            background-color: #f2f2f2;
            color: #000;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 8px;
            padding: 8px 5px;
            text-align: left;
        }
        td {
            border: 1px solid #000;
            padding: 8px 5px;
            vertical-align: top;
        }
        
        .bold { font-weight: 900; }
        .small { font-size: 8px; color: #333; }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 8px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .pagenum:before {
            content: counter(page);
        }
        .page-footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 8px;
            color: #333;
        }
        
        /* Signature Section */
        .signature-container { margin-top: 50px; width: 100%; page-break-inside: avoid; }
        .signature-box { float: right; width: 200px; text-align: center; }
        .signature-space { height: 60px; }
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
                <div style="font-weight: 900; font-size: 20px;">LAUNDRYAN</div>
            @endif
        </div>
        <div class="company-info">
            <div class="bold" style="font-size: 14px;">{{ strtoupper($siteSettings['name']) }}</div>
            <div class="small">Official Financial Report</div>
            <div class="small">Address: CMS Management System</div>
        </div>
        <div class="report-title">
            <h1>FINANCIAL REPORT</h1>
            <div class="bold">PERIOD: {{ isset($startDate) && $startDate && isset($endDate) && $endDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y') : strtoupper($period ?: 'ALL') }}</div>
            <div class="small">PRINTED: {{ now()->timezone('Asia/Jakarta')->format('l, d/m/Y H:i') }} WIB</div>
        </div>
        <div class="clear"></div>
    </div>

    <table class="summary-table">
        <tr>
            <td class="summary-cell">
                <div class="summary-label">Total Income</div>
                <div class="summary-value">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Total Expenses</div>
                <div class="summary-value">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Net Profit/Loss</div>
                <div class="summary-value" style="color: {{ $balance < 0 ? '#ff0000' : '#000' }}">Rp {{ number_format($balance, 0, ',', '.') }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Profit Margin</div>
                <div class="summary-value" style="color: {{ ($profitMargin ?? 0) < 0 ? '#ff0000' : '#000' }}">{{ $profitMargin ?? 0 }}%</div>
            </td>
        </tr>
    </table>

    <!-- Revenue Breakdown -->
    <div class="section-title">Revenue Breakdown (By Service)</div>
    <table>
        <thead>
            <tr>
                <th width="40">No</th>
                <th>Service Name</th>
                <th width="100">Percentage</th>
                <th width="150">Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($revenueByServiceData ?? [] as $index => $item)
                @php
                    $percentage = $totalIncome > 0 ? round(($item['revenue'] / $totalIncome) * 100, 1) : 0;
                @endphp
            <tr>
                <td align="center"><div class="bold">{{ $index + 1 }}</div></td>
                <td><div class="bold">{{ $item['name'] }}</div></td>
                <td align="center"><div class="small">{{ $percentage }}%</div></td>
                <td align="left"><div class="bold">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</div></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" align="center">No revenue data available.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Expense Breakdown -->
    <div class="section-title">Expense Breakdown (By Category)</div>
    <table>
        <thead>
            <tr>
                <th width="40">No</th>
                <th>Expense Category</th>
                <th width="100">Percentage</th>
                <th width="150">Total Expense</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expensePieData ?? [] as $index => $item)
                @php
                    $percentage = $totalExpense > 0 ? round(($item['amount'] / $totalExpense) * 100, 1) : 0;
                @endphp
            <tr>
                <td align="center"><div class="bold">{{ $index + 1 }}</div></td>
                <td><div class="bold">{{ $item['category'] }}</div></td>
                <td align="center"><div class="small">{{ $percentage }}%</div></td>
                <td align="left"><div class="bold">Rp {{ number_format($item['amount'], 0, ',', '.') }}</div></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" align="center">No expense data available.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title" style="margin-top: 30px;">Income History (Detailed)</div>
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="70">Date</th>
                <th width="90">Category</th>
                <th width="80">Method</th>
                <th>Description</th>
                <th width="100">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomeHistory as $item)
            <tr>
                <td align="center"><div class="bold">{{ $loop->iteration }}</div></td>
                <td><div class="bold">{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</div></td>
                <td><div class="bold">{{ $item->category }}</div></td>
                <td align="center"><div class="small bold" style="color: #2563eb;">{{ $item->payment_method ?: 'CASH' }}</div></td>
                <td><div class="small">{{ $item->description ?: '-' }}</div></td>
                <td align="left"><div class="bold">Rp {{ number_format($item->amount, 0, ',', '.') }}</div></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" align="center">No income records for this period.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" align="right" style="background-color: #f2f2f2; border: 1px solid #000;">TOTAL INCOME</th>
                <th align="left" style="background-color: #f2f2f2; border: 1px solid #000;">
                    <div class="bold">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
                </th>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">Expense History (Detailed)</div>
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="70">Date</th>
                <th width="90">Category</th>
                <th width="80">Method</th>
                <th>Description</th>
                <th width="100">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenseHistory as $item)
            <tr>
                <td align="center"><div class="bold">{{ $loop->iteration }}</div></td>
                <td><div class="bold">{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</div></td>
                <td><div class="bold">{{ $item->category }}</div></td>
                <td align="center"><div class="small bold" style="color: #e11d48;">{{ $item->payment_method ?: 'CASH' }}</div></td>
                <td><div class="small">{{ $item->description ?: '-' }}</div></td>
                <td align="left"><div class="bold">Rp {{ number_format($item->amount, 0, ',', '.') }}</div></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" align="center">No expense records for this period.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" align="right" style="background-color: #f2f2f2; border: 1px solid #000;">TOTAL EXPENSE</th>
                <th align="left" style="background-color: #f2f2f2; border: 1px solid #000;">
                    <div class="bold">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
                </th>
            </tr>
        </tfoot>
    </table>

    <div class="signature-container">
        <div class="signature-box" style="float: left; text-align: center; margin-left: 50px;">
            <div class="small">{{ now()->timezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}</div>
            <div class="bold">Acknowledged by,</div>
            <div class="bold">Owner / Manager</div>
            <div class="signature-space"></div>
            <div class="bold">____________________</div>
        </div>
        <div class="signature-box" style="margin-right: 50px;">
            <div class="small">{{ now()->format('d F Y') }}</div>
            <div class="bold">Prepared by,</div>
            <div class="bold">{{ auth()->user()->name ?? 'Admin Laundryan' }}</div>
            <div class="signature-space"></div>
            <div class="bold">____________________</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="page-footer">
        Page <span class="pagenum"></span> - {{ $siteSettings['name'] }} - {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
    </div>

    <div class="footer" style="border-top: none; margin-top: 20px;">
        This document is valid and generated automatically by the {{ $siteSettings['name'] }} management system.
    </div>
</body>
</html>
