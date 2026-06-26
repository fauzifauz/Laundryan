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

        // Basic authorization: user must be admin, or linked to the order
        $user = auth()->user();
        if ($user->role !== 'admin' && 
            $order->customer_id !== $user->id && 
            $order->courier_id !== $user->id &&
            $user->role !== 'karyawan') {
            abort(403);
        }

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
