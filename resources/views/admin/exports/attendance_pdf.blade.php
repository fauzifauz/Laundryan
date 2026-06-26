<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laundryan - Attendance Report</title>
    <style>
        @page {
            margin: 0.8cm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5px;
            color: #000;
            line-height: 1.3;
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
        .report-title h1 { margin: 0; font-size: 14px; font-weight: 900; }
        .clear { clear: both; }
        
        /* Summary Section */
        .summary-table {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #000;
            border-collapse: collapse;
        }
        .summary-cell {
            width: 20%;
            padding: 10px 5px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
        }
        .summary-label { 
            font-size: 7px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin-bottom: 4px;
            color: #555;
        }
        .summary-value { 
            font-size: 10px; 
            font-weight: 900; 
        }
 
        .section-title {
            font-size: 9px;
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
            padding: 4px 2px;
            text-align: left;
        }
        td {
            border: 1px solid #000;
            padding: 4px 2px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            font-size: 7px;
        }
        
        .bold { font-weight: 900; }
        .small { font-size: 7px; color: #333; }
        
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
            <div class="small">Digital Attendance & HR Management</div>
            <div class="small">Employee and Courier Presence Registry</div>
        </div>
        <div class="report-title">
            <h1>ATTENDANCE REPORT</h1>
            <div class="bold">PERIOD: {{ strtoupper($periodLabel) }}</div>
            <div class="small">PRINTED: {{ now()->timezone('Asia/Jakarta')->format('l, d/m/Y H:i') }} WIB</div>
        </div>
        <div class="clear"></div>
    </div>

    @php
        $total = $attendances->count();
        $present = $attendances->whereIn('status', ['present', 'late'])->count();
        $absent = $attendances->where('status', 'absent')->count();
        $permit = $attendances->where('status', 'permit')->count();
        $leave = $attendances->where('status', 'leave')->count();
    @endphp

    <table class="summary-table">
        <tr>
            <td class="summary-cell">
                <div class="summary-label">Total Records</div>
                <div class="summary-value">{{ $total }} Entries</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Total Present / Late</div>
                <div class="summary-value" style="color: #10b981;">{{ $present }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Total Absent</div>
                <div class="summary-value" style="color: #ef4444;">{{ $absent }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Total Permit</div>
                <div class="summary-value" style="color: #3b82f6;">{{ $permit }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Total Leave</div>
                <div class="summary-value" style="color: #8b5cf6;">{{ $leave }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Attendance Registry Sheet</div>
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Date</th>
                <th width="17%">Employee / Courier Name</th>
                <th width="8%">Role</th>
                <th width="7%">Check In</th>
                <th width="7%">Check Out</th>
                <th width="8%">Status</th>
                <th width="18%">Location</th>
                <th width="8%">Approval</th>
                <th width="14%">Notes / Reject Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $record)
                @php
                    $roleMap = ['admin' => 'Admin', 'karyawan' => 'Employee', 'kurir' => 'Courier'];
                    $statusMap = [
                        'present' => 'Present',
                        'late' => 'Late',
                        'absent' => 'Absent',
                        'permit' => 'Permit',
                        'leave' => 'Leave'
                    ];
                    $statusColors = [
                        'present' => '#059669',
                        'late' => '#d97706',
                        'absent' => '#dc2626',
                        'permit' => '#2563eb',
                        'leave' => '#7c3aed'
                    ];
                @endphp
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td align="center"><span class="bold">{{ \Carbon\Carbon::parse($record->date)->format('Y-m-d') }}</span></td>
                    <td><span class="bold">{{ $record->user->name }}</span></td>
                    <td align="center"><span class="small bold">{{ $roleMap[$record->user->role] ?? ucfirst($record->user->role) }}</span></td>
                    <td align="center" class="bold">{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('H:i') : '-' }}</td>
                    <td align="center" class="bold">{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('H:i') : '-' }}</td>
                    <td align="center">
                        <span class="bold" style="color: {{ $statusColors[$record->status] ?? '#333' }};">
                            {{ $statusMap[$record->status] ?? ucfirst($record->status) }}
                        </span>
                    </td>
                    <td>
                        @if($record->location_name)
                            {{ $record->location_name }}
                            @if($record->latitude && $record->longitude)
                                <div class="small" style="font-size: 7px; color: #666;">({{ $record->latitude }}, {{ $record->longitude }})</div>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td align="center">
                        @if(in_array($record->status, ['permit', 'leave']))
                            @if($record->approval_status === 'approved')
                                <span class="bold" style="color: #059669;">APPROVED</span>
                            @elseif($record->approval_status === 'rejected')
                                <span class="bold" style="color: #dc2626;">REJECTED</span>
                            @else
                                <span class="bold" style="color: #d97706;">PENDING</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{ $record->reject_reason ?: '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" align="center">No attendance records found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-container">
        <div class="signature-box" style="float: left; text-align: center; margin-left: 30px;">
            <div class="small">{{ now()->timezone('Asia/Jakarta')->format('l, j F Y') }}</div>
            <div class="bold">Acknowledged by,</div>
            <div class="bold">HRD Manager</div>
            <div class="signature-space"></div>
            <div class="bold">____________________</div>
        </div>
        <div class="signature-box" style="margin-right: 30px;">
            <div class="small">{{ now()->timezone('Asia/Jakarta')->format('l, j F Y') }}</div>
            <div class="bold">Prepared by,</div>
            <div class="bold">{{ auth()->user()->name ?? 'Admin Laundryan' }}</div>
            <div class="signature-space"></div>
            <div class="bold">____________________</div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="page-footer">
        Page <span class="pagenum"></span> - {{ $siteSettings['name'] }} Attendance Sheet - {{ now()->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
    </div>
</body>
</html>
