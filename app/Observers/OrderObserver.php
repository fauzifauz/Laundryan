<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Str;

class OrderObserver
{
    public function created(Order $order)
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : ($order->customer ? $order->customer->name : 'Customer');

        ActivityLog::log(
            'Order',
            'Order Created',
            'Order #' . $order->order_code . ' created by ' . $actorName,
            'Order',
            $order->order_code,
            null,
            $order->toArray()
        );

        // Automatically create a Payment record for the Order if not exists
        if (!Payment::where('order_id', $order->id)->exists()) {
            Payment::create([
                'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'payment_method' => $order->payment_method ?: 'cash',
                'status' => $order->payment_status === 'paid' ? 'success' : 'pending',
                'payment_date' => $order->payment_status === 'paid' ? now() : null,
            ]);
        }
    }

    public function updated(Order $order)
    {
        $actor = auth()->user();
        $actorRole = $actor ? ucfirst($actor->role) : 'System';
        $actorName = $actor ? $actor->name : 'System';

        // 1. Check if status changed
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status;

            $statusEnglish = [
                'pending_payment' => 'Pending Payment',
                'waiting_pickup' => 'Waiting for Pickup',
                'picking_up' => 'Picking Up',
                'picked_up' => 'Picked Up',
                'in_transit_to_laundry' => 'In Transit to Laundry',
                'arrived_at_laundry' => 'Arrived at Laundry',
                'washing' => 'Washing',
                'drying_ironing' => 'Drying & Ironing',
                'packing' => 'Packing',
                'ready_for_delivery' => 'Ready for Delivery',
                'delivering' => 'Delivering',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];

            $oldStatusTxt = $statusEnglish[$oldStatus] ?? ucfirst(str_replace('_', ' ', $oldStatus));
            $newStatusTxt = $statusEnglish[$newStatus] ?? ucfirst(str_replace('_', ' ', $newStatus));

            if ($newStatus === 'cancelled') {
                ActivityLog::log(
                    'Order',
                    'Order Cancelled',
                    'Order #' . $order->order_code . ' cancelled by ' . $actorRole . ' ' . $actorName,
                    'Order',
                    $order->order_code,
                    ['status' => $oldStatus],
                    ['status' => $newStatus]
                );
            } elseif ($newStatus === 'completed') {
                ActivityLog::log(
                    'Order',
                    'Order Completed',
                    'Order #' . $order->order_code . ' marked as completed by ' . $actorRole . ' ' . $actorName,
                    'Order',
                    $order->order_code,
                    ['status' => $oldStatus],
                    ['status' => $newStatus]
                );
            } else {
                ActivityLog::log(
                    'Order',
                    'Order Status Changed',
                    'Order #' . $order->order_code . ' status changed to "' . $newStatusTxt . '" by ' . $actorRole . ' ' . $actorName,
                    'Order',
                    $order->order_code,
                    ['status' => $oldStatus],
                    ['status' => $newStatus]
                );
            }
        }

        // 2. Check if courier changed
        if ($order->isDirty('courier_id') || $order->isDirty('pickup_courier_id') || $order->isDirty('delivery_courier_id')) {
            $newCourierId = $order->courier_id ?: ($order->pickup_courier_id ?: $order->delivery_courier_id);
            if ($newCourierId) {
                $courier = User::find($newCourierId);
                $courierName = $courier ? $courier->name : 'Courier';

                ActivityLog::log(
                    'Order',
                    'Courier Assigned',
                    'Courier "' . $courierName . '" assigned to Order #' . $order->order_code,
                    'Order',
                    $order->order_code,
                    [
                        'courier_id' => $order->getOriginal('courier_id'),
                        'pickup_courier_id' => $order->getOriginal('pickup_courier_id'),
                        'delivery_courier_id' => $order->getOriginal('delivery_courier_id')
                    ],
                    [
                        'courier_id' => $order->courier_id,
                        'pickup_courier_id' => $order->pickup_courier_id,
                        'delivery_courier_id' => $order->delivery_courier_id
                    ]
                );
            }
        }

        // 3. Check if payment method changed
        if ($order->isDirty('payment_method')) {
            $oldMethod = strtoupper($order->getOriginal('payment_method'));
            $newMethod = strtoupper($order->payment_method);
            ActivityLog::log(
                'Payment',
                'Payment Method Changed',
                'Payment method for Order #' . $order->order_code . ' changed to ' . $newMethod,
                'Payment',
                $order->order_code,
                ['payment_method' => $oldMethod],
                ['payment_method' => $newMethod]
            );
        }

        // 4. Sync payment status if order payment_status changes
        if ($order->isDirty('payment_status')) {
            $newPaymentStatus = $order->payment_status;
            $payment = $order->payments()->first();
            if ($payment) {
                if ($newPaymentStatus === 'paid' && $payment->status !== 'success') {
                    $payment->update([
                        'status' => 'success',
                        'payment_date' => now(),
                    ]);
                } elseif ($newPaymentStatus === 'pending' && $payment->status === 'success') {
                    $payment->update([
                        'status' => 'pending',
                    ]);
                }
            }
        }
    }
}
