<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\Location;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('courier_id', auth()->id())
            ->whereNotIn('status', ['completed', 'selesai'])
            ->with(['customer', 'service', 'itemType'])
            ->get();

        if ($orders->isNotEmpty()) {
            $latestLocation = Location::where('user_id', auth()->id())->latest()->first();
            $currentLat = $latestLocation ? $latestLocation->latitude : -6.1664983;
            $currentLng = $latestLocation ? $latestLocation->longitude : 106.5602886;

            $sorted = [];
            $remaining = $orders->all();

            while (count($remaining) > 0) {
                $nearestKey = null;
                $minDist = null;

                foreach ($remaining as $key => $order) {
                    $isPickup = in_array($order->status, [
                        'waiting_pickup', 'picking_up', 'picked_up', 'in_transit_to_laundry', 
                        'penjemputan', 'dijemput', 'diantar', 'sampai'
                    ]);
                    $destLat = $isPickup ? $order->pickup_lat : $order->delivery_lat;
                    $destLng = $isPickup ? $order->pickup_lng : $order->delivery_lng;
                    if (!$destLat || !$destLng) {
                        $destLat = -6.1664983;
                        $destLng = 106.5602886;
                    }

                    $dist = sqrt(pow($destLat - $currentLat, 2) + pow($destLng - $currentLng, 2));
                    if (is_null($minDist) || $dist < $minDist) {
                        $minDist = $dist;
                        $nearestKey = $key;
                    }
                }

                $nearestOrder = $remaining[$nearestKey];
                $sorted[] = $nearestOrder;
                unset($remaining[$nearestKey]);

                // Update current position for next iteration
                $isPickup = in_array($nearestOrder->status, [
                    'waiting_pickup', 'picking_up', 'picked_up', 'in_transit_to_laundry', 
                    'penjemputan', 'dijemput', 'diantar', 'sampai'
                ]);
                $currentLat = $isPickup ? $nearestOrder->pickup_lat : $nearestOrder->delivery_lat;
                $currentLng = $isPickup ? $nearestOrder->pickup_lng : $nearestOrder->delivery_lng;
                if (!$currentLat || !$currentLng) {
                    $currentLat = -6.1664983;
                    $currentLng = 106.5602886;
                }
            }

            $orders = collect($sorted);
        }
            
        return view('kurir.dashboard', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->courier_id !== auth()->id()) {
            abort(403);
        }
        $order->load(['customer', 'service', 'itemType', 'photos', 'messages.sender']);
        return view('kurir.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        if ($order->courier_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|string',
            'photo'  => 'nullable|image|max:2048',
        ]);

        $order->update(['status' => $request->status]);
        $order->load('customer');

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('order_photos', 'public');
            OrderPhoto::create([
                'order_id'   => $order->id,
                'user_id'    => auth()->id(),
                'photo_path' => $path,
                'context'    => $request->status,
            ]);
        }

        // Broadcast to admin tracking channel via WebSocket
        broadcast(new \App\Events\CourierStatusUpdated($order));

        return redirect()->back()->with('success', 'Status berhasil diperbarui: ' . $request->status);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'order_id'  => 'nullable|exists:orders,id',
        ]);

        $location = Location::create([
            'user_id'   => auth()->id(),
            'order_id'  => $request->order_id,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        $location->load('user');

        // Always broadcast location to admin tracking channel
        broadcast(new \App\Events\CourierLocationUpdated($location));

        return response()->json(['success' => true]);
    }
}
