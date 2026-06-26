<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\ActivityLog;

class PaymentObserver
{
    public function updated(Payment $payment)
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : 'System';
        $actorRole = $actor ? ucfirst($actor->role) : 'System';

        if ($payment->isDirty('status')) {
            $oldStatus = $payment->getOriginal('status');
            $newStatus = $payment->status;
            $order = $payment->order;
            $orderCode = $order ? $order->order_code : '-';
            $amountFormatted = 'Rp' . number_format($payment->amount, 0, ',', '.');

            if ($newStatus === 'success') {
                ActivityLog::log(
                    'Payment',
                    'Payment Successful',
                    'Payment for Order #' . $orderCode . ' of ' . $amountFormatted . ' was successful',
                    'Payment',
                    $orderCode,
                    ['status' => $oldStatus],
                    ['status' => $newStatus]
                );
            } elseif ($newStatus === 'failed') {
                ActivityLog::log(
                    'Payment',
                    'Payment Failed',
                    'Payment for Order #' . $orderCode . ' failed',
                    'Payment',
                    $orderCode,
                    ['status' => $oldStatus],
                    ['status' => $newStatus]
                );
            } elseif ($newStatus === 'refunded') {
                ActivityLog::log(
                    'Payment',
                    'Refund Processed',
                    'Refund for Order #' . $orderCode . ' of ' . $amountFormatted . ' processed',
                    'Payment',
                    $orderCode,
                    ['status' => $oldStatus],
                    ['status' => $newStatus]
                );
            }
        }
    }
}
