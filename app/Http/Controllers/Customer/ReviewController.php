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
            'rating_service' => 'required|integer|min:1|max:5',
            'rating_pickup_courier' => 'nullable|integer|min:1|max:5',
            'rating_delivery_courier' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'completed') {
            return redirect()->back()->with('error', 'You can only review completed orders.');
        }

        $ratings = array_filter([
            $request->rating_service,
            $request->rating_pickup_courier,
            $request->rating_delivery_courier,
        ]);
        $avgRating = count($ratings) > 0 ? (int)round(array_sum($ratings) / count($ratings)) : 5;

        $review = Review::create([
            'order_id' => $order->id,
            'rating' => $avgRating,
            'rating_service' => $request->rating_service,
            'rating_courier' => $request->rating_delivery_courier ?: $request->rating_pickup_courier, // backward compatibility
            'rating_pickup_courier' => $request->rating_pickup_courier,
            'rating_delivery_courier' => $request->rating_delivery_courier,
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
