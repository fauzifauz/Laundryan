<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Service;
use App\Models\ItemType;
use App\Models\OrderStatusLog;
use App\Models\Finance;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;

class OrderController extends Controller
{
    /**
     * Build a base64 QR code PNG for the given URL.
     */
    private function generateQrCode(string $url): string
    {
        $opts = [
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => true,
            'scale' => 4,
        ];
        $qr = new QRCode($opts);
        $data = $qr->render($url);
        $qr->clearSegments();
        return (string) $data;
    }

    private function buildBaseQuery(Request $request, bool $includePeriod = false)
    {
        $query = Order::with(['customer', 'service', 'itemType', 'courier', 'pickupCourier', 'deliveryCourier', 'review']);

        // Search Filter (Order Code, Customer Name, Phone, Address)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('pickup_address', 'like', "%{$search}%")
                    ->orWhere('delivery_address', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'active_processing') {
                $query->whereNotIn('status', ['completed', 'cancelled', 'pending_payment']);
            } elseif ($request->input('status') === 'in_progress') {
                $query->whereNotIn('status', ['completed', 'cancelled']);
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        // Courier Assigned Filter
        if ($request->input('courier_assigned') === 'unassigned') {
            $query->where(function ($q) {
                $q->whereNull('pickup_courier_id')
                    ->orWhereNull('delivery_courier_id');
            })->whereNotIn('status', ['completed', 'cancelled']);
        }

        // Period filter (today)
        if ($request->input('filter_period') === 'today') {
            $query->whereDate('created_at', Carbon::today());
        }

        // Courier Filter
        if ($request->filled('courier_id')) {
            $courierId = $request->input('courier_id');
            $query->where(function ($q) use ($courierId) {
                $q->where('courier_id', $courierId)
                    ->orWhere('pickup_courier_id', $courierId)
                    ->orWhere('delivery_courier_id', $courierId);
            });
        }

        // Service Filter
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->input('service_id'));
        }

        // Specific Date Filter (passed from Daily chart click)
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        // Date Range Filter (passed from Weekly chart click)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->input('start_date'))->startOfDay(),
                Carbon::parse($request->input('end_date'))->endOfDay()
            ]);
        }

        // Period Filter (passed from Popular Services click/links)
        if ($request->filled('period')) {
            $period = $request->input('period');
            if ($period === 'daily') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($period === 'weekly') {
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek()->startOfDay(),
                    Carbon::now()->endOfWeek()->endOfDay()
                ]);
            } elseif ($period === 'monthly') {
                $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
            } elseif ($period === 'yearly') {
                $query->whereYear('created_at', Carbon::now()->year);
            }
        }

        // Month & Year Filter
        if ($includePeriod) {
            if ($request->filled('month') && $request->input('month') !== 'all') {
                if (\DB::connection()->getDriverName() === 'sqlite') {
                    $query->whereRaw("strftime('%m', created_at) = ?", [sprintf('%02d', $request->input('month'))]);
                } else {
                    $query->whereMonth('created_at', $request->input('month'));
                }
            }

            if ($request->filled('year') && $request->input('year') !== 'all') {
                if (\DB::connection()->getDriverName() === 'sqlite') {
                    $query->whereRaw("strftime('%Y', created_at) = ?", [$request->input('year')]);
                } else {
                    $query->whereYear('created_at', $request->input('year'));
                }
            }
        }
        
        // Filter by having review (e.g. for Top Performing Couriers redirect)
        if ($request->filled('has_review')) {
            if ($request->input('has_review') == '1') {
                $query->whereHas('review');
            } elseif ($request->input('has_review') == '0') {
                $query->whereDoesntHave('review');
            }
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function index(Request $request)
    {
        $query = $this->buildBaseQuery($request, true);
        $orders = $query->paginate(10)->withQueryString();

        $couriers = User::where('role', 'kurir')->where('status', 'active')->get();

        $qrCodes = [];
        foreach ($orders as $order) {
            $qrCodes[$order->id] = $this->generateQrCode(route('admin.orders.show', $order->id));
        }

        // Available Years
        if (\DB::connection()->getDriverName() === 'sqlite') {
            $years = Order::selectRaw("strftime('%Y', created_at) as year")
                ->whereNotNull('created_at')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();
        } else {
            $years = Order::selectRaw('YEAR(created_at) as year')
                ->whereNotNull('created_at')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();
        }

        if (empty($years)) {
            $years = [now()->year];
        }

        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        // Compute real-time statistics
        $stats = [
            'total_count' => Order::count(),
            'today_count' => Order::whereDate('created_at', Carbon::today())->count(),
            'unassigned_count' => Order::where(function ($q) {
                $q->whereNull('pickup_courier_id')
                    ->orWhereNull('delivery_courier_id');
            })->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'active_processing_count' => Order::whereNotIn('status', ['completed', 'cancelled', 'pending_payment'])->count(),
            'pending_payment_count' => Order::where('status', 'pending_payment')->count(),
            'ready_delivery_count' => Order::where('status', 'ready_for_delivery')->count(),
            'delivering_count' => Order::where('status', 'delivering')->count(),
            'completed_count' => Order::where('status', 'completed')->count(),
        ];

        $selectedCourier = null;
        if ($request->filled('courier_id')) {
            $selectedCourier = User::find($request->input('courier_id'));
        }

        $selectedService = null;
        if ($request->filled('service_id')) {
            $selectedService = Service::find($request->input('service_id'));
        }

        return view('admin.orders.index', compact('orders', 'couriers', 'years', 'months', 'qrCodes', 'stats', 'selectedCourier', 'selectedService'));
    }

    public function create()
    {
        $customers = User::where('role', 'pelanggan')->where('status', 'active')->get();
        $services = Service::where('is_active', true)->get();
        $itemTypes = ItemType::where('is_active', true)->get();
        $couriers = User::where('role', 'kurir')->where('status', 'active')->get();

        return view('admin.orders.create', compact('customers', 'services', 'itemTypes', 'couriers'));
    }

    public function store(Request $request)
    {
        $customerMode = $request->input('customer_mode', 'select');

        // Support both dropdown selection and manual customer entry
        $rules = [
            'service_id' => 'required|exists:services,id',
            'item_type_id' => 'required|exists:item_types,id',
            'notes' => 'nullable|string',
            'soap' => 'nullable|string',
            'fragrance' => 'nullable|string',
            'pickup_courier_id' => 'nullable|exists:users,id',
            'delivery_courier_id' => 'nullable|exists:users,id',
            'status' => 'required|string',
            'payment_status' => 'required|in:pending,paid',
            'payment_method' => 'required|in:cash,transfer,e-wallet',
        ];

        // Validate customer and conditional logistics fields
        if ($customerMode === 'select') {
            $rules['customer_id'] = 'required|exists:users,id';
            $rules['pickup_address'] = 'required|string';
            $rules['delivery_address'] = 'required|string';
            $rules['pickup_time'] = 'required|date';
        } else {
            $rules['customer_name'] = 'required|string|max:255';
            $rules['customer_phone'] = 'nullable|string|max:30';
            $rules['pickup_address'] = 'nullable|string';
            $rules['delivery_address'] = 'nullable|string';
            $rules['pickup_time'] = 'nullable|date';
        }

        $request->validate($rules);

        // Resolve customer_id: find existing or create walk-in guest
        $customerId = null;
        if ($customerMode === 'manual') {
            $customer = null;
            if ($request->filled('customer_phone')) {
                $customer = User::where('phone', $request->customer_phone)
                    ->where('role', 'pelanggan')
                    ->first();
            }

            if (!$customer) {
                $customer = User::create([
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'email' => 'walkin_' . time() . '_' . rand(1000, 9999) . '@laundryan.local',
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                    'role' => 'pelanggan',
                    'status' => 'active',
                ]);
            }
            $customerId = $customer->id;
        } else {
            $customerId = $request->input('customer_id');
        }

        $service = Service::find($request->input('service_id'));
        $itemType = ItemType::find($request->input('item_type_id'));

        $service_price = $service->base_price;
        $item_price = $itemType->base_price;
        $shipping_cost = 15000; // Flat rate
        $tax = ($service_price + $item_price + $shipping_cost) * 0.1;
        $total_price = $service_price + $item_price + $shipping_cost + $tax;

        // Fallback coordinates
        $baseLat = -6.1664983;
        $baseLng = 106.5602886;

        $geocode = function ($address) use ($baseLat, $baseLng) {
            try {
                $query = $address;
                if (!str_contains(strtolower($address), 'indonesia')) {
                    $query .= ', Tangerang, Indonesia';
                }
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get("https://photon.komoot.io/api/", [
                    'q' => $query,
                    'limit' => 1,
                    'lat' => $baseLat,
                    'lon' => $baseLng
                ]);

                if ($response->successful() && count($response->json()['features']) > 0) {
                    $feature = $response->json()['features'][0];
                    $coords = $feature['geometry']['coordinates'];
                    return ['lat' => $coords[1], 'lng' => $coords[0]];
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Photon Geocoding failed: ' . $e->getMessage());
            }
            return ['lat' => $baseLat, 'lng' => $baseLng];
        };

        $pickupAddress = $request->input('pickup_address') ?: '-';
        $deliveryAddress = $request->input('delivery_address') ?: '-';
        $pickupTime = $request->input('pickup_time') ?: now();

        $pickupCoords = $geocode($pickupAddress);
        $deliveryCoords = $geocode($deliveryAddress);

        $service = \App\Models\Service::find($request->input('service_id'));
        $itemType = \App\Models\ItemType::find($request->input('item_type_id'));
        $serviceInitials = $service ? strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $service->name), 0, 2)) : 'SV';
        $itemInitials = $itemType ? strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $itemType->name), 0, 2)) : 'IT';
        $orderCode = 'ORD-' . $serviceInitials . '-' . $itemInitials . '-' . strtoupper(Str::random(6));

        $order = Order::create([
            'order_code' => $orderCode,
            'customer_id' => $customerId,
            'service_id' => $request->input('service_id'),
            'item_type_id' => $request->input('item_type_id'),
            'courier_id' => $request->input('pickup_courier_id') ?: $request->input('delivery_courier_id'), // keep backward compatibility
            'pickup_courier_id' => $request->input('pickup_courier_id'),
            'delivery_courier_id' => $request->input('delivery_courier_id'),
            'pickup_address' => $pickupAddress,
            'pickup_lat' => $pickupCoords['lat'],
            'pickup_lng' => $pickupCoords['lng'],
            'delivery_address' => $deliveryAddress,
            'delivery_lat' => $deliveryCoords['lat'],
            'delivery_lng' => $deliveryCoords['lng'],
            'pickup_time' => $pickupTime,
            'notes' => $request->input('notes'),
            'soap' => $request->input('soap'),
            'fragrance' => $request->input('fragrance'),
            'service_price' => $service_price,
            'item_price' => $item_price,
            'shipping_cost' => $shipping_cost,
            'tax' => $tax,
            'total_price' => $total_price,
            'status' => $request->input('status'),
            'payment_status' => $request->input('payment_status'),
            'payment_method' => $request->input('payment_method'),
        ]);

        // Record status log
        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'user_id' => auth()->id(),
        ]);

        // Finance entry if paid
        if ($order->payment_status === 'paid') {
            Finance::create([
                'type' => 'income',
                'amount' => $order->total_price,
                'category' => 'Laundry Order',
                'description' => 'Payment for order ' . $order->order_code,
                'date' => now(),
            ]);
        }

        return redirect()->route('admin.orders.index')->with('success', "Order #{$order->order_code} created successfully.");
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'service', 'itemType', 'courier', 'pickupCourier', 'deliveryCourier', 'photos.user', 'statusLogs.user', 'review', 'messages.sender', 'payments']);

        $couriers = User::where('role', 'kurir')->where('status', 'active')->get();
        $qrCode = $this->generateQrCode(route('admin.orders.show', $order->id));

        return view('admin.orders.show', compact('order', 'couriers', 'qrCode'));
    }

    public function edit(Order $order)
    {
        $order->load(['customer', 'service', 'itemType', 'pickupCourier', 'deliveryCourier']);
        $customers = User::where('role', 'pelanggan')->where('status', 'active')->get();
        $services = Service::where('is_active', true)->get();
        $itemTypes = ItemType::where('is_active', true)->get();
        $couriers = User::where('role', 'kurir')->where('status', 'active')->get();

        return view('admin.orders.edit', compact('order', 'customers', 'services', 'itemTypes', 'couriers'));
    }

    public function update(Request $request, Order $order)
    {
        $customer = User::find($request->customer_id);
        $isWalkin = $customer && str_contains($customer->email ?? '', 'walkin_');

        $rules = [
            'customer_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'item_type_id' => 'required|exists:item_types,id',
            'notes' => 'nullable|string',
            'soap' => 'nullable|string',
            'fragrance' => 'nullable|string',
            'pickup_courier_id' => 'nullable|exists:users,id',
            'delivery_courier_id' => 'nullable|exists:users,id',
            'status' => 'required|string',
            'payment_status' => 'required|in:pending,paid',
            'payment_method' => 'required|in:cash,transfer,e-wallet',
        ];

        if ($isWalkin) {
            $rules['pickup_address'] = 'nullable|string';
            $rules['delivery_address'] = 'nullable|string';
            $rules['pickup_time'] = 'nullable|date';
        } else {
            $rules['pickup_address'] = 'required|string';
            $rules['delivery_address'] = 'required|string';
            $rules['pickup_time'] = 'required|date';
        }

        $request->validate($rules);

        $service = Service::find($request->service_id);
        $itemType = ItemType::find($request->item_type_id);

        $service_price = $service->base_price;
        $item_price = $itemType->base_price;
        $shipping_cost = 15000;
        $tax = ($service_price + $item_price + $shipping_cost) * 0.1;
        $total_price = $service_price + $item_price + $shipping_cost + $tax;

        $oldStatus = $order->status;
        $oldPaymentStatus = $order->payment_status;

        $pickupAddress = $request->pickup_address ?: '-';
        $deliveryAddress = $request->delivery_address ?: '-';
        $pickupTime = $request->pickup_time ?: now();

        $order->update([
            'customer_id' => $request->customer_id,
            'service_id' => $request->service_id,
            'item_type_id' => $request->item_type_id,
            'courier_id' => $request->pickup_courier_id ?: $request->delivery_courier_id,
            'pickup_courier_id' => $request->pickup_courier_id,
            'delivery_courier_id' => $request->delivery_courier_id,
            'pickup_address' => $pickupAddress,
            'delivery_address' => $deliveryAddress,
            'pickup_time' => $pickupTime,
            'notes' => $request->notes,
            'soap' => $request->soap,
            'fragrance' => $request->fragrance,
            'service_price' => $service_price,
            'item_price' => $item_price,
            'shipping_cost' => $shipping_cost,
            'tax' => $tax,
            'total_price' => $total_price,
            'status' => $request->status,
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method,
        ]);

        if ($oldStatus !== $order->status) {
            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'user_id' => auth()->id(),
            ]);
            broadcast(new \App\Events\OrderStatusUpdated($order))->toOthers();
        }

        if ($oldPaymentStatus !== 'paid' && $order->payment_status === 'paid') {
            Finance::create([
                'type' => 'income',
                'amount' => $order->total_price,
                'category' => 'Laundry Order',
                'description' => 'Payment for order ' . $order->order_code,
                'date' => now(),
            ]);
        }

        return redirect()->route('admin.orders.index')
            ->with('success', 'Order updated successfully.')
            ->with('action_type', 'order_updated')
            ->with('target_order_id', $order->id);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully.'
            ]);
        }
        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
    }

    public function assignCourier(Request $request, Order $order)
    {
        $request->validate([
            'pickup_courier_id' => 'nullable|exists:users,id',
            'delivery_courier_id' => 'nullable|exists:users,id',
        ]);

        $order->update([
            'pickup_courier_id' => $request->pickup_courier_id,
            'delivery_courier_id' => $request->delivery_courier_id,
            'courier_id' => $request->pickup_courier_id ?: $request->delivery_courier_id,
        ]);

        return redirect()->back()
            ->with('success', 'Courier assignment updated successfully.')
            ->with('action_type', 'courier_assigned')
            ->with('target_order_id', $order->id);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $request->status,
        ]);

        if ($oldStatus !== $order->status) {
            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'user_id' => auth()->id(),
            ]);
            broadcast(new \App\Events\OrderStatusUpdated($order))->toOthers();
        }

        return redirect()->back()
            ->with('success', 'Order status updated successfully.')
            ->with('action_type', 'status_updated')
            ->with('target_order_id', $order->id);
    }

    private function getPeriodLabel(Request $request): string
    {
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        $month = $request->input('month');
        $year = $request->input('year');

        if ($month && $month !== 'all' && $year && $year !== 'all') {
            return $months[$month] . ' ' . $year;
        } elseif ($month && $month !== 'all') {
            return $months[$month];
        } elseif ($year && $year !== 'all') {
            return 'Year ' . $year;
        }

        return 'All Time';
    }

    public function exportPdf(Request $request)
    {
        $query = $this->buildBaseQuery($request, true);
        $orders = $query->get();
        $period = $this->getPeriodLabel($request);

        $qrCodes = [];
        foreach ($orders as $order) {
            $qrCodes[$order->id] = $this->generateQrCode(route('admin.orders.show', $order->id));
        }

        $pdf = Pdf::loadView('admin.exports.orders_pdf', compact('orders', 'period', 'qrCodes'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        $slugPeriod = str_replace(' ', '_', strtolower($period));
        return $pdf->download("laundryan_orders_report_{$slugPeriod}_" . date('Ymd') . ".pdf");
    }

    public function exportCsv(Request $request)
    {
        $query = $this->buildBaseQuery($request, true);
        $orders = $query->get();
        $period = $this->getPeriodLabel($request);
        $slugPeriod = str_replace(' ', '_', strtolower($period));
        $filename = "laundryan_orders_report_{$slugPeriod}_" . date('Ymd') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'Order Code',
            'Date',
            'Customer',
            'Phone Number',
            'Email',
            'Pickup Address',
            'Delivery Address',
            'Service',
            'Item Type',
            'Soap',
            'Fragrance',
            'Pickup Courier',
            'Delivery Courier',
            'Status',
            'Payment Method',
            'Total Price'
        ];

        $callback = function () use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $statusTranslations = [
                'pending_payment' => 'Pending Payment',
                'waiting_pickup' => 'Waiting Pickup',
                'picking_up' => 'Picking Up',
                'picked_up' => 'Picked Up',
                'in_transit_to_laundry' => 'In Transit to Laundry',
                'arrived_at_laundry' => 'Arrived at Laundry',
                'washing' => 'Washing',
                'drying_ironing' => 'Drying & Ironing',
                'packing' => 'Packing',
                'ready_for_delivery' => 'Ready for Delivery',
                'delivering' => 'Delivering',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_code,
                    $order->created_at->format('d/m/Y H:i'),
                    $order->customer ? $order->customer->name : '-',
                    $order->customer ? $order->customer->phone : '-',
                    $order->customer ? $order->customer->email : '-',
                    $order->pickup_address,
                    $order->delivery_address,
                    $order->service ? $order->service->name : '-',
                    $order->itemType ? $order->itemType->name : '-',
                    $order->soap ?: '-',
                    $order->fragrance ?: '-',
                    $order->pickupCourier ? $order->pickupCourier->name : '-',
                    $order->deliveryCourier ? $order->deliveryCourier->name : '-',
                    $statusTranslations[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status)),
                    strtoupper($order->payment_method ?: 'CASH'),
                    $order->total_price
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
