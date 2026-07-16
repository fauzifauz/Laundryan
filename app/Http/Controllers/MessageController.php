<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Message;

class MessageController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = auth()->user();

        // Basic authorization: user must be admin, or linked to the order
        $isAllowed = in_array($user->role, ['admin', 'karyawan'], true)
            || $order->customer_id === $user->id
            || $order->courier_id === $user->id
            || $order->pickup_courier_id === $user->id
            || $order->delivery_courier_id === $user->id;

        abort_unless($isAllowed, 403);

        $message = Message::create([
            'order_id' => $order->id,
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        \App\Models\ActivityLog::log(
            'Order',
            'Order Message Sent',
            ucfirst($user->role) . ' "' . $user->name . '" sent a message on Order #' . $order->order_code,
            'Order',
            $order->order_code,
            null,
            ['message' => \Illuminate\Support\Str::limit($request->message, 100)]
        );

        broadcast(new \App\Events\MessageSent($message))->toOthers();

        return redirect()->back()
            ->with('success', 'Message sent.')
            ->with('action_type', 'message_sent');
    }
}
