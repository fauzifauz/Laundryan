<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'completed') {
            return redirect()->back()->with('error', 'You can only review completed orders.');
        }

        $review = Review::create([
            'order_id' => $order->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        \App\Models\ActivityLog::log(
            'Order',
            'Order Review Submitted',
            'Customer "' . auth()->user()->name . '" submitted a ' . $request->rating . '-star review for Order #' . $order->order_code,
            'Order',
            $order->order_code,
            null,
            $review->toArray()
        );

        return redirect()->back()->with('success', 'Thank you for your feedback!');
    }
}
