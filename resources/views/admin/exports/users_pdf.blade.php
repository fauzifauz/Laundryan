<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laundryan - User Data Report</title>
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
            width: 25%;
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
        
        .avatar-circle {
            width: 22px;
            height: 22px;
            line-height: 22px;
            border-radius: 50%;
            background-color: #e2e8f0;
            color: #4a5568;
            font-weight: bold;
            font-size: 9px;
            text-align: center;
            border: 1px solid #cbd5e1;
        }

        .status-badge {
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            text-transform: uppercase;
            font-size: 7px;
        }

        .status-active {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #059669;
        }

        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #dc2626;
        }
        
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
            <div class="small">System User Administration Report</div>
            <div class="small">Detailed Data Classified by User Roles</div>
        </div>
        <div class="report-title">
            <h1>USER DATA REPORT</h1>
            <div class="bold">PERIOD: {{ strtoupper($periodLabel) }}</div>
            <div class="small">PRINTED: {{ now()->timezone('Asia/Jakarta')->format('l, F j, Y H:i') }} WIB</div>
        </div>
        <div class="clear"></div>
    </div>

    @php
        $totalUsers = $users->count();
        $activeCount = $users->where('status', 'active')->count();
        $inactiveCount = $users->where('status', 'inactive')->count();
        
        $roleCounts = [
            'admin' => $users->where('role', 'admin')->count(),
            'karyawan' => $users->where('role', 'karyawan')->count(),
            'kurir' => $users->where('role', 'kurir')->count(),
            'pelanggan' => $users->where('role', 'pelanggan')->count(),
        ];
    @endphp

    <table class="summary-table">
        <tr>
            <td class="summary-cell">
                <div class="summary-label">Total Users</div>
                <div class="summary-value">{{ $totalUsers }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Active Accounts</div>
                <div class="summary-value" style="color: #059669;">{{ $activeCount }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Suspended Accounts</div>
                <div class="summary-value" style="color: #dc2626;">{{ $inactiveCount }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Role Distribution</div>
                <div class="summary-value" style="font-size: 8px; font-weight: normal; text-align: left; padding-left: 8px;">
                    • Admin: {{ $roleCounts['admin'] }} | Staff: {{ $roleCounts['karyawan'] }} <br>
                    • Courier: {{ $roleCounts['kurir'] }} | Customer: {{ $roleCounts['pelanggan'] }}
                </div>
            </td>
        </tr>
    </table>

    @php
        $roleSections = [
            'admin' => 'Administrators',
            'karyawan' => 'Staff',
            'kurir' => 'Couriers',
            'pelanggan' => 'Customers',
        ];
    @endphp

    @foreach($roleSections as $roleKey => $roleTitle)
        @php
            $roleUsers = $users->where('role', $roleKey);
        @endphp
        
        @if($roleUsers->count() > 0)
            <div class="section-title">{{ $roleTitle }} ({{ $roleUsers->count() }})</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 4%; text-align: center;">No</th>
                        <th style="width: 7%; text-align: center;">Photo</th>
                        <th style="width: 16%;">Full Name</th>
                        <th style="width: 22%;">Email</th>
                        <th style="width: 12%;">Phone Number</th>
                        <th style="width: 23%;">Address</th>
                        <th style="width: 7%; text-align: center;">Status</th>
                        <th style="width: 9%;">Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roleUsers->values() as $index => $user)
                        @php
                            $avatarBase64 = '';
                            if ($user->photo) {
                                $relative = str_replace(['/storage/', 'storage/', url('/')], '', $user->photo);
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
                                        $avatarBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                        break;
                                    }
                                }
                            }
                        @endphp
                        <tr>
                            <td align="center">{{ $index + 1 }}</td>
                            <td align="center">
                                @if($avatarBase64)
                                    <img src="{{ $avatarBase64 }}" style="width: 22px; height: 22px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1;">
                                @else
                                    <div class="avatar-circle">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td><span class="bold">{{ $user->name }}</span></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?: '-' }}</td>
                            <td>{{ $user->address ?: '-' }}</td>
                            <td align="center">
                                @if($user->status === 'active')
                                    <span class="status-badge status-active">Active</span>
                                @else
                                    <span class="status-badge status-inactive">Suspended</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    <div class="signature-container">
        <div class="signature-box" style="float: left; text-align: center; margin-left: 20px;">
            <div class="small">{{ now()->timezone('Asia/Jakarta')->format('l, F j, Y') }}</div>
            <div class="bold">Approved By,</div>
            <div class="bold">HR Manager</div>
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
        Page <span class="pagenum"></span> - {{ $siteSettings['name'] }} User Report - {{ now()->timezone('Asia/Jakarta')->format('Y-m-d H:i') }}
    </div>
</body>
</html>
