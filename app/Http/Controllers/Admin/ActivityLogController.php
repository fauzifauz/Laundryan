<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Search Filter (User name, email, order number, description)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reference_id', 'like', "%{$search}%");
            });
        }

        // Role Filter
        if ($request->filled('role') && $request->input('role') !== 'all') {
            $roleVal = $request->input('role');
            if ($roleVal === 'sistem' || $roleVal === 'system') {
                $query->whereIn('role', ['sistem', 'system']);
            } else {
                $query->where('role', $roleVal);
            }
        }

        // Category Filter
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        // Period Filter
        if ($request->filled('period') && $request->input('period') !== 'all') {
            $period = $request->input('period');
            if ($period === 'today') {
                if ($request->filled('filter_date')) {
                    $date = Carbon::parse($request->input('filter_date'));
                } else {
                    $date = Carbon::today();
                }
                $query->whereDate('created_at', $date);
            } elseif ($period === 'week') {
                if ($request->filled('filter_week')) {
                    $parts = explode('-W', $request->input('filter_week'));
                    if (count($parts) === 2) {
                        $year = (int)$parts[0];
                        $week = (int)$parts[1];
                        $date = Carbon::now()->setISODate($year, $week);
                        $query->whereBetween('created_at', [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()]);
                    } else {
                        $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    }
                } else {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                }
            } elseif ($period === 'month') {
                if ($request->filled('filter_month')) {
                    $parts = explode('-', $request->input('filter_month'));
                    if (count($parts) === 2) {
                        $year = (int)$parts[0];
                        $month = (int)$parts[1];
                        $query->whereMonth('created_at', $month)->whereYear('created_at', $year);
                    } else {
                        $query->whereMonth('created_at', Carbon::now()->month)
                              ->whereYear('created_at', Carbon::now()->year);
                    }
                } else {
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                }
            } elseif ($period === 'year') {
                if ($request->filled('filter_year')) {
                    $query->whereYear('created_at', $request->input('filter_year'));
                } else {
                    $query->whereYear('created_at', Carbon::now()->year);
                }
            }
        }

        // Clickable KPI Card support
        if ($request->input('activity_type') === 'failed_login') {
            $query->whereIn('activity_type', ['Login Gagal', 'Failed Login']);
        }

        if ($request->input('user_active') == 1) {
            $query->whereNotNull('user_id');
        }

        // Statistics
        $todayCount = ActivityLog::whereDate('created_at', Carbon::today())->count();
        $yesterdayCount = ActivityLog::whereDate('created_at', Carbon::yesterday())->count();
        $diffPercent = $yesterdayCount > 0 ? (($todayCount - $yesterdayCount) / $yesterdayCount) * 100 : 0;

        $failedLoginsToday = ActivityLog::whereIn('activity_type', ['Login Gagal', 'Failed Login'])
            ->whereDate('created_at', Carbon::today())
            ->count();
        $failedLoginsTotal = ActivityLog::whereIn('activity_type', ['Login Gagal', 'Failed Login'])->count();

        $activeUsersToday = ActivityLog::whereDate('created_at', Carbon::today())
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        $mostActiveCategoryRow = ActivityLog::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->first();
        $mostActiveCategory = $mostActiveCategoryRow ? $mostActiveCategoryRow->category : 'None Yet';

        $stats = [
            'today_count' => $todayCount,
            'diff_percent' => $diffPercent,
            'failed_logins_today' => $failedLoginsToday,
            'failed_logins_total' => $failedLoginsTotal,
            'active_users_today' => $activeUsersToday,
            'most_active_category' => $mostActiveCategory
        ];

        // Paginate logs
        $logs = $query->latest()->paginate(15)->withQueryString();

        // Get years available in logs
        $yearExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y', created_at) as year"
            : "YEAR(created_at) as year";

        $years = ActivityLog::selectRaw($yearExpression)
            ->whereNotNull('created_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        if (empty($years)) {
            $years = [date('Y')];
        }

        return view('admin.activity-logs.index', compact('logs', 'stats', 'years'));
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
            'year' => 'required|string',
        ]);

        $month = $request->month;
        $year = $request->year;

        if ($month !== 'all' && !is_numeric($month)) {
            abort(400, 'Invalid month');
        }
        if ($year !== 'all' && !is_numeric($year)) {
            abort(400, 'Invalid year');
        }

        $query = ActivityLog::with('user');

        if ($month !== 'all') {
            $query->whereMonth('created_at', $month);
        }
        if ($year !== 'all') {
            $query->whereYear('created_at', $year);
        }

        $logs = $query->latest()->get();

        $groupedLogs = [];
        foreach ($logs as $log) {
            $role = strtolower($log->role ?: 'system');
            if ($role === 'sistem') {
                $role = 'system';
            }
            $groupedLogs[$role][] = $log;
        }

        $roleOrder = ['admin', 'karyawan', 'kurir', 'pelanggan', 'system'];
        uksort($groupedLogs, function($a, $b) use ($roleOrder) {
            $posA = array_search($a, $roleOrder);
            $posB = array_search($b, $roleOrder);
            $posA = $posA === false ? 999 : $posA;
            $posB = $posB === false ? 999 : $posB;
            return $posA <=> $posB;
        });

        if ($month !== 'all' && $year !== 'all') {
            $periodLabel = Carbon::create()->month((int)$month)->format('F') . ' ' . $year;
        } elseif ($month !== 'all') {
            $periodLabel = Carbon::create()->month((int)$month)->format('F') . ' (All Years)';
        } elseif ($year !== 'all') {
            $periodLabel = 'All Months ' . $year;
        } else {
            $periodLabel = 'All Periods';
        }

        $pdf = Pdf::loadView('admin.exports.activity_logs_pdf', compact('groupedLogs', 'periodLabel', 'logs'));

        return $pdf->download("Laundryan-Activity-Logs-{$periodLabel}.pdf");
    }

    public function exportCsv(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
            'year' => 'required|string',
        ]);

        $month = $request->month;
        $year = $request->year;

        if ($month !== 'all' && !is_numeric($month)) {
            abort(400, 'Invalid month');
        }
        if ($year !== 'all' && !is_numeric($year)) {
            abort(400, 'Invalid year');
        }

        $query = ActivityLog::with('user');

        if ($month !== 'all') {
            $query->whereMonth('created_at', $month);
        }
        if ($year !== 'all') {
            $query->whereYear('created_at', $year);
        }

        $logs = $query->latest()->get();

        $groupedLogs = [];
        foreach ($logs as $log) {
            $role = strtolower($log->role ?: 'system');
            if ($role === 'sistem') {
                $role = 'system';
            }
            $groupedLogs[$role][] = $log;
        }

        $roleOrder = ['admin', 'karyawan', 'kurir', 'pelanggan', 'system'];
        uksort($groupedLogs, function($a, $b) use ($roleOrder) {
            $posA = array_search($a, $roleOrder);
            $posB = array_search($b, $roleOrder);
            $posA = $posA === false ? 999 : $posA;
            $posB = $posB === false ? 999 : $posB;
            return $posA <=> $posB;
        });

        if ($month !== 'all' && $year !== 'all') {
            $periodLabel = Carbon::create()->month((int)$month)->format('F') . ' ' . $year;
        } elseif ($month !== 'all') {
            $periodLabel = Carbon::create()->month((int)$month)->format('F') . ' (All Years)';
        } elseif ($year !== 'all') {
            $periodLabel = 'All Months ' . $year;
        } else {
            $periodLabel = 'All Periods';
        }

        $filename = "laundryan_activity_logs_" . strtolower(str_replace(' ', '_', $periodLabel)) . "_" . date('Ymd') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($groupedLogs, $logs, $periodLabel) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['LAUNDRYAN - ACTIVITY LOGS AUDIT TRAIL REPORT']);
            fputcsv($file, ['Period', $periodLabel]);
            fputcsv($file, ['Printed At', now()->timezone('Asia/Jakarta')->format('l, d/m/Y H:i') . ' WIB']);
            fputcsv($file, []);

            fputcsv($file, ['== SUMMARY ==']);
            fputcsv($file, ['Total Recorded Activities', $logs->count()]);
            fputcsv($file, []);

            $roleDisplayMap = [
                'admin' => 'ADMIN',
                'karyawan' => 'EMPLOYEE',
                'kurir' => 'COURIER',
                'pelanggan' => 'CUSTOMER',
                'system' => 'SYSTEM'
            ];

            foreach ($groupedLogs as $roleKey => $roleLogs) {
                $roleDisplay = $roleDisplayMap[$roleKey] ?? strtoupper($roleKey);
                fputcsv($file, ["== {$roleDisplay} ACTIVITIES == (Total: " . count($roleLogs) . " records)"]);
                // Headers
                fputcsv($file, [
                    'No', 'Timestamp', 'User Name', 'Email', 'Category', 
                    'Activity Type', 'Description', 'Module', 
                    'Reference ID', 'IP Address', 'Browser', 'Device', 'User Agent'
                ]);

                foreach ($roleLogs as $index => $log) {
                    fputcsv($file, [
                        $index + 1,
                        $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i:s') : '-',
                        $log->user_name ?: ($log->user ? $log->user->name : 'System'),
                        $log->email ?: ($log->user ? $log->user->email : '-'),
                        $log->category,
                        $log->activity_type,
                        $log->description,
                        $log->module ?: '-',
                        $log->reference_id ?: '-',
                        $log->ip_address ?: '-',
                        $log->browser ?: '-',
                        $log->device ?: '-',
                        $log->user_agent ?: '-'
                    ]);
                }
                fputcsv($file, []); // Add blank line between sections
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
