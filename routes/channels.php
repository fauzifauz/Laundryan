<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.tracking', function ($user) {
    return in_array($user->role, ['admin', 'karyawan']);
});

Broadcast::channel('karyawan.orders', function ($user) {
    return $user->role === 'karyawan';
});

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    if (in_array($user->role, ['admin', 'karyawan'])) {
        return true;
    }

    $order = \App\Models\Order::find($orderId);

    if (!$order) {
        return false;
    }

    return $order->customer_id === $user->id
        || $order->courier_id === $user->id
        || $order->pickup_courier_id === $user->id
        || $order->delivery_courier_id === $user->id;
});
