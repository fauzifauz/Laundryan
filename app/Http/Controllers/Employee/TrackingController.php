<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Location;

class TrackingController extends Controller
{
    public function index()
    {
        return view('karyawan.tracking.index');
    }

    public function data()
    {
        // ─── COURIER DATA ───────────────────────────────────────────────
        $couriers = User::where('role', 'kurir')
            ->where('status', 'active')
            ->get();

        $trackingData = [];

        foreach ($couriers as $courier) {
            $latestLocation = Location::where('user_id', $courier->id)
                ->latest()
                ->first();

            $path = Location::where('user_id', $courier->id)
                ->where('created_at', '>=', now()->subHours(4))
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($l) => [$l->latitude, $l->longitude]);

            $activeOrders = Order::where('courier_id', $courier->id)
                ->whereIn('status', [
                    'waiting_pickup', 'picking_up', 'picked_up', 'in_transit_to_laundry', 
                    'ready_for_delivery', 'delivering',
                    'penjemputan', 'dijemput', 'diantar', 'pengantaran', 'diantarkan'
                ])
                ->with(['customer', 'itemType'])
                ->get();

            if ($activeOrders->isNotEmpty()) {
                $currentLat = $latestLocation ? $latestLocation->latitude : -6.1664983;
                $currentLng = $latestLocation ? $latestLocation->longitude : 106.5602886;

                $sorted = [];
                $remaining = $activeOrders->all();

                while (count($remaining) > 0) {
                    $nearestKey = null;
                    $minDist = null;

                    foreach ($remaining as $key => $order) {
                        $isPickup = in_array($order->status, [
                            'waiting_pickup', 'picking_up', 'picked_up', 'in_transit_to_laundry', 
                            'arrived_at_laundry', 'penjemputan', 'dijemput', 'diantar', 'sampai'
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

                    $isPickup = in_array($nearestOrder->status, [
                        'waiting_pickup', 'picking_up', 'picked_up', 'in_transit_to_laundry', 
                        'arrived_at_laundry', 'penjemputan', 'dijemput', 'diantar', 'sampai'
                    ]);
                    $currentLat = $isPickup ? $nearestOrder->pickup_lat : $nearestOrder->delivery_lat;
                    $currentLng = $isPickup ? $nearestOrder->pickup_lng : $nearestOrder->delivery_lng;
                    if (!$currentLat || !$currentLng) {
                        $currentLat = -6.1664983;
                        $currentLng = 106.5602886;
                    }
                }
                $activeOrders = collect($sorted);
            }

            $trackingData[] = [
                'courier' => [
                    'id'    => $courier->id,
                    'name'  => $courier->name,
                    'email' => $courier->email,
                    'phone' => $courier->phone,
                    'photo' => $courier->photo ? asset('storage/' . $courier->photo) : "https://ui-avatars.com/api/?name=" . urlencode($courier->name) . "&color=005bc0&background=E0F2FE",
                ],
                'location' => $latestLocation ? [
                    'lat' => $latestLocation->latitude,
                    'lng' => $latestLocation->longitude,
                    'updated_at' => $latestLocation->updated_at->diffForHumans(),
                    'updated_at_raw' => $latestLocation->updated_at->toISOString(),
                    'location_history' => $path,
                ] : [
                    'lat' => -6.1664983,
                    'lng' => 106.5602886,
                    'updated_at' => 'No location recorded',
                    'updated_at_raw' => now()->toISOString(),
                    'location_history' => [],
                ],
                'orders' => $activeOrders->map(function($order) {
                    // Status mapping for English translation
                    $statusMap = [
                        'waiting_pickup'       => 'PICKING UP',
                        'picking_up'           => 'PICKING UP',
                        'picked_up'            => 'PICKED UP',
                        'in_transit_to_laundry'=> 'TO LAUNDRY',
                        'arrived_at_laundry'   => 'ARRIVED',
                        'penjemputan'          => 'PICKING UP',
                        'dijemput'             => 'PICKED UP',
                        'diantar'              => 'TO LAUNDRY',
                        'sampai'               => 'ARRIVED',
                        'ready_for_delivery'   => 'DELIVERING',
                        'pengantaran'          => 'DELIVERING',
                        'delivering'           => 'ON DELIVERY',
                        'diantarkan'           => 'ON DELIVERY',
                        'selesai'              => 'COMPLETED',
                        'completed'            => 'COMPLETED',
                    ];

                    $isPickup = in_array($order->status, [
                        'waiting_pickup', 'picking_up', 'picked_up', 'in_transit_to_laundry', 
                        'arrived_at_laundry', 'penjemputan', 'dijemput', 'diantar', 'sampai'
                    ]);

                    // Dynamic Destination Logic
                    $laundryBase = ['lat' => -6.1664983, 'lng' => 106.5602886];
                    $destLat = $order->delivery_lat;
                    $destLng = $order->delivery_lng;

                    if ($isPickup) {
                        // If status is 'diantar' (In Transit to Laundry), destination is Laundry Base
                        if (in_array($order->status, ['in_transit_to_laundry', 'diantar'])) {
                            $destLat = $laundryBase['lat'];
                            $destLng = $laundryBase['lng'];
                        } else {
                            $destLat = $order->pickup_lat;
                            $destLng = $order->pickup_lng;
                        }
                    }
                    
                    return [
                        'id'            => $order->id,
                        'order_code'    => $order->order_code,
                        'status'        => $statusMap[$order->status] ?? strtoupper(str_replace('_', ' ', $order->status)),
                        'type'          => $isPickup ? 'pickup' : 'delivery',
                        'customer_name' => $order->customer->name ?? 'Unknown',
                        'customer_photo'=> ($order->customer && $order->customer->photo) ? asset('storage/' . $order->customer->photo) : "https://ui-avatars.com/api/?name=" . urlencode($order->customer->name ?? 'U') . "&color=FFFFFF&background=" . ($isPickup ? 'F59E0B' : '10B981'),
                        'address'       => $isPickup ? $order->pickup_address : $order->delivery_address,
                        'item_type'     => $order->itemType->name ?? 'Laundry',
                        'dest_lat'      => $destLat,
                        'dest_lng'      => $destLng,
                        'pickup_lat'    => $order->pickup_lat,
                        'pickup_lng'    => $order->pickup_lng,
                    ];
                }),
            ];
        }

        // ─── HEATMAP DATA (ALL ACTIVE ORDERS) ───────────────────────────
        $allActiveOrders = Order::whereIn('status', [
            'waiting_pickup', 'picking_up', 'picked_up', 'in_transit_to_laundry', 
            'arrived_at_laundry', 'washing', 'drying_ironing', 'packing', 
            'ready_for_delivery', 'delivering', 'completed',
            'penjemputan', 'dijemput', 'diantar', 'sampai', 'pengantaran', 'diantarkan', 'selesai'
        ])->get()->map(function($o) {
            $isPickup = in_array($o->status, [
                'waiting_pickup', 'picking_up', 'picked_up', 'in_transit_to_laundry', 'arrived_at_laundry',
                'penjemputan', 'dijemput', 'diantar', 'sampai'
            ]);
            return [
                'lat' => $isPickup ? $o->pickup_lat : $o->delivery_lat,
                'lng' => $isPickup ? $o->pickup_lng : $o->delivery_lng,
            ];
        })->filter(fn($coords) => $coords['lat'] && $coords['lng'])->values();

        return response()->json([
            'tracking' => $trackingData,
            'heatmap'  => $allActiveOrders
        ]);
    }
}
