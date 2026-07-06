<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class SalaryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Current Statement: last successfully withdrawn salary by employee
        $statementPayroll = Payroll::where('user_id', $user->id)
            ->where('status', 'paid')
            ->whereIn('payment_method', ['stripe', 'e-wallet'])
            ->orderByDesc('payment_date')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        $statementType = $statementPayroll ? 'last_withdrawn' : 'none';

        // Withdrawal Hub: all payrolls eligible for employee withdrawal
        $withdrawablePayrolls = Payroll::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Payroll History (all payrolls)
        $payrolls = Payroll::where('user_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Withdrawal History (payrolls that are paid and settled via Stripe or E-Wallet)
        $withdrawals = Payroll::where('user_id', $user->id)
            ->where('status', 'paid')
            ->whereIn('payment_method', ['stripe', 'e-wallet'])
            ->orderBy('payment_date', 'desc')
            ->get();

        return view('kurir.salary.index', compact(
            'statementPayroll',
            'statementType',
            'withdrawablePayrolls',
            'payrolls',
            'withdrawals'
        ));
    }

    public function withdraw(Request $request, Payroll $payroll)
    {
        $user = Auth::user();

        // Ensure user owns this payroll
        if ($payroll->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        if ($payroll->status !== 'pending') {
            return redirect()->back()->with('error', 'This payroll is not available for withdrawal.');
        }

        $request->validate([
            'payment_method' => 'required|in:stripe,e-wallet',
            'stripe_account_id' => 'required_if:payment_method,stripe|string|nullable',
            'ewallet_provider' => 'required_if:payment_method,e-wallet|in:dana,gopay,ovo,linkaja|nullable',
            'ewallet_phone' => 'required_if:payment_method,e-wallet|string|nullable',
        ]);

        if ($request->payment_method === 'stripe') {
            // Save Stripe connected account ID if provided and not already saved
            if ($request->filled('stripe_account_id') && $user->stripe_account_id !== $request->stripe_account_id) {
                $user->update(['stripe_account_id' => $request->stripe_account_id]);
            }

            $referenceId = 'WDL-STRIPE-' . strtoupper(Str::random(10));
            $methodLabel = 'stripe';
        } else {
            $referenceId = 'WDL-' . strtoupper($request->ewallet_provider) . '-' . strtoupper(Str::random(10));
            $methodLabel = 'e-wallet';
        }

        $payroll->update([
            'status' => 'paid',
            'payment_method' => $methodLabel,
            'stripe_transfer_id' => $referenceId,
            'payment_date' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Your salary has been withdrawn successfully.')
            ->with('toast_title', 'Salary Withdrawn Successfully');
    }

    public function downloadPayslipPdf(Payroll $payroll)
    {
        $user = Auth::user();

        if ($payroll->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $pdf = Pdf::loadView('kurir.exports.payslip_pdf', compact('payroll', 'user'))
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        $periodLabel = Carbon::create($payroll->year, $payroll->month, 1)->format('F_Y');

        return $pdf->download("laundryan_payslip_{$periodLabel}_PAY-" . sprintf('%04d', $payroll->id) . '.pdf');
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', 'all');
        $year = $request->get('year', 'all');

        $query = Payroll::where('user_id', $user->id);

        if ($month !== 'all') {
            $query->where('month', $month);
        }
        if ($year !== 'all') {
            $query->where('year', $year);
        }

        $payrolls = $query->orderBy('year', 'desc')->orderBy('month', 'desc')->get();
        $totalAmount = $payrolls->sum(function($p) {
            return $p->amount + $p->bonus - $p->potongan;
        });

        $pdf = Pdf::loadView('kurir.exports.salary_pdf', compact('payrolls', 'month', 'year', 'totalAmount', 'user'))
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

        return $pdf->download("laundryan_salary_report_{$periodLabel}_" . date('Ymd') . ".pdf");
    }

    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', 'all');
        $year = $request->get('year', 'all');

        $query = Payroll::where('user_id', $user->id);

        if ($month !== 'all') {
            $query->where('month', $month);
        }
        if ($year !== 'all') {
            $query->where('year', $year);
        }

        $payrolls = $query->orderBy('year', 'desc')->orderBy('month', 'desc')->get();
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

        $filename = "laundryan_salary_{$periodLabel}_" . date('Ymd') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($payrolls, $periodLabel, $totalAmount, $user) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['LAUNDRYAN - COURIER SALARY REPORT']);
            fputcsv($file, ['Employee Name', $user->name]);
            fputcsv($file, ['Role', 'Courier']);
            fputcsv($file, ['Period', $periodLabel]);
            fputcsv($file, ['Printed At', now()->timezone('Asia/Jakarta')->format('l, d/m/Y H:i') . ' WIB']);
            fputcsv($file, []);

            // Summary
            fputcsv($file, ['Total Net Salary Received', 'Rp ' . number_format($totalAmount, 0, ',', '.')]);
            fputcsv($file, ['Total Payslips', $payrolls->count()]);
            fputcsv($file, []);

            // Main Table
            fputcsv($file, [
                'No', 'Payroll ID', 'Period', 'Base Salary', 'Bonus', 'Deductions', 'Net Salary', 
                'Status', 'Payment Method', 'Payment Date', 'Withdrawal Reference'
            ]);

            foreach ($payrolls as $index => $pay) {
                $netSalary = $pay->amount + $pay->bonus - $pay->potongan;
                $periodStr = Carbon::create($pay->year, $pay->month, 1)->format('F Y');
                fputcsv($file, [
                    $index + 1,
                    'PAY-' . sprintf('%04d', $pay->id),
                    $periodStr,
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
