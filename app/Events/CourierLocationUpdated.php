<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Location;

class CourierLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(Location $location)
    {
        $courier = $location->user;

        // Get ALL active orders for this courier
        $activeOrders = \App\Models\Order::where('courier_id', $courier->id)
            ->whereIn('status', [
                'penjemputan',
                'dijemput',
                'diantar',
                'sampai',
                'pengantaran',
                'diantarkan',
                'selesai'
            ])
            ->with('customer')
            ->orderBy('created_at', 'asc')
            ->get();

        $mappedOrders = $activeOrders->map(function ($order) {
            $type = in_array($order->status, ['penjemputan', 'dijemput', 'diantar', 'sampai']) ? 'pickup' : 'delivery';

            if ($type === 'pickup') {
                if (in_array($order->status, ['in_transit_to_laundry', 'diantar'])) {
                    $destLat = -6.1664983; // Laundry base
                    $destLng = 106.5602886;
                } else {
                    $destLat = $order->pickup_lat;
                    $destLng = $order->pickup_lng;
                }
            } else {
                $destLat = $order->delivery_lat ?? -6.1664983;
                $destLng = $order->delivery_lng ?? 106.5602886;
            }

            $statusMap = [
                'waiting_pickup' => 'PICKING UP',
                'picking_up' => 'PICKING UP',
                'picked_up' => 'PICKED UP',
                'in_transit_to_laundry' => 'TO LAUNDRY',
                'arrived_at_laundry' => 'ARRIVED',
                'penjemputan' => 'PICKING UP',
                'dijemput' => 'PICKED UP',
                'diantar' => 'TO LAUNDRY',
                'sampai' => 'ARRIVED',
                'ready_for_delivery' => 'DELIVERING',
                'pengantaran' => 'DELIVERING',
                'delivering' => 'ON DELIVERY',
                'diantarkan' => 'ON DELIVERY',
                'selesai' => 'COMPLETED',
                'completed' => 'COMPLETED',
            ];

            return [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'status' => $statusMap[$order->status] ?? strtoupper(str_replace('_', ' ', $order->status)),
                'type' => $type,
                'customer_name' => $order->customer->name ?? 'Unknown',
                'address' => $type === 'pickup' ? $order->pickup_address : $order->delivery_address,
                'item_type' => $order->itemType->name ?? 'Laundry',
                'dest_lat' => $destLat,
                'dest_lng' => $destLng,
                'pickup_lat' => $order->pickup_lat,
                'pickup_lng' => $order->pickup_lng,
            ];
        })->toArray();

        $this->payload = [
            'courier' => [
                'id' => $courier->id,
                'name' => $courier->name,
                'phone' => $courier->phone,
            ],
            'location' => [
                'lat' => $location->latitude,
                'lng' => $location->longitude,
                'updated_at' => $location->updated_at->diffForHumans(),
                'updated_at_raw' => $location->updated_at->toISOString(),
                'is_near_destination' => $location->is_near_destination ?? false,
                'near_order_code' => $location->near_order_code ?? null,
                'location_history' => \App\Models\Location::where('user_id', $location->user_id)
                    ->where('created_at', '>=', now()->subHours(4))
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(fn($l) => [$l->latitude, $l->longitude]),
            ],
            'orders' => $mappedOrders,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.tracking'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'courier.location.updated';
    }
}
