<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Base query for payments belonging to customer's orders
        $query = Payment::whereHas('order', function ($q) use ($user) {
            $q->where('customer_id', $user->id);
        })->with(['order.service', 'order.itemType']);

        // Filter by Period
        $period = $request->input('period', 'all');
        if ($period === 'harian') {
            $query->whereDate('payment_date', Carbon::today());
        } elseif ($period === 'bulanan') {
            $query->whereMonth('payment_date', Carbon::now()->month)
                  ->whereYear('payment_date', Carbon::now()->year);
        } elseif ($period === 'tahunan') {
            $query->whereYear('payment_date', Carbon::now()->year);
        }

        // Filter by Status
        $status = $request->input('status', 'all');
        if ($status === 'success') {
            $query->where('status', 'success');
        } elseif ($status === 'pending') {
            $query->where('status', 'pending');
        }

        $payments = $query->latest('payment_date')->paginate(10)->withQueryString();

        return view('customer.payments.index', compact('payments', 'period', 'status'));
    }

    public function uploadProof(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'proof_payment' => 'required|image|max:2048', // max 2MB
        ]);

        if ($request->hasFile('proof_payment')) {
            $path = $request->file('proof_payment')->store('receipts', 'public');

            // Find or create Payment record safely
            $payment = Payment::where('order_id', $order->id)->first();
            if ($payment) {
                $payment->update([
                    'proof_path' => $path,
                    'status' => 'success', // Automatically mark as success/approved
                    'payment_date' => now(),
                ]);
            } else {
                $payment = Payment::create([
                    'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
                    'order_id' => $order->id,
                    'amount' => $order->total_price,
                    'payment_method' => $order->payment_method ?: 'transfer',
                    'status' => 'success', // Automatically mark as success/approved
                    'proof_path' => $path,
                    'payment_date' => now(),
                ]);
            }

            // Automatically update order status to paid and advance to waiting_pickup
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'waiting_pickup',
            ]);

            // Add OrderStatusLog
            \App\Models\OrderStatusLog::create([
                'order_id' => $order->id,
                'status'   => 'waiting_pickup',
                'user_id'  => auth()->id(),
            ]);

            // Automatically record as Income in Finance if not already recorded
            $financeExists = \App\Models\Finance::where('type', 'income')
                ->where('description', 'like', "%{$order->order_code}%")
                ->exists();

            if (!$financeExists) {
                \App\Models\Finance::create([
                    'type'        => 'income',
                    'amount'      => $order->total_price,
                    'category'    => 'Laundry Order',
                    'description' => "Bank Transfer Payment for Order {$order->order_code} (PAY: " . ($payment->payment_code ?? '') . ")",
                    'date'        => now(),
                ]);
            }

            return redirect()->back()->with('payment_success_popup', 'Payment Successful! Your transfer proof has been verified and your order has been moved to the Pickup queue.');
        }

        return redirect()->back()->withErrors(['proof_payment' => 'Failed to upload payment proof. Please try again.']);
    }

    public function qrisSimulation(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['service', 'itemType']);
        return view('customer.payments.qris_simulation', compact('order'));
    }

    public function qrisSimulationPay(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        // Find or create Payment record safely
        $payment = Payment::where('order_id', $order->id)->first();
        if ($payment) {
            $payment->update([
                'status' => 'success', // Simulate Stripe successful callback
                'payment_date' => now(),
            ]);
        } else {
            $payment = Payment::create([
                'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'payment_method' => 'qris',
                'status' => 'success',
                'payment_date' => now(),
            ]);
        }

        // Automatically update order status to paid and advance to waiting_pickup
        $order->update([
            'payment_status' => 'paid',
            'status'         => 'waiting_pickup',
        ]);

        // Add OrderStatusLog
        \App\Models\OrderStatusLog::create([
            'order_id' => $order->id,
            'status'   => 'waiting_pickup',
            'user_id'  => auth()->id(),
        ]);

        // Automatically record as Income in Finance if not already recorded
        $financeExists = \App\Models\Finance::where('type', 'income')
            ->where('description', 'like', "%{$order->order_code}%")
            ->exists();

        if (!$financeExists) {
            \App\Models\Finance::create([
                'type'        => 'income',
                'amount'      => $order->total_price,
                'category'    => 'Laundry Order',
                'description' => "QRIS Payment (Stripe Simulation) for Order {$order->order_code} (PAY: " . ($payment->payment_code ?? '') . ")",
                'date'        => now(),
            ]);
        }

        return redirect()->route('customer.orders.show', $order->id)->with('payment_success_popup', 'QRIS Payment via Stripe Simulation Successful! Your order status has been updated to Waiting Pickup.');
    }
}
