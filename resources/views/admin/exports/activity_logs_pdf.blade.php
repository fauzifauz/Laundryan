<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laundryan - System Activity Logs Audit Trail Report</title>
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
        .report-title h1 { margin: 0; font-size: 12px; font-weight: 900; }
        .clear { clear: both; }
        
        /* Summary Section */
        .summary-table {
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .summary-cell {
            width: 50%;
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
            font-size: 9px;
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
            font-size: 7px;
            padding: 5px 3px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.data-table td {
            border: 1px solid #000;
            padding: 5px 3px;
            vertical-align: middle;
            font-size: 7px;
            word-wrap: break-word;
            word-break: break-all;
            overflow-wrap: break-word;
        }
        
        .bold { font-weight: 900; }
        .small { font-size: 7.5px; color: #333; }
        .meta-text { font-size: 6.5px; color: #555; margin-top: 2px; }
        
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
            <div class="small">System Audit Trail & Security Ledger</div>
            <div class="small">Compliance & Activity Tracking</div>
        </div>
        <div class="report-title">
            <h1>ACTIVITY LOG REPORT</h1>
            <div class="bold">PERIOD: {{ strtoupper($periodLabel) }}</div>
            <div class="small">PRINTED: {{ now()->timezone('Asia/Jakarta')->format('l, F j, Y H:i') }} WIB</div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Summary Statistics Table -->
    <table class="summary-table">
        <tr>
            <td class="summary-cell" style="width: 50%;">
                <div class="summary-label">Total Recorded Activities</div>
                <div class="summary-value" style="color: #005bc0;">{{ $logs->count() }}</div>
            </td>
            <td class="summary-cell" style="width: 50%;">
                <div class="summary-label">Target Audit Period</div>
                <div class="summary-value" style="color: #059669;">{{ strtoupper($periodLabel) }}</div>
            </td>
        </tr>
    </table>

    @php
        $roleDisplayMap = [
            'admin' => 'ADMIN',
            'karyawan' => 'EMPLOYEE',
            'kurir' => 'COURIER',
            'pelanggan' => 'CUSTOMER',
            'system' => 'SYSTEM'
        ];
    @endphp

    @foreach($groupedLogs as $roleKey => $roleLogs)
        @php
            $roleDisplay = $roleDisplayMap[$roleKey] ?? strtoupper($roleKey);
        @endphp
        <div class="section-title">{{ $roleDisplay }} ACTIVITIES ({{ count($roleLogs) }} Records)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No</th>
                    <th style="width: 14%;">Timestamp</th>
                    <th style="width: 18%;">User Details</th>
                    <th style="width: 13%;">Category</th>
                    <th style="width: 13%;">Activity Type</th>
                    <th style="width: 25%;">Description</th>
                    <th style="width: 12%;">Client Metadata</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roleLogs as $index => $log)
                    <tr>
                        <td align="center">{{ $index + 1 }}</td>
                        <td>
                            {{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') : '-' }}
                        </td>
                        <td>
                            <span class="bold">{{ $log->user_name ?: 'System' }}</span>
                            <div class="meta-text">{{ $log->email ?: '-' }}</div>
                        </td>
                        <td>
                            {{ $log->category }}
                        </td>
                        <td>
                            <span class="bold">{{ $log->activity_type }}</span>
                        </td>
                        <td>
                            {{ $log->description }}
                        </td>
                        <td>
                            <span class="bold">{{ $log->ip_address ?: '-' }}</span>
                            <div class="meta-text">{{ $log->browser ?: '-' }} / {{ $log->device ?: '-' }}</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="signature-container">
        <div class="signature-box" style="float: left; text-align: center; margin-left: 20px;">
            <div class="small">{{ now()->timezone('Asia/Jakarta')->format('l, F j, Y') }}</div>
            <div class="bold">Acknowledged By,</div>
            <div class="bold">Operations Manager</div>
            <div class="signature-space"></div>
            <div class="bold">____________________</div>
        </div>
        <div class="signature-box" style="margin-right: 20px;">
            <div class="small">{{ now()->timezone('Asia/Jakarta')->format('l, F j, Y') }}</div>
            <div class="bold">Audited By,</div>
            <div class="bold">{{ auth()->user()->name ?? 'System Administrator' }}</div>
            <div class="signature-space"></div>
            <div class="bold">____________________</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="page-footer">
        Page <span class="pagenum"></span> - {{ $siteSettings['name'] }} System Activity Logs Report - {{ now()->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
    </div>
</body>
</html>
