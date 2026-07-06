<?php

namespace App\Http\Controllers\Courier;

use App\Events\CourierLocationUpdated;
use App\Events\CourierStatusUpdated;
use App\Events\LocationUpdated;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\OrderStatusLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class OrderController extends Controller
{
    private const PICKUP_ACTIVE_STATUSES = [
        'waiting_pickup',
        'picking_up',
        'picked_up',
        'in_transit_to_laundry',

        // Dukungan data lama
        'penjemputan',
        'dijemput',
        'diantar',
        'sampai',
    ];

    private const DELIVERY_ACTIVE_STATUSES = [
        'ready_for_delivery',
        'delivering',

        // Dukungan data lama
        'pengantaran',
        'diantarkan',
    ];

    private const FINISHED_STATUSES = [
        'completed',
        'cancelled',
        'selesai',
    ];

    private const TO_LAUNDRY_STATUSES = [
        'picked_up',
        'in_transit_to_laundry',

        // Dukungan data lama
        'dijemput',
        'diantar',
        'sampai',
    ];

    private const STATUS_TRANSITIONS = [
        // Pickup standar
        'waiting_pickup' => 'picking_up',
        'picking_up' => 'picked_up',
        'picked_up' => 'in_transit_to_laundry',
        'in_transit_to_laundry' => 'arrived_at_laundry',

        // Pickup data lama
        'penjemputan' => 'picked_up',
        'dijemput' => 'in_transit_to_laundry',
        'diantar' => 'arrived_at_laundry',
        'sampai' => 'arrived_at_laundry',

        // Delivery standar
        'ready_for_delivery' => 'delivering',
        'delivering' => 'completed',

        // Delivery data lama
        'pengantaran' => 'completed',
        'diantarkan' => 'completed',
    ];

    private const PHOTO_REQUIRED_STATUSES = [
        'picked_up',
        'completed',
    ];

    private const STATUS_LABELS = [
        'pending_payment' => 'Menunggu Pembayaran',

        'waiting_pickup' => 'Menunggu Penjemputan',
        'picking_up' => 'Proses Penjemputan',
        'picked_up' => 'Laundry Dijemput',
        'in_transit_to_laundry' => 'Dalam Perjalanan ke Laundry',
        'arrived_at_laundry' => 'Sampai di Laundry',

        'washing' => 'Proses Pencucian',
        'drying_ironing' => 'Pengeringan dan Setrika',
        'packing' => 'Packing',
        'ready_for_delivery' => 'Siap Diantar',

        'delivering' => 'Dalam Pengantaran',
        'completed' => 'Selesai Diantar',
        'cancelled' => 'Dibatalkan',

        // Label data lama
        'penjemputan' => 'Proses Penjemputan',
        'dijemput' => 'Laundry Dijemput',
        'diantar' => 'Dalam Perjalanan ke Laundry',
        'sampai' => 'Sampai di Laundry',
        'pengantaran' => 'Dalam Pengantaran',
        'diantarkan' => 'Selesai Diantar',
        'selesai' => 'Selesai',
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

                if ($nearestKey === null) {
                    break;
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

    public function orders(Request $request)
    {
        $courierId = (int) Auth::id();

        $search = trim((string) $request->query('search', ''));
        $scope = (string) $request->query('scope', 'active');
        $type = (string) $request->query('type', 'all');
        $status = (string) $request->query('status', '');

        if (! in_array($scope, ['active', 'completed', 'all'], true)) {
            $scope = 'active';
        }

        if (! in_array($type, ['all', 'pickup', 'delivery'], true)) {
            $type = 'all';
        }

        if (
            $status !== ''
            && ! array_key_exists($status, self::STATUS_LABELS)
        ) {
            $status = '';
        }

        $query = $this
            ->assignedOrdersQuery($courierId)
            ->with([
                'customer',
                'service',
                'itemType',
                'pickupCourier',
                'deliveryCourier',
            ]);

        if ($search !== '') {
            $searchTerm = '%'.$search.'%';

            $query->where(function (
                Builder $searchQuery
            ) use ($searchTerm) {
                $searchQuery
                    ->where('order_code', 'like', $searchTerm)
                    ->orWhere('pickup_address', 'like', $searchTerm)
                    ->orWhere('delivery_address', 'like', $searchTerm)
                    ->orWhereHas('customer', function (
                        Builder $customerQuery
                    ) use ($searchTerm) {
                        $customerQuery
                            ->where('name', 'like', $searchTerm)
                            ->orWhere('phone', 'like', $searchTerm);
                    });
            });
        }

        if ($scope === 'active') {
            $query->whereNotIn(
                'status',
                self::FINISHED_STATUSES
            );
        }

        if ($scope === 'completed') {
            $query->whereIn(
                'status',
                ['completed', 'selesai']
            );
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $this->applyTaskTypeFilter(
            $query,
            $type,
            $courierId
        );

        $orders = $query
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        $pickupQuery = $this->assignedOrdersQuery($courierId);

        $this->applyTaskTypeFilter(
            $pickupQuery,
            'pickup',
            $courierId
        );

        $deliveryQuery = $this->assignedOrdersQuery($courierId);

        $this->applyTaskTypeFilter(
            $deliveryQuery,
            'delivery',
            $courierId
        );

        $statistics = [
            'total' => $this
                ->assignedOrdersQuery($courierId)
                ->count(),

            'active' => $this
                ->assignedOrdersQuery($courierId)
                ->whereNotIn(
                    'status',
                    self::FINISHED_STATUSES
                )
                ->count(),

            'completed' => $this
                ->assignedOrdersQuery($courierId)
                ->whereIn(
                    'status',
                    ['completed', 'selesai']
                )
                ->count(),

            'pickup' => $pickupQuery->count(),

            'delivery' => $deliveryQuery->count(),
        ];

        $statusOptions = self::STATUS_LABELS;

        return view('kurir.orders.index', compact(
            'orders',
            'statistics',
            'statusOptions',
            'courierId',
            'search',
            'scope',
            'type',
            'status'
        ));
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
            'statusLogs.user',
        ]);

        $statusLabel = $this->getStatusLabel($order->status);
        $nextStatus = $this->getNextStatus($order->status);

        $nextStatusLabel = $nextStatus
            ? $this->getStatusLabel($nextStatus)
            : null;

        $photoRequired = $nextStatus
            ? $this->isPhotoRequired($nextStatus)
            : false;

        $canUpdateStatus = $nextStatus !== null
            && $this->canHandleCurrentFlow(
                $order,
                $courierId
            );

        $isPickupFlow = $this->isPickupFlow($order->status);

        [
            $destinationLatitude,
            $destinationLongitude,
        ] = $this->getDestinationCoordinates($order);

        return view('kurir.orders.show', compact(
            'order',
            'statusLabel',
            'nextStatus',
            'nextStatusLabel',
            'photoRequired',
            'canUpdateStatus',
            'isPickupFlow',
            'destinationLatitude',
            'destinationLongitude'
        ));
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

        abort_unless(
            $this->canHandleCurrentFlow($order, $courierId),
            403
        );

        $nextStatus = $this->getNextStatus($order->status);

        if ($nextStatus === null) {
            return redirect()
                ->back()
                ->withErrors([
                    'status' => 'Order ini tidak memiliki tahap kurir berikutnya.',
                ]);
        }

        $photoRequired = $this->isPhotoRequired($nextStatus);

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in([$nextStatus]),
            ],
            'photo' => [
                $photoRequired ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'status.in' => 'Status tidak sesuai dengan urutan proses order.',
            'photo.required' => 'Foto bukti wajib diunggah pada tahap ini.',
            'photo.image' => 'File bukti harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $oldStatus = $order->status;
        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request
                ->file('photo')
                ->store('order_photos', 'public');
        }

        try {
            DB::transaction(function () use (
                $order,
                $validated,
                $courierId,
                $photoPath
            ) {
                $order->update([
                    'status' => $validated['status'],
                ]);

                if ($photoPath !== null) {
                    OrderPhoto::create([
                        'order_id' => $order->id,
                        'user_id' => $courierId,
                        'photo_path' => $photoPath,
                        'context' => $validated['status'],
                    ]);
                }

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => $validated['status'],
                    'user_id' => $courierId,
                ]);
            });
        } catch (Throwable $exception) {
            if ($photoPath !== null) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        $order->refresh()->load([
            'customer',
            'service',
            'itemType',
            'pickupCourier',
            'deliveryCourier',
        ]);

        broadcast(
            new CourierStatusUpdated($order)
        );

        broadcast(
            new OrderStatusUpdated($order)
        )->toOthers();

        return redirect()
            ->back()
            ->with(
                'success',
                sprintf(
                    'Status berhasil diperbarui dari %s menjadi %s.',
                    $this->getStatusLabel($oldStatus),
                    $this->getStatusLabel($validated['status'])
                )
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

    private function assignedOrdersQuery(
        int $courierId
    ): Builder {
        return Order::query()
            ->where(function (
                Builder $query
            ) use ($courierId) {
                $query
                    ->where(
                        'pickup_courier_id',
                        $courierId
                    )
                    ->orWhere(
                        'delivery_courier_id',
                        $courierId
                    )
                    ->orWhere(function (
                        Builder $legacyQuery
                    ) use ($courierId) {
                        $legacyQuery
                            ->where(
                                'courier_id',
                                $courierId
                            )
                            ->whereNull(
                                'pickup_courier_id'
                            )
                            ->whereNull(
                                'delivery_courier_id'
                            );
                    });
            });
    }

    private function applyTaskTypeFilter(
        Builder $query,
        string $type,
        int $courierId
    ): Builder {
        if ($type === 'pickup') {
            return $query->where(function (
                Builder $typeQuery
            ) use ($courierId) {
                $typeQuery
                    ->where(
                        'pickup_courier_id',
                        $courierId
                    )
                    ->orWhere(function (
                        Builder $legacyQuery
                    ) use ($courierId) {
                        $legacyQuery
                            ->where(
                                'courier_id',
                                $courierId
                            )
                            ->whereNull(
                                'pickup_courier_id'
                            )
                            ->whereNull(
                                'delivery_courier_id'
                            )
                            ->whereIn(
                                'status',
                                self::PICKUP_ACTIVE_STATUSES
                            );
                    });
            });
        }

        if ($type === 'delivery') {
            return $query->where(function (
                Builder $typeQuery
            ) use ($courierId) {
                $typeQuery
                    ->where(
                        'delivery_courier_id',
                        $courierId
                    )
                    ->orWhere(function (
                        Builder $legacyQuery
                    ) use ($courierId) {
                        $legacyQuery
                            ->where(
                                'courier_id',
                                $courierId
                            )
                            ->whereNull(
                                'pickup_courier_id'
                            )
                            ->whereNull(
                                'delivery_courier_id'
                            )
                            ->whereIn('status', [
                                ...self::DELIVERY_ACTIVE_STATUSES,
                                'completed',
                                'selesai',
                            ]);
                    });
            });
        }

        return $query;
    }

    private function getNextStatus(string $currentStatus): ?string
    {
        return self::STATUS_TRANSITIONS[$currentStatus] ?? null;
    }

    private function getStatusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status]
            ?? ucfirst(str_replace('_', ' ', $status));
    }

    private function isPhotoRequired(string $status): bool
    {
        return in_array(
            $status,
            self::PHOTO_REQUIRED_STATUSES,
            true
        );
    }

    private function isAssignedToCourier(
        Order $order,
        int $courierId
    ): bool {
        return $this->isPickupCourier($order, $courierId)
            || $this->isDeliveryCourier($order, $courierId)
            || $this->isLegacyCourier($order, $courierId);
    }

    private function canHandleCurrentFlow(
        Order $order,
        int $courierId
    ): bool {
        if ($this->isPickupFlow($order->status)) {
            return $this->isPickupCourier($order, $courierId)
                || $this->isLegacyCourier($order, $courierId);
        }

        if ($this->isDeliveryFlow($order->status)) {
            return $this->isDeliveryCourier($order, $courierId)
                || $this->isLegacyCourier($order, $courierId);
        }

        return false;
    }

    private function isPickupCourier(
        Order $order,
        int $courierId
    ): bool {
        return $order->pickup_courier_id !== null
            && (int) $order->pickup_courier_id === $courierId;
    }

    private function isDeliveryCourier(
        Order $order,
        int $courierId
    ): bool {
        return $order->delivery_courier_id !== null
            && (int) $order->delivery_courier_id === $courierId;
    }

    private function isLegacyCourier(
        Order $order,
        int $courierId
    ): bool {
        return $order->pickup_courier_id === null
            && $order->delivery_courier_id === null
            && (int) $order->courier_id === $courierId;
    }

    private function isPickupFlow(string $status): bool
    {
        return in_array(
            $status,
            self::PICKUP_ACTIVE_STATUSES,
            true
        );
    }

    private function isDeliveryFlow(string $status): bool
    {
        return in_array(
            $status,
            self::DELIVERY_ACTIVE_STATUSES,
            true
        );
    }

    private function getDestinationCoordinates(
        Order $order
    ): array {
        if (
            in_array(
                $order->status,
                self::TO_LAUNDRY_STATUSES,
                true
            )
        ) {
            return [
                self::DEFAULT_LATITUDE,
                self::DEFAULT_LONGITUDE,
            ];
        }

        if ($this->isPickupFlow($order->status)) {
            return [
                (float) (
                    $order->pickup_lat
                    ?: self::DEFAULT_LATITUDE
                ),
                (float) (
                    $order->pickup_lng
                    ?: self::DEFAULT_LONGITUDE
                ),
            ];
        }

        return [
            (float) (
                $order->delivery_lat
                ?: self::DEFAULT_LATITUDE
            ),
            (float) (
                $order->delivery_lng
                ?: self::DEFAULT_LONGITUDE
            ),
        ];
    }
}