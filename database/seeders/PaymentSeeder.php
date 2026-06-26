<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();

        if ($orders->isEmpty()) {
            return;
        }

        $methods = ['transfer', 'e-wallet', 'stripe', 'cash'];
        $statuses = ['success', 'pending', 'failed'];

        foreach ($orders as $index => $order) {
            // Check if payment already exists
            if (Payment::where('order_id', $order->id)->exists()) {
                continue;
            }

            // Determine status based on order's payment_status
            if ($order->payment_status === 'paid') {
                $status = 'success';
            } else {
                // If order payment status is pending, we can randomize it
                $status = $statuses[rand(0, 2)];
            }

            $method = $order->payment_method ?: $methods[rand(0, 2)];

            // Create payment receipt placeholder for transfer or e-wallet payments
            $proof_path = null;
            if (in_array($method, ['transfer', 'e-wallet']) && $status !== 'pending' && rand(0, 1) === 1) {
                // Let's use a dummy public image placeholder or leave it null. But wait, the requirements say:
                // "Preview bukti pembayaran (image upload dari pelanggan)"
                // We should provide some dummy images or a real URL / file path.
                // Let's just create a placeholder image path.
                $proof_path = 'receipts/dummy_receipt_' . rand(1, 5) . '.jpg';
            } elseif (in_array($method, ['transfer', 'e-wallet']) && $status === 'pending') {
                $proof_path = 'receipts/dummy_receipt_' . rand(1, 5) . '.jpg';
            }

            Payment::create([
                'payment_code' => 'PAY-' . strtoupper(substr(md5($order->order_code . $index), 0, 8)),
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'payment_method' => $method,
                'status' => $status,
                'proof_path' => $proof_path,
                'admin_notes' => $status === 'failed' ? 'Bukti transfer tidak terbaca atau nominal kurang.' : ($status === 'success' ? 'Pembayaran telah dikonfirmasi oleh sistem/admin.' : null),
                'payment_date' => $order->created_at ? Carbon::parse($order->created_at)->addMinutes(rand(10, 120)) : Carbon::now(),
                'created_at' => $order->created_at ?: Carbon::now(),
                'updated_at' => $order->created_at ?: Carbon::now(),
            ]);
        }
    }
}
