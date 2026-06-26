<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Finance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['order.customer', 'order.service', 'order.itemType']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_code', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($qo) use ($search) {
                      $qo->where('order_code', 'like', "%{$search}%")
                         ->orWhereHas('customer', function ($qc) use ($search) {
                             $qc->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Base query for statistics (with search, method, and period filters, but WITHOUT status filter)
        $statsQuery = clone $query;

        // Status filter (only applied to the main query for table listing)
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Method filter
        if ($request->filled('method') && $request->input('method') !== 'all') {
            $query->where('payment_method', $request->input('method'));
            $statsQuery->where('payment_method', $request->input('method'));
        }

        // Period filters
        if ($request->filled('month')) {
            $query->whereMonth('payment_date', $request->input('month'));
            $statsQuery->whereMonth('payment_date', $request->input('month'));
        }
        if ($request->filled('year')) {
            $query->whereYear('payment_date', $request->input('year'));
            $statsQuery->whereYear('payment_date', $request->input('year'));
        }

        // Calculate statistics using the statsQuery (unaffected by the status filter)
        $stats = [
            'total_transactions' => (clone $statsQuery)->count(),
            'total_earnings'     => (clone $statsQuery)->where('status', 'success')->sum('amount'),
            'status_success'     => (clone $statsQuery)->where('status', 'success')->count(),
            'status_pending'     => (clone $statsQuery)->where('status', 'pending')->count(),
            'status_failed'      => (clone $statsQuery)->where('status', 'failed')->count(),
        ];

        // Paginated payments list
        $payments = $query->latest('payment_date')->paginate(10)->withQueryString();

        // Get unique years in database for year filter dropdown, fallback to current year
        $years = Payment::selectRaw('YEAR(payment_date) as year')
            ->whereNotNull('payment_date')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        if (empty($years)) {
            $years = [date('Y')];
        }

        return view('admin.payments.index', compact('payments', 'stats', 'years'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['order.customer', 'order.service', 'order.itemType']);

        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:pending,success,failed,refunded',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $status = $request->status;
        $oldStatus = $payment->status;

        // Update payment status and notes
        $payment->update([
            'status' => $status,
            'admin_notes' => $request->has('admin_notes') ? $request->admin_notes : $payment->admin_notes,
        ]);

        $order = $payment->order;

        if ($status === 'success') {
            // Approve payment
            $order->update([
                'payment_status' => 'paid',
            ]);

            // If order is in pending_payment status, advance it to waiting_pickup
            if ($order->status === 'pending_payment') {
                $order->update([
                    'status' => 'waiting_pickup',
                ]);

                // Create order status log
                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'waiting_pickup',
                    'user_id' => auth()->id(),
                ]);
            }

            // Create/Ensure income recorded in Finance
            $financeExists = Finance::where('type', 'income')
                ->where('description', 'like', "%{$order->order_code}%")
                ->exists();

            if (!$financeExists) {
                Finance::create([
                    'type' => 'income',
                    'amount' => $payment->amount,
                    'category' => 'Laundry Order',
                    'description' => "Payment Verified for Order {$order->order_code} (Code: {$payment->payment_code})",
                    'date' => now(),
                ]);
            }
        } elseif ($status === 'failed') {
            // Reject payment
            $order->update([
                'payment_status' => 'pending',
            ]);

            // If order was waiting_pickup, revert it to pending_payment
            if ($order->status === 'waiting_pickup') {
                $order->update([
                    'status' => 'pending_payment',
                ]);

                // Create status log
                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'pending_payment',
                    'user_id' => auth()->id(),
                ]);
            }

            // Remove finance record if it was successful before
            if ($oldStatus === 'success') {
                Finance::where('type', 'income')
                    ->where('description', 'like', "%{$order->order_code}%")
                    ->delete();
            }
        } elseif ($status === 'pending') {
            // Revert payment status
            $order->update([
                'payment_status' => 'pending',
            ]);

            // Revert order status if it was waiting_pickup
            if ($order->status === 'waiting_pickup') {
                $order->update([
                    'status' => 'pending_payment',
                ]);

                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'pending_payment',
                    'user_id' => auth()->id(),
                ]);
            }

            // Remove finance record if it was successful before
            if ($oldStatus === 'success') {
                Finance::where('type', 'income')
                    ->where('description', 'like', "%{$order->order_code}%")
                    ->delete();
            }
        } elseif ($status === 'refunded') {
            // Refund payment
            $order->update([
                'payment_status' => 'pending',
                'status' => 'cancelled',
            ]);

            // If it was successful before, delete the income record
            if ($oldStatus === 'success') {
                Finance::where('type', 'income')
                    ->where('description', 'like', "%{$order->order_code}%")
                    ->delete();
            }

            // Record the refund expense in Finance
            Finance::create([
                'type' => 'expense',
                'amount' => $payment->amount,
                'category' => 'Refund',
                'description' => "Refund Order {$order->order_code} (Code: {$payment->payment_code})",
                'payment_method' => $payment->payment_method,
                'date' => now(),
            ]);
        }

        $message = 'Payment status updated successfully.';
        if ($status === 'failed') {
            $message = 'Payment marked as failed successfully.';
        } elseif ($status === 'refunded') {
            $message = 'Refund processed successfully.';
        } elseif ($status === 'success') {
            $message = 'Payment approved successfully.';
        }

        return redirect()->back()
            ->with('success', $message)
            ->with('action_status', $status);
    }

    public function downloadInvoice(Payment $payment)
    {
        $order = $payment->order->load(['customer', 'service', 'itemType']);
        
        $pdf = Pdf::loadView('invoices.order', compact('order'));
        return $pdf->download("Invoice-{$order->order_code}.pdf");
    }

    public function exportPdf(Request $request)
    {
        $query = Payment::with(['order.customer', 'order.service', 'order.itemType']);

        // Period filters
        $month = $request->input('month');
        $year = $request->input('year');

        if ($request->filled('month')) {
            $query->whereMonth('payment_date', $month);
        }
        if ($request->filled('year')) {
            $query->whereYear('payment_date', $year);
        }

        $payments = $query->latest('payment_date')->get();

        // Calculate stats
        $totalTransactions = $payments->count();
        $totalEarnings = $payments->where('status', 'success')->sum('amount');
        $successCount = $payments->where('status', 'success')->count();
        $pendingCount = $payments->where('status', 'pending')->count();
        $failedCount = $payments->where('status', 'failed')->count();

        // Generate period label
        $periodLabel = 'All Time';
        if ($request->filled('month') && $request->filled('year')) {
            $periodLabel = Carbon::create()->month((int)$month)->format('F') . ' ' . $year;
        } elseif ($request->filled('month')) {
            $periodLabel = Carbon::create()->month((int)$month)->format('F');
        } elseif ($request->filled('year')) {
            $periodLabel = 'Year ' . $year;
        }

        $pdf = Pdf::loadView('admin.exports.payments_pdf', compact(
            'payments',
            'totalTransactions',
            'totalEarnings',
            'successCount',
            'pendingCount',
            'failedCount',
            'periodLabel'
        ));

        return $pdf->download("Laundryan-Payment-Report-{$periodLabel}.pdf");
    }

    public function exportCsv(Request $request)
    {
        $query = Payment::with(['order.customer', 'order.service', 'order.itemType']);

        // Period filters
        $month = $request->input('month');
        $year = $request->input('year');

        if ($request->filled('month')) {
            $query->whereMonth('payment_date', $month);
        }
        if ($request->filled('year')) {
            $query->whereYear('payment_date', $year);
        }

        $payments = $query->latest('payment_date')->get();

        // Calculate stats
        $totalTransactions = $payments->count();
        $totalEarnings = $payments->where('status', 'success')->sum('amount');

        $periodLabel = 'All Time';
        if ($request->filled('month') && $request->filled('year')) {
            $periodLabel = Carbon::create()->month((int)$month)->format('F') . ' ' . $year;
        } elseif ($request->filled('month')) {
            $periodLabel = Carbon::create()->month((int)$month)->format('F');
        } elseif ($request->filled('year')) {
            $periodLabel = 'Year ' . $year;
        }

        $filename = "laundryan_payments_report_" . date('Ymd') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($payments, $periodLabel, $totalTransactions, $totalEarnings) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['LAUNDRYAN - PAYMENT TRANSACTION REPORT']);
            fputcsv($file, ['Period', $periodLabel]);
            fputcsv($file, ['Printed At', now()->timezone('Asia/Jakarta')->format('l, d/m/Y H:i') . ' WIB']);
            fputcsv($file, []);

            // Summary Info
            fputcsv($file, ['== SUMMARY ==']);
            fputcsv($file, ['Total Transactions', $totalTransactions]);
            fputcsv($file, ['Total Earnings (Success)', 'Rp ' . number_format($totalEarnings, 0, ',', '.')]);
            fputcsv($file, []);

            // Data Table Headers
            fputcsv($file, ['No', 'Payment Code', 'Order Code', 'Payment Date', 'Customer Name', 'Phone', 'Payment Method', 'Amount', 'Status', 'Admin Notes']);

            foreach ($payments as $index => $payment) {
                fputcsv($file, [
                    $index + 1,
                    $payment->payment_code,
                    $payment->order->order_code,
                    $payment->payment_date ? $payment->payment_date->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-',
                    $payment->order->customer ? $payment->order->customer->name : 'Walk-In Guest',
                    $payment->order->customer ? $payment->order->customer->phone : '-',
                    strtoupper($payment->payment_method),
                    $payment->amount,
                    strtoupper($payment->status),
                    $payment->admin_notes ?: '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
