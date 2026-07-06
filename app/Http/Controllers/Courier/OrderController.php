<?php

namespace App\Http\Controllers\Courier;

use App\Events\CourierLocationUpdated;
use App\Events\CourierStatusUpdated;
use App\Events\LocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderPhoto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private const PICKUP_ACTIVE_STATUSES = [
        'waiting_pickup',
        'picking_up',
        'picked_up',
        'in_transit_to_laundry',
        'arrived_at_laundry',
        'penjemputan',
        'dijemput',
        'diantar',
        'sampai',
    ];

    private const DELIVERY_ACTIVE_STATUSES = [
        'ready_for_delivery',
        'delivering',
        'pengantaran',
        'diantarkan',
    ];

    private const FINISHED_STATUSES = [
        'completed',
        'cancelled',
        'selesai',
    ];

    private const DEFAULT_LATITUDE = -6.1664983;

    private const DEFAULT_LONGITUDE = 106.5602886;

    public function index()
    {
        $courierId = (int) Auth::id();

        $orders = Order::query()
            ->where(function (Builder $query) use ($courierId) {
                $query
                    ->where(function (Builder $pickupQuery) use ($courierId) {
                        $pickupQuery
                            ->where('pickup_courier_id', $courierId)
                            ->whereIn(
                                'status',
                                self::PICKUP_ACTIVE_STATUSES
                            );
                    })
                    ->orWhere(function (
                        Builder $deliveryQuery
                    ) use ($courierId) {
                        $deliveryQuery
                            ->where('delivery_courier_id', $courierId)
                            ->whereIn(
                                'status',
                                self::DELIVERY_ACTIVE_STATUSES
                            );
                    })
                    ->orWhere(function (
                        Builder $legacyQuery
                    ) use ($courierId) {
                        $legacyQuery
                            ->where('courier_id', $courierId)
                            ->whereNull('pickup_courier_id')
                            ->whereNull('delivery_courier_id')
                            ->whereNotIn(
                                'status',
                                self::FINISHED_STATUSES
                            );
                    });
            })
            ->with([
                'customer',
                'service',
                'itemType',
            ])
            ->latest()
            ->get();

        if ($orders->isNotEmpty()) {
            $latestLocation = Location::query()
                ->where('user_id', $courierId)
                ->latest()
                ->first();

            $currentLatitude = (float) (
                $latestLocation?->latitude
                ?? self::DEFAULT_LATITUDE
            );

            $currentLongitude = (float) (
                $latestLocation?->longitude
                ?? self::DEFAULT_LONGITUDE
            );

            $sortedOrders = [];
            $remainingOrders = $orders->values()->all();

            while (count($remainingOrders) > 0) {
                $nearestKey = null;
                $minimumDistance = null;

                foreach ($remainingOrders as $key => $order) {
                    [
                        $destinationLatitude,
                        $destinationLongitude,
                    ] = $this->getDestinationCoordinates($order);

                    $distance = sqrt(
                        (($destinationLatitude - $currentLatitude) ** 2)
                        + (($destinationLongitude - $currentLongitude) ** 2)
                    );

                    if (
                        $minimumDistance === null
                        || $distance < $minimumDistance
                    ) {
                        $minimumDistance = $distance;
                        $nearestKey = $key;
                    }
                }

                $nearestOrder = $remainingOrders[$nearestKey];

                $sortedOrders[] = $nearestOrder;

                unset($remainingOrders[$nearestKey]);

                [
                    $currentLatitude,
                    $currentLongitude,
                ] = $this->getDestinationCoordinates($nearestOrder);
            }

            $orders = collect($sortedOrders);
        }

        return view('kurir.dashboard', compact('orders'));
    }

    public function show(Order $order)
    {
        $courierId = (int) Auth::id();

        abort_unless(
            $this->isAssignedToCourier($order, $courierId),
            403
        );

        $order->load([
            'customer',
            'service',
            'itemType',
            'pickupCourier',
            'deliveryCourier',
            'photos.user',
            'messages.sender',
        ]);

        return view('kurir.orders.show', compact('order'));
    }

    public function updateStatus(
        Request $request,
        Order $order
    ) {
        $courierId = (int) Auth::id();

        abort_unless(
            $this->isAssignedToCourier($order, $courierId),
            403
        );

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'max:50',
            ],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        if ($request->hasFile('photo')) {
            $photoPath = $request
                ->file('photo')
                ->store('order_photos', 'public');

            OrderPhoto::create([
                'order_id' => $order->id,
                'user_id' => $courierId,
                'photo_path' => $photoPath,
                'context' => $validated['status'],
            ]);
        }

        $order->load([
            'customer',
            'pickupCourier',
            'deliveryCourier',
        ]);

        broadcast(
            new CourierStatusUpdated($order)
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Status berhasil diperbarui: '
                .$validated['status']
            );
    }

    public function updateLocation(Request $request)
    {
        $courierId = (int) Auth::id();

        $validated = $request->validate([
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
            'order_id' => [
                'nullable',
                'integer',
                'exists:orders,id',
            ],
        ]);

        if (! empty($validated['order_id'])) {
            $order = Order::findOrFail(
                $validated['order_id']
            );

            abort_unless(
                $this->isAssignedToCourier(
                    $order,
                    $courierId
                ),
                403
            );
        }

        $location = Location::create([
            'user_id' => $courierId,
            'order_id' => $validated['order_id'] ?? null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        $location->load('user');

        broadcast(
            new CourierLocationUpdated($location)
        );

        if (! empty($validated['order_id'])) {
            broadcast(
                new LocationUpdated($location)
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Lokasi kurir berhasil diperbarui.',
        ]);
    }

    private function isAssignedToCourier(
        Order $order,
        int $courierId
    ): bool {
        $isPickupCourier =
            (int) $order->pickup_courier_id === $courierId;

        $isDeliveryCourier =
            (int) $order->delivery_courier_id === $courierId;

        $isLegacyCourier =
            $order->pickup_courier_id === null
            && $order->delivery_courier_id === null
            && (int) $order->courier_id === $courierId;

        return $isPickupCourier
            || $isDeliveryCourier
            || $isLegacyCourier;
    }

    private function getDestinationCoordinates(
        Order $order
    ): array {
        $isPickup = in_array(
            $order->status,
            self::PICKUP_ACTIVE_STATUSES,
            true
        );

        $latitude = $isPickup
            ? $order->pickup_lat
            : $order->delivery_lat;

        $longitude = $isPickup
            ? $order->pickup_lng
            : $order->delivery_lng;

        return [
            (float) (
                $latitude ?: self::DEFAULT_LATITUDE
            ),
            (float) (
                $longitude ?: self::DEFAULT_LONGITUDE
            ),
        ];
    }
}