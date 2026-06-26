<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\OrderPhoto;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index()
    {
        // Orders that arrived at laundry or are in process
        $orders = Order::whereIn('status', [
            'picked_up', 
            'in_transit_to_laundry', 
            'arrived_at_laundry', 
            'washing', 
            'drying_ironing', 
            'packing', 
            'ready_for_delivery'
        ])
        ->with(['customer', 'service', 'itemType'])
        ->latest()
        ->get();

        return view('karyawan.dashboard', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string',
            'photo' => 'required|image|max:2048', // Mandatory photo for transparency
        ]);

        $order->update(['status' => $request->status]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('order_photos', 'public');
            OrderPhoto::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'photo_path' => $path,
                'context' => $request->status,
            ]);
        }

        return redirect()->back()->with('success', 'Order updated to ' . str_replace('_', ' ', $request->status));
    }
}
