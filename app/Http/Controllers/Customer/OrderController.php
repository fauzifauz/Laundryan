<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Service;
use App\Models\ItemType;
use App\Models\Payment;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->customerOrders()->latest();

        // Filter based on status
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'proses_pencucian' || $status === 'active') {
                $query->whereNotIn('status', ['completed', 'cancelled']);
            } elseif ($status === 'setrika') {
                $query->where('status', 'drying_ironing');
            } elseif ($status === 'packing') {
                $query->where('status', 'packing');
            } elseif ($status === 'selesai' || $status === 'completed') {
                $query->where('status', 'completed');
            } else {
                $query->where('status', $status);
            }
        }

        // Filter based on period
        if ($request->filled('period') && $request->period !== 'all') {
            $period = $request->period;
            if ($period === 'harian') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($period === 'mingguan') {
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($period === 'bulanan') {
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
            } elseif ($period === 'tahunan') {
                $query->whereYear('created_at', Carbon::now()->year);
            }
        }

        $orders = $query->get();
        return view('customer.orders.index', compact('orders'));
    }

    public function create()
    {
        $services = Service::where('is_active', true)->get();
        $itemTypes = ItemType::where('is_active', true)->get();
        return view('customer.orders.create', compact('services', 'itemTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'item_type_id' => 'required|exists:item_types,id',
            'pickup_address' => 'required|string',
            'delivery_address' => 'required|string',
            'pickup_time' => 'required|date|after:now',
            'soap' => 'required|string',
            'fragrance' => 'required|string',
            'payment_method' => 'required|in:stripe,bank_transfer,qris',
            'notes_admin' => 'nullable|string',
            'notes_employee' => 'nullable|string',
            'notes_courier' => 'nullable|string',
        ]);

        $pricing = $this->getPricingDetails(
            $request->service_id,
            $request->item_type_id,
            $request->pickup_address,
            $request->delivery_address
        );

        if (!$pricing) {
            return redirect()->back()->withErrors(['error' => 'Unable to calculate pricing.']);
        }

        $service = Service::find($request->service_id);
        $itemType = ItemType::find($request->item_type_id);

        // Consolidate notes for Admin, Karyawan, Kurir
        $notesArray = [];
        if ($request->filled('notes_admin')) {
            $notesArray[] = 'Catatan Admin: ' . $request->notes_admin;
        }
        if ($request->filled('notes_employee')) {
            $notesArray[] = 'Catatan Karyawan: ' . $request->notes_employee;
        }
        if ($request->filled('notes_courier')) {
            $notesArray[] = 'Catatan Kurir: ' . $request->notes_courier;
        }
        $consolidatedNotes = implode("\n", $notesArray);

        $order = Order::create([
            'order_code' => 'ORD-' . strtoupper(Str::random(10)),
            'customer_id' => auth()->id(),
            'service_id' => $service->id,
            'item_type_id' => $itemType->id,
            'pickup_address' => $request->pickup_address,
            'pickup_lat' => $pricing['pickup_lat'],
            'pickup_lng' => $pricing['pickup_lng'],
            'delivery_address' => $request->delivery_address,
            'delivery_lat' => $pricing['delivery_lat'],
            'delivery_lng' => $pricing['delivery_lng'],
            'pickup_time' => $request->pickup_time,
            'notes' => $consolidatedNotes,
            'service_price' => $pricing['service_price'],
            'item_price' => $pricing['item_price'],
            'shipping_cost' => $pricing['shipping_cost'],
            'tax' => $pricing['tax'],
            'total_price' => $pricing['total_price'],
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'soap' => $request->soap,
            'fragrance' => $request->fragrance,
            'payment_method' => $request->payment_method,
        ]);

        if ($request->payment_method === 'stripe') {
            try {
                // Stripe Integration
                Stripe::setApiKey(config('services.stripe.secret'));

                $session = Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'idr',
                            'product_data' => [
                                'name' => 'Laundry Service: ' . $service->name . ' (' . $itemType->name . ')',
                            ],
                            'unit_amount' => (int)($pricing['total_price'] * 100),
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => route('customer.payment.success', $order->id) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('customer.payment.cancel', $order->id),
                    'client_reference_id' => $order->id,
                ]);

                $order->update(['stripe_session_id' => $session->id]);

                // Create a Payment record
                Payment::create([
                    'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
                    'order_id' => $order->id,
                    'amount' => $order->total_price,
                    'payment_method' => 'stripe',
                    'status' => 'pending',
                    'payment_date' => now(),
                ]);

                return redirect($session->url);
            } catch (\Exception $e) {
                // If stripe fails (e.g. key is empty), fallback to bank transfer
                Payment::create([
                    'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
                    'order_id' => $order->id,
                    'amount' => $order->total_price,
                    'payment_method' => 'transfer',
                    'status' => 'pending',
                    'payment_date' => now(),
                ]);
                $order->update(['payment_method' => 'bank_transfer']);
                return redirect()->route('customer.orders.show', $order->id)->with('warning', 'Online payment system is currently unavailable. Redirected to manual Bank Transfer.');
            }
        } elseif ($request->payment_method === 'qris') {
            // QRIS Payment Method (Stripe QRIS Simulation)
            Payment::create([
                'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'payment_method' => 'qris',
                'status' => 'pending',
                'payment_date' => now(),
            ]);

            return redirect()->route('customer.orders.show', $order->id)->with('success', 'Laundry order successfully created. Please scan the QRIS code to perform payment simulation.');
        } else {
            // Manual Bank Transfer
            Payment::create([
                'payment_code' => 'PAY-' . strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'payment_method' => 'transfer',
                'status' => 'pending',
                'payment_date' => now(),
            ]);

            return redirect()->route('customer.orders.show', $order->id)->with('success', 'Laundry order successfully created. Please complete bank transfer payment and upload your receipt.');
        }
    }

    public function success(Request $request, Order $order)
    {
        // Guard: only process if not already paid (prevents duplicate Finance records on refresh)
        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'waiting_pickup',
            ]);

            // Update associated payment record to success
            $payment = Payment::where('order_id', $order->id)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'success',
                    'payment_date' => now(),
                ]);
            }

            // Automatically record as Income in Finance
            \App\Models\Finance::create([
                'type'        => 'income',
                'amount'      => $order->total_price,
                'category'    => 'Laundry Order',
                'description' => 'Payment for order ' . $order->order_code,
                'date'        => now(),
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Payment successfully confirmed! Awaiting courier assignment.');
    }

    public function cancel(Order $order)
    {
        return redirect()->route('dashboard')->with('error', 'Stripe payment cancelled.');
    }

    public function show(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }
        
        $order->load(['service', 'itemType', 'courier', 'photos', 'messages.sender', 'review']);
        
        $latestLocation = \App\Models\Location::where('order_id', $order->id)
            ->latest()
            ->first();

        if (request()->ajax()) {
            return response()->json([
                'latitude' => $latestLocation->latitude ?? null,
                'longitude' => $latestLocation->longitude ?? null,
            ]);
        }

        return view('customer.orders.show', compact('order', 'latestLocation'));
    }

    public function invoice(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['service', 'itemType', 'customer']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.order', compact('order'));
        return $pdf->download('Invoice-' . $order->order_code . '.pdf');
    }

    public function calculatePrice(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'item_type_id' => 'required|exists:item_types,id',
            'pickup_address' => 'required|string',
            'delivery_address' => 'required|string',
        ]);

        $pricing = $this->getPricingDetails(
            $request->service_id,
            $request->item_type_id,
            $request->pickup_address,
            $request->delivery_address
        );

        if (!$pricing) {
            return response()->json(['error' => 'Unable to calculate pricing.'], 422);
        }

        return response()->json($pricing);
    }

    private function geocode($address)
    {
        $baseLat = -6.1664983;
        $baseLng = 106.5602886;

        try {
            $query = $address;
            if (!str_contains(strtolower($address), 'indonesia')) {
                $query .= ', Tangerang, Indonesia';
            }

            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("https://photon.komoot.io/api/", [
                'q'     => $query,
                'limit' => 1,
                'lat'   => $baseLat,
                'lon'   => $baseLng
            ]);

            if ($response->successful() && count($response->json()['features']) > 0) {
                $feature = $response->json()['features'][0];
                $coords  = $feature['geometry']['coordinates'];
                return [
                    'lat' => $coords[1],
                    'lng' => $coords[0]
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Photon Geocoding failed: ' . $e->getMessage());
        }

        return ['lat' => $baseLat, 'lng' => $baseLng];
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function getPricingDetails($serviceId, $itemTypeId, $pickupAddress, $deliveryAddress)
    {
        $service = Service::find($serviceId);
        $itemType = ItemType::find($itemTypeId);

        if (!$service || !$itemType) {
            return null;
        }

        $service_price = $service->base_price;
        $item_price = $itemType->base_price;

        $pickupCoords = $this->geocode($pickupAddress);
        $deliveryCoords = $this->geocode($deliveryAddress);

        $baseLat = -6.1664983;
        $baseLng = 106.5602886;

        $pickupDistance = $this->calculateDistance($baseLat, $baseLng, $pickupCoords['lat'], $pickupCoords['lng']);
        $deliveryDistance = $this->calculateDistance($baseLat, $baseLng, $deliveryCoords['lat'], $deliveryCoords['lng']);
        $distance = max($pickupDistance, $deliveryDistance);

        $deliveryFeeConfig = \App\Models\DeliveryFee::where('is_active', true)
            ->where('min_distance', '<=', $distance)
            ->where('max_distance', '>=', $distance)
            ->first();

        if (!$deliveryFeeConfig) {
            $deliveryFeeConfig = \App\Models\DeliveryFee::where('is_active', true)
                ->orderByRaw("ABS(max_distance - ?)", [$distance])
                ->first();
        }

        if ($deliveryFeeConfig) {
            $calculated_fee = $distance * $deliveryFeeConfig->fee;
            $shipping_cost = max($deliveryFeeConfig->min_fee, $calculated_fee);
            if ($deliveryFeeConfig->max_fee !== null) {
                $shipping_cost = min($deliveryFeeConfig->max_fee, $shipping_cost);
            }
        } else {
            $shipping_cost = 15000;
        }

        $activeTax = \App\Models\Tax::where('is_active', true)->first();
        $taxName = $activeTax ? $activeTax->name : 'PPN';
        $taxPercentage = $activeTax ? $activeTax->percentage : 10.00;
        
        $tax = ($service_price + $item_price + $shipping_cost) * ($taxPercentage / 100);
        $total_price = $service_price + $item_price + $shipping_cost + $tax;

        return [
            'service_price' => $service_price,
            'item_price' => $item_price,
            'shipping_cost' => $shipping_cost,
            'tax' => $tax,
            'total_price' => $total_price,
            'tax_name' => $taxName,
            'tax_percentage' => $taxPercentage,
            'distance' => $distance,
            'pickup_lat' => $pickupCoords['lat'],
            'pickup_lng' => $pickupCoords['lng'],
            'delivery_lat' => $deliveryCoords['lat'],
            'delivery_lng' => $deliveryCoords['lng'],
        ];
    }
}
