<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\OrderStatusLog;
use App\Models\User;
use App\Models\Service;
use App\Models\ItemType;
use App\Models\Review;
use App\Models\Finance;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Output\QRGdImagePNG;

class OrderController extends Controller
{
    private const EMPLOYEE_ALLOWED_STATUSES = [
        'arrived_at_laundry',
        'washing',
        'drying_ironing',
        'packing',
        'ready_for_delivery',
    ];

    private const EMPLOYEE_FORBIDDEN_FILTER_STATUSES = [
        'pending_payment',
        'waiting_pickup',
        'picking_up',
        'picked_up',
        'in_transit_to_laundry',
        'cancelled',
    ];

    public function index(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $servicePeriod = $request->get('service_period', 'weekly');

        // Original Employee orders in process (pipeline)
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

        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'completed')->count();
        $inProgressOrders = Order::whereNotIn('status', ['completed', 'cancelled'])->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        // Sync order finances before calculating today's revenue
        $existingCodes = Finance::where('type', 'income')
            ->where('description', 'like', 'Payment for order %')
            ->pluck('description')
            ->map(function ($desc) {
                return trim(str_replace('Payment for order ', '', $desc));
            })
            ->filter()
            ->toArray();

        $missingOrders = Order::where('payment_status', 'paid')
            ->whereNotIn('order_code', $existingCodes)
            ->get();

        foreach ($missingOrders as $order) {
            Finance::create([
                'type' => 'income',
                'amount' => $order->total_price,
                'category' => 'Laundry Order',
                'description' => 'Payment for order ' . $order->order_code,
                'date' => $order->created_at ? $order->created_at->toDateString() : now()->toDateString(),
                'payment_method' => $order->payment_method ?: 'cash',
            ]);
        }

        // Active couriers count
        $activeCouriersCount = User::where('role', 'kurir')
            ->where('status', 'active')
            ->whereHas('locations', function ($query) {
                $query->where('updated_at', '>=', now()->subMinutes(5));
            })
            ->whereHas('courierOrders', function ($query) {
                $query->whereIn('status', [
                    'waiting_pickup', 'picking_up', 'picked_up', 'in_transit_to_laundry', 
                    'ready_for_delivery', 'delivering',
                    'penjemputan', 'dijemput', 'diantar', 'pengantaran', 'diantarkan'
                ]);
            })
            ->count();

        $todayRevenue = Finance::where('type', 'income')
            ->whereDate('date', Carbon::today())
            ->sum('amount');

        $rawStatusBreakdown = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusProses = [
            'Arrived at Laundry' => $rawStatusBreakdown['arrived_at_laundry'] ?? 0,
            'Washing' => $rawStatusBreakdown['washing'] ?? 0,
            'Drying & Ironing' => $rawStatusBreakdown['drying_ironing'] ?? 0,
            'Packing' => $rawStatusBreakdown['packing'] ?? 0,
            'Completed' => $rawStatusBreakdown['completed'] ?? 0,
        ];

        $avgRating = Review::avg('rating') ?? 0;

        // Statistics
        $stats = [
            'daily' => Order::whereDate('created_at', Carbon::today())->count(),
            'weekly' => Order::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'monthly' => Order::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count(),
            'yearly' => Order::whereYear('created_at', Carbon::now()->year)->count(),
        ];
        
        // Chart Data based on Period
        $chartData = ['labels' => [], 'data' => [], 'full_dates' => [], 'filter_type' => $period];
        
        if ($period === 'daily') {
            $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
            for ($i = 0; $i <= 6; $i++) {
                $day = $startOfWeek->copy()->addDays($i);
                $chartData['labels'][]     = $day->format('D, d M');
                $chartData['full_dates'][] = $day->toDateString();
                $chartData['data'][]       = Order::whereDate('created_at', $day->toDateString())->count();
            }
        } elseif ($period === 'weekly') {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth   = Carbon::now()->endOfMonth();
            $weekStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
            $weekNum = 1;
            while ($weekStart->lte($endOfMonth)) {
                $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
                $labelStart = $weekStart->lt($startOfMonth) ? $startOfMonth->copy() : $weekStart->copy();
                $labelEnd   = $weekEnd->gt($endOfMonth)   ? $endOfMonth->copy()   : $weekEnd->copy();

                $chartData['labels'][]     = 'Wk ' . $weekNum . ' (' . $labelStart->format('d') . '–' . $labelEnd->format('d M') . ')';
                $chartData['full_dates'][] = $weekStart->copy()->max($startOfMonth)->toDateString() . '|' . $weekEnd->copy()->min($endOfMonth)->toDateString();
                $chartData['data'][]       = Order::whereBetween('created_at', [
                    $weekStart->copy()->max($startOfMonth)->startOfDay(),
                    $weekEnd->copy()->min($endOfMonth)->endOfDay()
                ])->count();

                $weekStart->addWeek();
                $weekNum++;
            }
        } elseif ($period === 'monthly') {
            for ($i = 1; $i <= 12; $i++) {
                $month = Carbon::now()->month($i)->year(Carbon::now()->year);
                $chartData['labels'][]     = $month->format('M');
                $chartData['full_dates'][] = $month->format('Y-m');
                $chartData['data'][]       = Order::whereMonth('created_at', $i)
                                                    ->whereYear('created_at', Carbon::now()->year)
                                                    ->count();
            }
        } elseif ($period === 'yearly') {
            $oldestOrder = Order::orderBy('created_at', 'asc')->first();
            $startYear   = $oldestOrder ? (int) $oldestOrder->created_at->format('Y') : Carbon::now()->year;
            $endYear     = Carbon::now()->year;

            for ($y = $startYear; $y <= $endYear; $y++) {
                $chartData['labels'][]     = (string) $y;
                $chartData['full_dates'][] = $y;
                $chartData['data'][]       = Order::whereYear('created_at', $y)->count();
            }
        }

        // Latest Orders for Live Feed
        $latestOrders = Order::with(['customer', 'service', 'itemType'])
            ->latest()
            ->take(25)
            ->get();

        // Service Distribution (Pie Chart)
        $serviceDistributionQuery = Order::groupBy('service_id')
            ->selectRaw('service_id, count(*) as count')
            ->with('service');
            
        if ($servicePeriod === 'daily') {
            $serviceDistributionQuery->whereDate('created_at', Carbon::today());
        } elseif ($servicePeriod === 'monthly') {
            $serviceDistributionQuery->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        } elseif ($servicePeriod === 'yearly') {
            $serviceDistributionQuery->whereYear('created_at', Carbon::now()->year);
        } else { // weekly
            $serviceDistributionQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        }
            
        $serviceDistribution = $serviceDistributionQuery->get()
            ->map(function($item) {
                return [
                    'service_id' => $item->service_id,
                    'label' => $item->service->name ?? 'Unknown',
                    'count' => $item->count
                ];
            });

        $receivedCount = $rawStatusBreakdown['arrived_at_laundry'] ?? 0;
        $washingCount = $rawStatusBreakdown['washing'] ?? 0;
        $ironingCount = $rawStatusBreakdown['drying_ironing'] ?? 0;
        $packingCount = $rawStatusBreakdown['packing'] ?? 0;
        $readyCount = $rawStatusBreakdown['ready_for_delivery'] ?? 0;
        $hour = Carbon::now()->hour;
        if ($hour >= 5 && $hour < 12) {
            $greeting = 'GOOD MORNING';
        } elseif ($hour >= 12 && $hour < 17) {
            $greeting = 'GOOD AFTERNOON';
        } elseif ($hour >= 17 && $hour < 21) {
            $greeting = 'GOOD EVENING';
        } else {
            $greeting = 'GOOD NIGHT';
        }

        return view('karyawan.dashboard', compact(
            'orders', 'totalOrders', 'completedOrders', 'inProgressOrders', 'statusProses',
            'avgRating', 'stats', 'chartData', 'latestOrders', 'period', 'servicePeriod',
            'serviceDistribution', 'greeting', 'cancelledOrders', 'activeCouriersCount', 'todayRevenue',
            'receivedCount', 'washingCount', 'ironingCount', 'packingCount', 'readyCount'
        ));
    }

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

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $statusFilter = $request->input('status');
            if (in_array($statusFilter, self::EMPLOYEE_FORBIDDEN_FILTER_STATUSES, true)) {
                // Ignore forbidden status filters for employee panel
            } elseif ($statusFilter === 'active_processing') {
                $query->whereNotIn('status', ['completed', 'cancelled', 'pending_payment']);
            } elseif ($statusFilter === 'in_progress') {
                $query->whereNotIn('status', ['completed', 'cancelled']);
            } elseif ($statusFilter === 'in_queue') {
                $query->whereIn('status', [
                    'picked_up', 
                    'in_transit_to_laundry', 
                    'arrived_at_laundry', 
                    'washing', 
                    'drying_ironing', 
                    'packing', 
                    'ready_for_delivery'
                ]);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        if ($request->input('courier_assigned') === 'unassigned') {
            $query->where(function ($q) {
                $q->whereNull('pickup_courier_id')
                    ->orWhereNull('delivery_courier_id');
            })->whereNotIn('status', ['completed', 'cancelled']);
        }

        if ($request->input('filter_period') === 'today') {
            $query->whereDate('created_at', Carbon::today());
        }

        if ($request->filled('courier_id')) {
            $courierId = $request->input('courier_id');
            $query->where(function ($q) use ($courierId) {
                $q->where('courier_id', $courierId)
                    ->orWhere('pickup_courier_id', $courierId)
                    ->orWhere('delivery_courier_id', $courierId);
            });
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->input('service_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->input('start_date'))->startOfDay(),
                Carbon::parse($request->input('end_date'))->endOfDay(),
            ]);
        }

        if ($request->filled('period')) {
            $period = $request->input('period');
            if ($period === 'daily') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($period === 'weekly') {
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek()->startOfDay(),
                    Carbon::now()->endOfWeek()->endOfDay(),
                ]);
            } elseif ($period === 'monthly') {
                $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
            } elseif ($period === 'yearly') {
                $query->whereYear('created_at', Carbon::now()->year);
            }
        }

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

        if ($request->filled('has_review')) {
            if ($request->input('has_review') == '1') {
                $query->whereHas('review');
            } elseif ($request->input('has_review') == '0') {
                $query->whereDoesntHave('review');
            }
        }

        return $query->orderBy('created_at', 'desc');
    }

    private function getPipelineCounts(): array
    {
        $breakdown = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'arrived_at_laundry' => (int) ($breakdown['arrived_at_laundry'] ?? 0),
            'washing' => (int) ($breakdown['washing'] ?? 0),
            'drying_ironing' => (int) ($breakdown['drying_ironing'] ?? 0),
            'packing' => (int) ($breakdown['packing'] ?? 0),
            'ready_for_delivery' => (int) ($breakdown['ready_for_delivery'] ?? 0),
        ];
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', self::EMPLOYEE_ALLOWED_STATUSES),
            'photo' => 'nullable|image|max:2048',
        ]);

        $oldStatus = $order->status;
        $order->update([
            'status' => $request->status,
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('order_photos', 'public');
            OrderPhoto::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'photo_path' => $path,
                'context' => $request->status,
            ]);
        }

        if ($oldStatus !== $order->status) {
            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'user_id' => auth()->id(),
            ]);
            $counts = $this->getPipelineCounts();
            broadcast(new \App\Events\OrderStatusUpdated($order, $counts))->toOthers();
        }

        $statusLabels = [
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
        $label = $statusLabels[$request->status] ?? ucfirst(str_replace('_', ' ', $request->status));
        $successMessage = "Order status updated to {$label} successfully.";

        $payload = [
            'success' => true,
            'message' => $successMessage,
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'order_code' => $order->order_code,
            ],
            'counts' => $this->getPipelineCounts(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        return redirect()->back()
            ->with('success', $successMessage)
            ->with('action_type', 'status_updated')
            ->with('target_order_id', $order->id);
    }

    public function exportPdf(Request $request)
    {
        $month = $request->get('month', 'all');
        $year  = $request->get('year', 'all');

        $query = Order::with(['customer', 'service', 'itemType', 'courier']);
        if ($month !== 'all') $query->whereMonth('created_at', $month);
        if ($year  !== 'all') $query->whereYear('created_at', $year);

        $period = 'All Time';
        if ($month !== 'all' && $year !== 'all') {
            $period = Carbon::create($year, $month)->format('F Y');
        } elseif ($month !== 'all') {
            $period = Carbon::create(2026, $month)->format('F') . ' (All Years)';
        } elseif ($year !== 'all') {
            $period = 'Year ' . $year;
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('admin.exports.orders_pdf', compact('orders', 'period'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $label = str_replace(' ', '_', strtolower($period));
        return $pdf->download("laundryan_orders_{$label}_" . date('Ymd') . ".pdf");
    }

    public function exportCsv(Request $request)
    {
        $month = $request->get('month', 'all');
        $year  = $request->get('year', 'all');

        $query = Order::with(['customer', 'service', 'itemType']);
        if ($month !== 'all') $query->whereMonth('created_at', $month);
        if ($year  !== 'all') $query->whereYear('created_at', $year);

        $period = 'All Time';
        if ($month !== 'all' && $year !== 'all') {
            $period = Carbon::create($year, $month)->format('F Y');
        } elseif ($month !== 'all') {
            $period = Carbon::create(2026, $month)->format('F') . ' (All Years)';
        } elseif ($year !== 'all') {
            $period = 'Year ' . $year;
        }

        $orders = $query->orderBy('created_at', 'desc')->get();
        $label  = str_replace(' ', '_', strtolower($period));
        $filename = "laundryan_orders_{$label}_" . date('Ymd') . ".csv";

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order Code','Date','Customer','Phone','Pickup Address','Delivery Address','Service','Item Type','Soap','Fragrance','Notes','Status','Payment Method','Total Price']);
            foreach ($orders as $o) {
                fputcsv($file, [
                    $o->order_code,
                    $o->created_at->format('d/m/Y H:i'),
                    $o->customer->name ?? '-',
                    $o->customer->phone ?? '-',
                    $o->pickup_address,
                    $o->delivery_address,
                    $o->service->name ?? '-',
                    $o->itemType->name ?? '-',
                    $o->soap ?: '-',
                    $o->fragrance ?: '-',
                    $o->notes ?: '-',
                    strtoupper(str_replace('_', ' ', $o->status)),
                    strtoupper($o->payment_method ?: 'CASH'),
                    $o->total_price,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function ordersIndex(Request $request)
    {
        $query = $this->buildBaseQuery($request, true);
        $orders = $query->paginate(10)->withQueryString();

        $couriers = User::where('role', 'kurir')->where('status', 'active')->get();

        $qrCodes = [];
        foreach ($orders as $order) {
            $qrCodes[$order->id] = $this->generateQrCode(route('karyawan.orders.show', $order->id));
        }

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
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        $stats = [
            'total_count' => Order::count(),
            'today_count' => Order::whereDate('created_at', Carbon::today())->count(),
            'unassigned_count' => Order::where(function ($q) {
                $q->whereNull('pickup_courier_id')
                    ->orWhereNull('delivery_courier_id');
            })->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'active_processing_count' => Order::whereNotIn('status', ['completed', 'cancelled', 'pending_payment'])->count(),
            'arrived_at_laundry_count' => Order::where('status', 'arrived_at_laundry')->count(),
            'ready_delivery_count' => Order::where('status', 'ready_for_delivery')->count(),
            'delivering_count' => Order::where('status', 'delivering')->count(),
            'completed_count' => Order::where('status', 'completed')->count(),
        ];

        $selectedCourier = $request->filled('courier_id') ? User::find($request->input('courier_id')) : null;
        $selectedService = $request->filled('service_id') ? Service::find($request->input('service_id')) : null;

        return view('karyawan.orders.index', compact(
            'orders', 'couriers', 'years', 'months', 'qrCodes', 'stats', 'selectedCourier', 'selectedService'
        ));
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'service', 'itemType', 'courier', 'pickupCourier', 'deliveryCourier', 'photos.user', 'statusLogs.user', 'review', 'messages.sender', 'payments']);

        $couriers = User::where('role', 'kurir')->where('status', 'active')->get();
        $qrCode = $this->generateQrCode(route('karyawan.orders.show', $order->id));

        return view('karyawan.orders.show', compact('order', 'couriers', 'qrCode'));
    }

    public function create()
    {
        abort(403, 'Employees are not allowed to create orders.');
    }

    public function store(Request $request)
    {
        abort(403, 'Employees are not allowed to create orders.');
    }

    public function edit(Order $order)
    {
        abort(403, 'Employees are not allowed to edit orders.');
    }

    public function update(Request $request, Order $order)
    {
        abort(403, 'Employees are not allowed to edit orders.');
    }

    public function destroy(Order $order)
    {
        abort(403, 'Employees are not allowed to delete orders.');
    }

    public function assignCourier(Request $request, Order $order)
    {
        abort(403, 'Employees are not allowed to assign couriers.');
    }

    private function getPeriodLabel(Request $request): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
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

    public function ordersExportPdf(Request $request)
    {
        $query = $this->buildBaseQuery($request, true);
        $orders = $query->get();
        $period = $this->getPeriodLabel($request);

        $qrCodes = [];
        foreach ($orders as $order) {
            $qrCodes[$order->id] = $this->generateQrCode(route('karyawan.orders.show', $order->id));
        }

        $pdf = Pdf::loadView('admin.exports.orders_pdf', compact('orders', 'period', 'qrCodes'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        $slugPeriod = str_replace(' ', '_', strtolower($period));

        return $pdf->download("laundryan_orders_report_{$slugPeriod}_" . date('Ymd') . '.pdf');
    }

    public function ordersExportCsv(Request $request)
    {
        $query = $this->buildBaseQuery($request, true);
        $orders = $query->get();
        $period = $this->getPeriodLabel($request);
        $slugPeriod = str_replace(' ', '_', strtolower($period));
        $filename = "laundryan_orders_report_{$slugPeriod}_" . date('Ymd') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Order Code', 'Date', 'Customer', 'Phone Number', 'Email',
            'Pickup Address', 'Delivery Address', 'Service', 'Item Type',
            'Soap', 'Fragrance', 'Pickup Courier', 'Delivery Courier',
            'Status', 'Payment Method', 'Total Price',
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
                    $order->total_price,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
