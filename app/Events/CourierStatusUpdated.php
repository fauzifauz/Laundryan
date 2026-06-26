<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class CourierStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(Order $order)
    {
        $courierId = $order->courier_id;

        $activeOrders = \App\Models\Order::where('courier_id', $courierId)
            ->whereIn('status', [
                'penjemputan', 'dijemput', 'diantar', 'sampai',
                'pengantaran', 'diantarkan', 'selesai'
            ])
            ->with('customer')
            ->orderBy('created_at', 'asc')
            ->get();

        $mappedOrders = $activeOrders->map(function($o) {
            $type = in_array($o->status, ['penjemputan', 'dijemput', 'diantar', 'sampai']) ? 'pickup' : 'delivery';
            
            if ($type === 'pickup') {
                if (in_array($o->status, ['in_transit_to_laundry', 'diantar'])) {
                    $destLat = -6.1664983; // Laundry base
                    $destLng = 106.5602886;
                } else {
                    $destLat = $o->pickup_lat;
                    $destLng = $o->pickup_lng;
                }
            } else {
                $destLat = $o->delivery_lat ?? -6.1664983;
                $destLng = $o->delivery_lng ?? 106.5602886;
            }

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

            return [
                'id'            => $o->id,
                'order_code'    => $o->order_code,
                'status'        => $statusMap[$o->status] ?? strtoupper(str_replace('_', ' ', $o->status)),
                'type'          => $type,
                'customer_name' => $o->customer->name ?? 'Unknown',
                'address'       => $type === 'pickup' ? $o->pickup_address : $o->delivery_address,
                'item_type'     => $o->itemType->name ?? 'Laundry',
                'dest_lat'      => $destLat,
                'dest_lng'      => $destLng,
                'pickup_lat'    => $o->pickup_lat,
                'pickup_lng'    => $o->pickup_lng,
            ];
        })->toArray();

        $this->payload = [
            'courier_id' => $courierId,
            'orders'      => $mappedOrders,
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
        return 'courier.status.updated';
    }
}
