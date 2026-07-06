<?php

namespace App\Events;

use App\Models\Location;
use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourierLocationUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private const PICKUP_STATUSES = [
        'waiting_pickup',
        'picking_up',
        'picked_up',
        'in_transit_to_laundry',
        'arrived_at_laundry',

        // Dukungan data lama
        'penjemputan',
        'dijemput',
        'diantar',
        'sampai',
    ];

    private const DELIVERY_STATUSES = [
        'ready_for_delivery',
        'delivering',

        // Dukungan data lama
        'pengantaran',
        'diantarkan',
    ];

    private const TO_LAUNDRY_STATUSES = [
        'picked_up',
        'in_transit_to_laundry',
        'arrived_at_laundry',

        // Dukungan data lama
        'dijemput',
        'diantar',
        'sampai',
    ];

    private const STATUS_LABELS = [
        'waiting_pickup' => 'WAITING PICKUP',
        'picking_up' => 'PICKING UP',
        'picked_up' => 'PICKED UP',
        'in_transit_to_laundry' => 'TO LAUNDRY',
        'arrived_at_laundry' => 'ARRIVED',

        'ready_for_delivery' => 'READY FOR DELIVERY',
        'delivering' => 'ON DELIVERY',

        'penjemputan' => 'PICKING UP',
        'dijemput' => 'PICKED UP',
        'diantar' => 'TO LAUNDRY',
        'sampai' => 'ARRIVED',
        'pengantaran' => 'DELIVERING',
        'diantarkan' => 'DELIVERED',
    ];

    private const LAUNDRY_LATITUDE = -6.1664983;

    private const LAUNDRY_LONGITUDE = 106.5602886;

    public array $payload;

    public function __construct(Location $location)
    {
        $location->loadMissing('user');

        $courier = $location->user;
        $courierId = (int) $location->user_id;

        $orders = $this->getActiveOrders($courierId)
            ->map(fn (Order $order): array => $this->mapOrder($order))
            ->values()
            ->all();

        $locationHistory = Location::query()
            ->where('user_id', $courierId)
            ->where('created_at', '>=', now()->subHours(4))
            ->oldest('created_at')
            ->get()
            ->map(fn (Location $item): array => [
                (float) $item->latitude,
                (float) $item->longitude,
            ])
            ->values()
            ->all();

        $this->payload = [
            'courier' => [
                'id' => $courierId,
                'name' => $courier?->name ?? 'Kurir',
                'phone' => $courier?->phone,
            ],
            'location' => [
                'lat' => (float) $location->latitude,
                'lng' => (float) $location->longitude,
                'updated_at' => $location->updated_at?->diffForHumans(),
                'updated_at_raw' => $location->updated_at?->toISOString(),
                'is_near_destination' => false,
                'near_order_code' => null,
                'location_history' => $locationHistory,
            ],
            'orders' => $orders,
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

    private function getActiveOrders(int $courierId)
    {
        return Order::query()
            ->where(function (Builder $query) use ($courierId) {
                $query
                    ->where(function (
                        Builder $pickupQuery
                    ) use ($courierId) {
                        $pickupQuery
                            ->where('pickup_courier_id', $courierId)
                            ->whereIn(
                                'status',
                                self::PICKUP_STATUSES
                            );
                    })
                    ->orWhere(function (
                        Builder $deliveryQuery
                    ) use ($courierId) {
                        $deliveryQuery
                            ->where('delivery_courier_id', $courierId)
                            ->whereIn(
                                'status',
                                self::DELIVERY_STATUSES
                            );
                    })
                    ->orWhere(function (
                        Builder $legacyQuery
                    ) use ($courierId) {
                        $legacyQuery
                            ->where('courier_id', $courierId)
                            ->whereNull('pickup_courier_id')
                            ->whereNull('delivery_courier_id')
                            ->whereIn('status', [
                                ...self::PICKUP_STATUSES,
                                ...self::DELIVERY_STATUSES,
                            ]);
                    });
            })
            ->with([
                'customer',
                'itemType',
            ])
            ->oldest('created_at')
            ->get();
    }

    private function mapOrder(Order $order): array
    {
        $isPickup = in_array(
            $order->status,
            self::PICKUP_STATUSES,
            true
        );

        [$destinationLatitude, $destinationLongitude] =
            $this->getDestinationCoordinates($order, $isPickup);

        return [
            'id' => $order->id,
            'order_code' => $order->order_code,
            'status' => self::STATUS_LABELS[$order->status]
                ?? strtoupper(str_replace('_', ' ', $order->status)),
            'type' => $isPickup ? 'pickup' : 'delivery',
            'customer_name' => $order->customer?->name ?? 'Unknown',
            'address' => $isPickup
                ? $order->pickup_address
                : $order->delivery_address,
            'item_type' => $order->itemType?->name ?? 'Laundry',
            'dest_lat' => $destinationLatitude,
            'dest_lng' => $destinationLongitude,
            'pickup_lat' => (float) (
                $order->pickup_lat
                ?: self::LAUNDRY_LATITUDE
            ),
            'pickup_lng' => (float) (
                $order->pickup_lng
                ?: self::LAUNDRY_LONGITUDE
            ),
        ];
    }

    private function getDestinationCoordinates(
        Order $order,
        bool $isPickup
    ): array {
        if (
            in_array(
                $order->status,
                self::TO_LAUNDRY_STATUSES,
                true
            )
        ) {
            return [
                self::LAUNDRY_LATITUDE,
                self::LAUNDRY_LONGITUDE,
            ];
        }

        if ($isPickup) {
            return [
                (float) (
                    $order->pickup_lat
                    ?: self::LAUNDRY_LATITUDE
                ),
                (float) (
                    $order->pickup_lng
                    ?: self::LAUNDRY_LONGITUDE
                ),
            ];
        }

        return [
            (float) (
                $order->delivery_lat
                ?: self::LAUNDRY_LATITUDE
            ),
            (float) (
                $order->delivery_lng
                ?: self::LAUNDRY_LONGITUDE
            ),
        ];
    }
}