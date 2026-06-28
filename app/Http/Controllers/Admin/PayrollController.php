<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Payroll;
use App\Models\User;
use App\Models\Order;
use App\Services\AttendancePayrollService;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

use Stripe\Stripe;
use Stripe\Transfer;

class PayrollController extends Controller
{
    public function index()
    {
        $month = request('month', now()->month);
        $year = request('year', now()->year);
        $status = request('status');
        $role = request('role');
        $search = request('search');

        // Preserve 'all' values if explicitly requested
        if (request()->has('month')) {
            $month = request('month');
        }
        if (request()->has('year')) {
            $year = request('year');
        }

        $chartYear = request('chart_year', $year);

        // Main Query with filters for active payroll list
        $query = Payroll::with('user');

        if ($month !== 'all') {
            $query->where('month', $month);
        }
        if ($year !== 'all') {
            $query->where('year', $year);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($role) {
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->orderBy('created_at', 'desc')->get();

        // Statistics Cards (KPIs) based on current selection, ignoring status filter to keep stats consistent
        $statsQuery = Payroll::with('user');
        if ($month !== 'all') {
            $statsQuery->where('month', $month);
        }
        if ($year !== 'all') {
            $statsQuery->where('year', $year);
        }
        if ($role) {
            $statsQuery->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }
        if ($search) {
            $statsQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        $statsPayrolls = $statsQuery->get();

        $totalPayroll = $statsPayrolls->sum(function($p) {
            return $p->amount + $p->bonus - $p->potongan;
        });
        
        $paidEmployees = $statsPayrolls->where('status', 'paid')->count();
        $totalEmployeesCount = $statsPayrolls->count();
        $successfulTransactions = $statsPayrolls->where('status', 'paid')->count();
        $pendingTransactions = $statsPayrolls->where('status', 'pending')->count();

        // Chart Data (monthly expenses for the selected year, or yearly if 'all')
        $chartLabels = [];
        $chartExpenses = [];
        if ($chartYear === 'all') {
            $years = Payroll::select('year')->distinct()->orderBy('year', 'asc')->pluck('year')->toArray();
            if (empty($years)) {
                $years = [now()->year];
            }
            foreach ($years as $y) {
                $chartLabels[] = (string)$y;
                $chartExpenses[] = Payroll::where('year', $y)
                    ->get()
                    ->sum(function($p) {
                        return $p->amount + $p->bonus - $p->potongan;
                    });
            }
        } else {
            for ($m = 1; $m <= 12; $m++) {
                $chartLabels[] = Carbon::create($chartYear, $m)->format('M');
                $chartExpenses[] = Payroll::where('year', $chartYear)
                    ->where('month', $m)
                    ->get()
                    ->sum(function($p) {
                        return $p->amount + $p->bonus - $p->potongan;
                    });
            }
        }
        $chartData = [
            'labels' => $chartLabels,
            'data' => $chartExpenses
        ];

        // Historical paid payrolls (filter synchronously with name search, month, and year)
        $historyQuery = Payroll::with('user')->where('status', 'paid');
        
        if ($month !== 'all') {
            $historyQuery->where('month', $month);
        }
        if ($year !== 'all') {
            $historyQuery->where('year', $year);
        }
        if ($role) {
            $historyQuery->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }
        if ($search) {
            $historyQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $historyPayrolls = $historyQuery->orderBy('payment_date', 'desc')->get();

        // Staff members for manual payroll creation
        $staffMembers = User::whereIn('role', ['karyawan', 'kurir'])->orderBy('name', 'asc')->get();

        return view('admin.payroll.index', compact(
            'payrolls', 'month', 'year', 'status', 'role', 'search',
            'totalPayroll', 'paidEmployees', 'totalEmployeesCount', 'successfulTransactions', 'pendingTransactions',
            'chartData', 'historyPayrolls', 'staffMembers', 'chartYear'
        ));
    }

    public function generate(Request $request)
    {
        $month = $request->month;
        $year = $request->year;

        $employees = User::whereIn('role', ['karyawan', 'kurir'])->get();
        $generatedCount = 0;

        foreach ($employees as $employee) {
            // Check if already generated
            $exists = Payroll::where('user_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->exists();

            if (!$exists) {
                // Calculation Logic
                $base = ($employee->role === 'karyawan') ? 2500000 : 2000000;
                
                // 1. Calculate Order Bonuses (Filter by specific Month & Year)
                if ($employee->role === 'karyawan') {
                    // Karyawan gets a team performance bonus of Rp 5.000 for each order completed in the store this month
                    $ordersCount = Order::where('status', 'completed')
                        ->whereMonth('updated_at', $month)
                        ->whereYear('updated_at', $year)
                        ->count();
                    $orderBonus = $ordersCount * 5000;
                } else {
                    // Courier gets Rp 10.000 for each completed delivery/pickup they handled
                    $ordersCount = Order::where('status', 'completed')
                        ->whereMonth('updated_at', $month)
                        ->whereYear('updated_at', $year)
                        ->where(function ($q) use ($employee) {
                            $q->where('courier_id', $employee->id)
                              ->orWhere('pickup_courier_id', $employee->id)
                              ->orWhere('delivery_courier_id', $employee->id);
                        })
                        ->count();
                    $orderBonus = $ordersCount * 10000;
                }

                // 2. Calculate Attendance-based Incentives & Deductions
                $attendanceBonus = 0;
                $potongan = 0;
                
                // Check if employee has ever logged any attendance in the system (adopted attendance feature)
                $usesAttendance = \App\Models\Attendance::where('user_id', $employee->id)->exists();
                if ($usesAttendance) {
                    $approvedAttendance = \App\Models\Attendance::where('user_id', $employee->id)
                        ->whereMonth('date', $month)
                        ->whereYear('date', $year)
                        ->where('approval_status', 'approved')
                        ->count();

                    $rejectedAttendance = \App\Models\Attendance::where('user_id', $employee->id)
                        ->whereMonth('date', $month)
                        ->whereYear('date', $year)
                        ->where('approval_status', 'rejected')
                        ->count();

                    // Attendance incentive: Rp 15.000 per approved check-in
                    $attendanceBonus = $approvedAttendance * 15000;

                    // Deduction for rejected attendance (invalid checkout, late, etc.): Rp 25.000 per incident
                    $potongan += $rejectedAttendance * 25000;

                    // Proportional absence deduction (assuming 20 standard working days)
                    if ($approvedAttendance < 20) {
                        $potongan += (20 - $approvedAttendance) * 50000;
                    }
                    
                    // Cap maximum deduction at 50% of base salary to protect worker basic income
                    $potongan = min($potongan, $base * 0.5);

                    $alphaInfo = AttendancePayrollService::calculateAlphaDeduction(
                        $employee->id,
                        (int) $month,
                        (int) $year,
                        (float) $base,
                        (float) ($orderBonus + $attendanceBonus),
                        (float) $potongan
                    );

                    $potongan += $alphaInfo['deduction'];
                    $potongan = min($potongan, $base * 0.5);
                } else {
                    $alphaInfo = ['count' => 0, 'deduction' => 0];
                }

                $bonus = $orderBonus + $attendanceBonus;

                Payroll::create([
                    'user_id'  => $employee->id,
                    'amount'   => $base,
                    'bonus'    => $bonus,
                    'potongan' => $potongan,
                    'alpha_count' => $alphaInfo['count'] ?? 0,
                    'alpha_deduction' => $alphaInfo['deduction'] ?? 0,
                    'month'    => $month,
                    'year'     => $year,
                    'status'   => 'pending'
                ]);
                $generatedCount++;
            }
        }

        if ($generatedCount > 0) {
            return redirect()->back()->with('success', 'Payroll generated for ' . Carbon::create($year, $month)->format('F Y'));
        }

        return redirect()->back()->with('warning', 'Payroll for ' . Carbon::create($year, $month)->format('F Y') . ' has already been generated.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'status' => 'required|string|in:pending,paid,failed',
            'payment_method' => 'nullable|string',
            'stripe_transfer_id' => 'nullable|string',
        ]);

        $payroll = Payroll::where('user_id', $request->user_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($payroll) {
            $employeeName = $payroll->user ? $payroll->user->name : 'Staff';
            $period = Carbon::create($request->year, $request->month)->format('F Y');
            return redirect()->back()
                ->with('warning', 'Payroll record for ' . $employeeName . ' in ' . $period . ' already exists.')
                ->with('toast_title', 'Payroll Already Exists');
        }

        $payroll = Payroll::create([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'bonus' => $request->bonus ?: 0,
            'potongan' => $request->potongan ?: 0,
            'month' => $request->month,
            'year' => $request->year,
            'status' => $request->status,
            'payment_method' => $request->payment_method,
            'stripe_transfer_id' => $request->stripe_transfer_id,
            'payment_date' => $request->status === 'paid' ? now() : null,
        ]);

        if ($request->status === 'paid') {
            $netAmount = $payroll->amount + $payroll->bonus - $payroll->potongan;
            \App\Models\Finance::create([
                'type' => 'expense',
                'amount' => $netAmount,
                'category' => 'Payroll',
                'description' => 'Salary payment for ' . $payroll->user->name . ' (' . Carbon::create($payroll->year, $payroll->month)->format('M Y') . ')',
                'payment_method' => $request->payment_method ?: 'CASH',
                'date' => now(),
            ]);
        }

        $payroll->load('user');
        $employeeName = $payroll->user ? $payroll->user->name : 'Staff';
        $netSalaryValue = $payroll->amount + $payroll->bonus - $payroll->potongan;
        $netSalary = 'Rp ' . number_format($netSalaryValue, 0, '.', ',');
        $period = Carbon::create($payroll->year, $payroll->month)->format('F Y');
        $createdAt = Carbon::parse($payroll->created_at)->format('d F Y');

        return redirect()->back()
            ->with('success', 'Payroll record manually created successfully.')
            ->with('new_payroll_created', true)
            ->with('new_payroll_employee', $employeeName)
            ->with('new_payroll_period', $period)
            ->with('new_payroll_net_salary', $netSalary)
            ->with('new_payroll_created_at', $createdAt);
    }

    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return redirect()->back()->with('error', 'Paid payroll records cannot be modified.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'bonus' => 'required|numeric|min:0',
            'potongan' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,paid,failed',
            'payment_method' => 'nullable|string',
            'stripe_transfer_id' => 'nullable|string',
        ]);

        $oldAmount = (float)$payroll->amount;
        $oldBonus = (float)$payroll->bonus;
        $oldPotongan = (float)$payroll->potongan;
        $oldStatus = $payroll->status;
        $oldPaymentMethod = $payroll->payment_method ?: '';
        $oldStripeTransferId = $payroll->stripe_transfer_id ?: '';

        $newAmount = (float)$request->amount;
        $newBonus = (float)$request->bonus;
        $newPotongan = (float)$request->potongan;
        $newStatus = $request->status;
        $newPaymentMethod = $request->payment_method ?: '';
        $newStripeTransferId = $request->stripe_transfer_id ?: '';

        if (
            $oldAmount === $newAmount &&
            $oldBonus === $newBonus &&
            $oldPotongan === $newPotongan &&
            $oldStatus === $newStatus &&
            $oldPaymentMethod === $newPaymentMethod &&
            $oldStripeTransferId === $newStripeTransferId
        ) {
            return redirect()->back()
                ->with('warning', 'No changes detected. Payroll details are already up-to-date.')
                ->with('toast_title', 'No Changes Made');
        }

        $oldStatus = $payroll->status;
        $netAmount = $request->amount + $request->bonus - $request->potongan;

        $payroll->update([
            'amount' => $request->amount,
            'bonus' => $request->bonus,
            'potongan' => $request->potongan,
            'status' => $request->status,
            'payment_method' => $request->payment_method ?: $payroll->payment_method,
            'stripe_transfer_id' => $request->stripe_transfer_id ?: $payroll->stripe_transfer_id,
            'payment_date' => $request->status === 'paid' ? ($payroll->payment_date ?: now()) : null,
        ]);

        // If status changed to paid, automatically log as finance expense
        if ($oldStatus !== 'paid' && $request->status === 'paid') {
            \App\Models\Finance::create([
                'type' => 'expense',
                'amount' => $netAmount,
                'category' => 'Payroll',
                'description' => 'Salary payment for ' . $payroll->user->name . ' (' . Carbon::create($payroll->year, $payroll->month)->format('M Y') . ')',
                'payment_method' => $request->payment_method ?: 'CASH',
                'date' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Payroll details updated successfully.');
    }

    public function payout(Payroll $payroll)
    {
        $user = $payroll->user;

        if (!$user->stripe_account_id) {
            return redirect()->back()->with('error', 'Employee has no Stripe Account ID linked.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $netAmount = $payroll->amount + $payroll->bonus - $payroll->potongan;

            $transfer = Transfer::create([
                'amount' => (int) ($netAmount * 100), // Convert to lowest denom (cents/IDR)
                'currency' => 'idr',
                'destination' => $user->stripe_account_id,
                'description' => 'Salary payout for ' . $user->name . ' (' . Carbon::create($payroll->year, $payroll->month)->format('M Y') . ')',
            ]);

            $payroll->update([
                'status' => 'paid',
                'payment_method' => 'stripe',
                'payment_date' => now(),
                'stripe_transfer_id' => $transfer->id
            ]);

            // Record as Expense
            \App\Models\Finance::create([
                'type' => 'expense',
                'amount' => $netAmount,
                'category' => 'Payroll',
                'description' => 'Salary payout for ' . $user->name . ' (' . Carbon::create($payroll->year, $payroll->month)->format('M Y') . ')',
                'payment_method' => 'stripe',
                'date' => now(),
            ]);

            return redirect()->back()->with('success', 'Payout successful via Stripe.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Stripe error: ' . $e->getMessage());
        }
    }

    public function payoutCash(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return redirect()->back()->with('error', 'This payroll has already been paid.');
        }

        $user = $payroll->user;
        $netAmount = $payroll->amount + $payroll->bonus - $payroll->potongan;

        $payroll->update([
            'status'             => 'paid',
            'payment_method'     => 'cash',
            'payment_date'       => now(),
            'stripe_transfer_id' => 'CASH-' . strtoupper(uniqid()),
        ]);

        // Automatically record as Expense in Finance
        \App\Models\Finance::create([
            'type'        => 'expense',
            'amount'      => $netAmount,
            'category'    => 'Payroll',
            'description' => 'Cash salary payment for ' . $user->name . ' (' . Carbon::create($payroll->year, $payroll->month)->format('M Y') . ')',
            'payment_method'=> 'cash',
            'date'        => now(),
        ]);

        return redirect()->back()->with('success', 'Cash payout for ' . $user->name . ' recorded successfully.');
    }

    public function exportPdf(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $status = $request->get('status');
        $role = $request->get('role');
        $search = $request->get('search');

        $query = Payroll::with('user');

        if ($month !== 'all') {
            $query->where('month', $month);
        }
        if ($year !== 'all') {
            $query->where('year', $year);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($role) {
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->orderBy('created_at', 'desc')->get();
        $totalAmount = $payrolls->sum(function($p) {
            return $p->amount + $p->bonus - $p->potongan;
        });

        $pdf = Pdf::loadView('admin.exports.payroll_pdf', compact('payrolls', 'month', 'year', 'totalAmount'))
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        if ($month === 'all' && $year === 'all') {
            $periodLabel = 'All Time';
        } elseif ($month === 'all') {
            $periodLabel = 'Year ' . $year;
        } elseif ($year === 'all') {
            $periodLabel = Carbon::create(2026, $month)->format('F') . ' (All Years)';
        } else {
            $periodLabel = Carbon::create($year, $month)->format('F Y');
        }

        return $pdf->download("laundryan_payroll_{$periodLabel}_" . date('Ymd') . ".pdf");
    }

    public function exportCsv(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $status = $request->get('status');
        $role = $request->get('role');
        $search = $request->get('search');

        $query = Payroll::with('user');

        if ($month !== 'all') {
            $query->where('month', $month);
        }
        if ($year !== 'all') {
            $query->where('year', $year);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($role) {
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->orderBy('created_at', 'desc')->get();
        $totalAmount = $payrolls->sum(function($p) {
            return $p->amount + $p->bonus - $p->potongan;
        });

        if ($month === 'all' && $year === 'all') {
            $periodLabel = 'All Time';
        } elseif ($month === 'all') {
            $periodLabel = 'Year ' . $year;
        } elseif ($year === 'all') {
            $periodLabel = Carbon::create(2026, $month)->format('F') . ' (All Years)';
        } else {
            $periodLabel = Carbon::create($year, $month)->format('F Y');
        }

        $filename = "laundryan_payroll_{$periodLabel}_" . date('Ymd') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($payrolls, $periodLabel, $totalAmount) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['LAUNDRYAN - PAYROLL REPORT']);
            fputcsv($file, ['Period', $periodLabel]);
            fputcsv($file, ['Printed At', now()->timezone('Asia/Jakarta')->format('l, d/m/Y H:i') . ' WIB']);
            fputcsv($file, []);

            // Summary
            fputcsv($file, ['Total Payroll Amount', 'Rp ' . number_format($totalAmount, 0, ',', '.')]);
            fputcsv($file, ['Total Employees', $payrolls->count()]);
            fputcsv($file, []);

            // Main Table (translated to English and removed NIK)
            fputcsv($file, [
                'No', 'Employee ID', 'Employee Name', 'Role', 'Employee Type', 
                'Base Salary', 'Bonus', 'Deductions', 'Net Salary', 
                'Status', 'Payment Method', 'Payout Date', 'Transaction Reference'
            ]);

            foreach ($payrolls as $index => $pay) {
                $roleMap = ['admin' => 'Admin', 'karyawan' => 'Employee', 'kurir' => 'Courier', 'pelanggan' => 'Customer'];
                $netSalary = $pay->amount + $pay->bonus - $pay->potongan;
                $empId = $pay->user->role === 'kurir' ? 'CUR-' . sprintf('%04d', $pay->user->id) : 'EMP-' . sprintf('%04d', $pay->user->id);
                fputcsv($file, [
                    $index + 1,
                    $empId,
                    $pay->user->name,
                    $roleMap[$pay->user->role] ?? ucfirst($pay->user->role),
                    $pay->user->role === 'kurir' ? 'Courier' : 'Employee',
                    'Rp ' . number_format($pay->amount, 0, ',', '.'),
                    'Rp ' . number_format($pay->bonus, 0, ',', '.'),
                    'Rp ' . number_format($pay->potongan, 0, ',', '.'),
                    'Rp ' . number_format($netSalary, 0, ',', '.'),
                    strtoupper($pay->status),
                    strtoupper($pay->payment_method ?: '-'),
                    $pay->payment_date ? Carbon::parse($pay->payment_date)->format('d/m/Y H:i') : '-',
                    $pay->stripe_transfer_id ?: '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
