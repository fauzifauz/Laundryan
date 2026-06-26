<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceController extends Controller
{
    /**
     * Build a base query with all active filters applied.
     */
    /**
     * Build a base query with all active filters applied.
     */
    private function buildBaseQuery(Request $request, $ignoreStatusFilters = false, $ignorePeriodFilters = false)
    {
        $query = Attendance::with('user');

        // --- Search (name or email, exact substring match) ---
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // --- Role filter ---
        $role = $request->input('role', '');
        if ($role && $role !== 'all') {
            $query->whereHas('user', fn($q) => $q->where('role', $role));
        } else {
            $query->whereHas('user', fn($q) => $q->whereIn('role', ['karyawan', 'kurir']));
        }

        if (!$ignoreStatusFilters) {
            // --- Attendance status filter ---
            $status = $request->input('status', '');
            if ($status && $status !== 'all') {
                if (str_contains($status, ',')) {
                    $query->whereIn('status', explode(',', $status));
                } else {
                    $query->where('status', $status);
                }
            }

            // --- Approval status filter ---
            $approval = $request->input('approval_status', '');
            if ($approval && $approval !== 'all') {
                $query->where('approval_status', $approval);
            }
        }

        if ($ignorePeriodFilters) {
            return $query;
        }

        // --- Period-based date filtering ---
        $month = $request->input('month');
        $year  = $request->input('year');

        if ($month || $year) {
            if ($month && $month !== 'all') {
                $query->whereMonth('date', $month);
            }
            if ($year && $year !== 'all') {
                $query->whereYear('date', $year);
            }
        } else {
            $period      = $request->input('period', 'monthly');
            $dateVal     = $request->input('date', now()->toDateString());
            $weekVal     = $request->input('week');           // format: "2026-W22"
            $filterMonth = $request->input('filter_month');   // format: "2026-05"
            $filterYear  = $request->input('filter_year', now()->year);

            if ($period === 'daily' && $request->filled('date')) {
                $query->whereDate('date', $dateVal);
            } elseif ($period === 'weekly' && $request->filled('week')) {
                try {
                    $weekStart = Carbon::parse($weekVal . '-1');
                    $weekEnd   = $weekStart->copy()->endOfWeek();
                    $query->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
                } catch (\Exception $e) {
                }
            } elseif ($period === 'monthly' && $request->filled('filter_month')) {
                $parsed = Carbon::parse($filterMonth . '-01');
                $query->whereYear('date', $parsed->year)->whereMonth('date', $parsed->month);
            } elseif ($period === 'yearly') {
                $query->whereYear('date', $filterYear);
            } else {
                $query->whereYear('date', now()->year)->whereMonth('date', now()->month);
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $baseQuery  = $this->buildBaseQuery($request, false);

        // Stats from full filtered result (ignoring status/approval filters so card totals remain accurate)
        // Calculated for ALL TIME (ignoring period filters)
        $allTimeQuery = $this->buildBaseQuery($request, true, true);
        $allTimeStats = [
            'total'            => (clone $allTimeQuery)->count(),
            'present'          => (clone $allTimeQuery)->whereIn('status', ['present', 'late'])->count(),
            'absent'           => (clone $allTimeQuery)->where('status', 'absent')->count(),
            'permit'           => (clone $allTimeQuery)->where('status', 'permit')->count(),
            'leave'            => (clone $allTimeQuery)->where('status', 'leave')->count(),
            'pending_approval' => (clone $allTimeQuery)->whereIn('status', ['permit', 'leave'])->where('approval_status', 'pending')->count(),
        ];

        // Calculated for the CURRENT RUNNING MONTH specifically
        $currentMonthQuery = $this->buildBaseQuery($request, true, true)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month);
        $currentMonthStats = [
            'total'            => (clone $currentMonthQuery)->count(),
            'present'          => (clone $currentMonthQuery)->whereIn('status', ['present', 'late'])->count(),
            'absent'           => (clone $currentMonthQuery)->where('status', 'absent')->count(),
            'permit'           => (clone $currentMonthQuery)->where('status', 'permit')->count(),
            'leave'            => (clone $currentMonthQuery)->where('status', 'leave')->count(),
            'pending_approval' => (clone $currentMonthQuery)->whereIn('status', ['permit', 'leave'])->where('approval_status', 'pending')->count(),
        ];

        // Keep alias for compatibility
        $stats = $allTimeStats;

        // Separate staff and courier with clones
        $roleFilter = $request->input('role', '');

        if ($roleFilter === 'karyawan') {
            $staffQuery   = (clone $baseQuery);
            $courierQuery = Attendance::whereRaw('1=0');
        } elseif ($roleFilter === 'kurir') {
            $staffQuery   = Attendance::whereRaw('1=0');
            $courierQuery = (clone $baseQuery);
        } else {
            $staffQuery   = (clone $baseQuery)->whereHas('user', fn($q) => $q->where('role', 'karyawan'));
            $courierQuery = (clone $baseQuery)->whereHas('user', fn($q) => $q->where('role', 'kurir'));
        }

        $karyawanAttendances = $staffQuery->latest('date')->latest('id')->paginate(10, ['*'], 'staff_page')->withQueryString();
        $kurirAttendances    = $courierQuery->latest('date')->latest('id')->paginate(10, ['*'], 'courier_page')->withQueryString();

        // Keep month/year for header export dropdowns
        $month = $request->input('month', 'all');
        $year  = $request->input('year', 'all');

        return view('admin.attendance.index', compact(
            'karyawanAttendances', 'kurirAttendances', 'allTimeStats', 'currentMonthStats', 'stats', 'month', 'year'
        ));
    }

    public function realtimeStats(Request $request)
    {
        $allTimeQuery = $this->buildBaseQuery($request, true, true);
        $allTime = [
            'total'   => (clone $allTimeQuery)->count(),
            'present' => (clone $allTimeQuery)->whereIn('status', ['present', 'late'])->count(),
            'absent'  => (clone $allTimeQuery)->where('status', 'absent')->count(),
            'permit'  => (clone $allTimeQuery)->whereIn('status', ['permit', 'leave'])->count(),
            'pending' => (clone $allTimeQuery)->whereIn('status', ['permit', 'leave'])->where('approval_status', 'pending')->count(),
        ];

        $currentMonthQuery = $this->buildBaseQuery($request, true, true)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month);
        $current = [
            'total'   => (clone $currentMonthQuery)->count(),
            'present' => (clone $currentMonthQuery)->whereIn('status', ['present', 'late'])->count(),
            'absent'  => (clone $currentMonthQuery)->where('status', 'absent')->count(),
            'permit'  => (clone $currentMonthQuery)->whereIn('status', ['permit', 'leave'])->count(),
            'pending' => (clone $currentMonthQuery)->whereIn('status', ['permit', 'leave'])->where('approval_status', 'pending')->count(),
        ];

        return response()->json([
            'all'     => $allTime,
            'current' => $current,
        ]);
    }

    public function approve(Attendance $attendance)
    {
        if (in_array($attendance->status, ['permit', 'leave'])) {
            $attendance->update(['approval_status' => 'approved', 'reject_reason' => null]);
            return redirect()->back()->with('success', 'Request approved successfully.');
        }
        return redirect()->back()->with('error', 'Only permit/leave requests can be approved.');
    }

    public function reject(Attendance $attendance, Request $request)
    {
        $request->validate(['reject_reason' => 'required|string|max:255']);
        if (in_array($attendance->status, ['permit', 'leave'])) {
            $attendance->update([
                'approval_status' => 'rejected',
                'reject_reason'   => $request->input('reject_reason'),
            ]);
            return redirect()->back()->with('success', 'Request rejected.');
        }
        return redirect()->back()->with('error', 'Only permit/leave requests can be rejected.');
    }

    /**
     * Build period label for exports.
     */
    private function getPeriodLabel(Request $request): string
    {
        $month = $request->input('month');
        $year  = $request->input('year');

        if ($month || $year) {
            $mLabel = ($month && $month !== 'all') ? Carbon::create(2026, $month)->format('F') : 'All Months';
            $yLabel = ($year && $year !== 'all') ? $year : 'All Years';
            if ($mLabel === 'All Months' && $yLabel === 'All Years') {
                return 'All Time';
            }
            return "{$mLabel} {$yLabel}";
        }

        $period      = $request->input('period', 'monthly');
        $dateVal     = $request->input('date', now()->toDateString());
        $weekVal     = $request->input('week', '');
        $filterMonth = $request->input('filter_month', now()->format('Y-m'));
        $filterYear  = $request->input('filter_year', now()->year);

        if ($period === 'daily' && $dateVal) {
            return 'Day ' . Carbon::parse($dateVal)->format('d F Y');
        } elseif ($period === 'weekly' && $weekVal) {
            try {
                $weekStart = Carbon::parse($weekVal . '-1');
                $weekEnd   = $weekStart->copy()->endOfWeek();
                return 'Week ' . $weekStart->format('d M') . ' - ' . $weekEnd->format('d M Y');
            } catch (\Exception $e) {
                return 'Weekly';
            }
        } elseif ($period === 'monthly' && $filterMonth) {
            return Carbon::parse($filterMonth . '-01')->format('F Y');
        } elseif ($period === 'yearly') {
            return 'Year ' . $filterYear;
        }
        return 'Month ' . now()->format('F Y');
    }

    public function exportPdf(Request $request)
    {
        $attendances = $this->buildBaseQuery($request)->latest('date')->latest('id')->get();
        $periodLabel = $this->getPeriodLabel($request);

        $pdf = Pdf::loadView('admin.exports.attendance_pdf', compact('attendances', 'periodLabel'));
        $slug = str_replace([' ', '/'], '_', strtolower($periodLabel));
        return $pdf->download("laundryan_attendance_{$slug}_" . date('Ymd') . ".pdf");
    }

    public function exportCsv(Request $request)
    {
        $attendances = $this->buildBaseQuery($request)->latest('date')->latest('id')->get();
        $periodLabel = $this->getPeriodLabel($request);
        $slug = str_replace([' ', '/'], '_', strtolower($periodLabel));

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"laundryan_attendance_{$slug}_" . date('Ymd') . ".csv\"",
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Date', 'Employee Name', 'Role', 'Check In', 'Check Out', 'Status', 'Location', 'Approval Status', 'Notes / Reject Reason']);

            $roleMap   = ['admin' => 'Admin', 'karyawan' => 'Employee', 'kurir' => 'Courier'];
            $statusMap = ['present' => 'Present', 'late' => 'Late', 'absent' => 'Absent', 'permit' => 'Permit', 'leave' => 'Leave'];

            foreach ($attendances as $i => $rec) {
                $approval = in_array($rec->status, ['permit', 'leave']) ? strtoupper($rec->approval_status) : '-';
                fputcsv($file, [
                    $i + 1,
                    Carbon::parse($rec->date)->format('Y-m-d'),
                    $rec->user->name,
                    $roleMap[$rec->user->role] ?? ucfirst($rec->user->role),
                    $rec->check_in  ?: '-',
                    $rec->check_out ?: '-',
                    $statusMap[$rec->status] ?? ucfirst($rec->status),
                    $rec->location_name ?: '-',
                    $approval,
                    $rec->reject_reason ?: '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
