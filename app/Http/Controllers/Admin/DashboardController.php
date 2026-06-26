<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use App\Models\Finance;
use App\Models\Payment;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $servicePeriod = $request->get('service_period', 'weekly');
        $exportPeriod = $request->get('export_period', 'weekly');

        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'completed')->count();
        $inProgressOrders = Order::whereNotIn('status', ['completed', 'cancelled'])->count();

        // New KPI Stats
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

        // Active couriers count matching tracking page logic
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

        $pendingPaymentOrders = Payment::where('status', 'pending')->count();

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

        $employees = User::where('role', 'karyawan')->get();
        $couriers = User::where('role', 'kurir')->where('status', 'active')->get()->map(function($courier) {
            $latestLocation = \App\Models\Location::where('user_id', $courier->id)->latest()->first();
            $courier->lat = $latestLocation ? $latestLocation->latitude : null;
            $courier->lng = $latestLocation ? $latestLocation->longitude : null;
            return $courier;
        });

        $topCouriers = User::where('role', 'kurir')->get()->map(function($courier) {
            $avg = Review::whereHas('order', function($q) use ($courier) {
                $q->where('courier_id', $courier->id);
            })->avg('rating');
            $courier->avg_rating = $avg ?? 0;
            return $courier;
        })->sortByDesc('avg_rating')->take(4);

        $avgRating = Review::avg('rating') ?? 0;

        // Statistics
        $stats = [
            'daily' => Order::whereDate('created_at', Carbon::today())->count(),
            'weekly' => Order::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'monthly' => Order::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count(),
            'yearly' => Order::whereYear('created_at', Carbon::now()->year)->count(),
        ];
        
        // Chart Data based on Period (Statistics Filter)
        $chartData = ['labels' => [], 'data' => [], 'full_dates' => [], 'filter_type' => $period];
        
        if ($period === 'daily') {
            // Show Mon–Sun of the current week
            $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY); // Monday
            for ($i = 0; $i <= 6; $i++) {
                $day = $startOfWeek->copy()->addDays($i);
                $chartData['labels'][]     = $day->format('D, d M'); // e.g. Mon, 19 May
                $chartData['full_dates'][] = $day->toDateString();
                $chartData['data'][]       = Order::whereDate('created_at', $day->toDateString())->count();
            }
        } elseif ($period === 'weekly') {
            // Show each week (Mon–Sun) within the current running month
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth   = Carbon::now()->endOfMonth();

            // Find the first Monday on or before the 1st of the month
            $weekStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);

            $weekNum = 1;
            while ($weekStart->lte($endOfMonth)) {
                $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

                // Clamp to month boundaries for label
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
            // Show all 12 months of the current year
            for ($i = 1; $i <= 12; $i++) {
                $month = Carbon::now()->month($i)->year(Carbon::now()->year);
                $chartData['labels'][]     = $month->format('M'); // Jan, Feb, ...
                $chartData['full_dates'][] = $month->format('Y-m');
                $chartData['data'][]       = Order::whereMonth('created_at', $i)
                                                    ->whereYear('created_at', Carbon::now()->year)
                                                    ->count();
            }
        } elseif ($period === 'yearly') {
            // Show all years from the oldest order record to the current year
            $oldestOrder = Order::orderBy('created_at', 'asc')->first();
            $startYear   = $oldestOrder ? (int) $oldestOrder->created_at->format('Y') : Carbon::now()->year;
            $endYear     = Carbon::now()->year;

            for ($y = $startYear; $y <= $endYear; $y++) {
                $chartData['labels'][]     = (string) $y;
                $chartData['full_dates'][] = $y;
                $chartData['data'][]       = Order::whereYear('created_at', $y)->count();
            }
        }


        // Latest Orders for Live Feed (increased to 25 for scrollability)
        $latestOrders = Order::with(['customer', 'service', 'itemType'])
            ->latest()
            ->take(25)
            ->get();

        // Latest Reviews
        $latestReviews = Review::with(['order.customer'])
            ->latest()
            ->take(5)
            ->get();

        // Service Distribution (Pie Chart) - Filtered by service_period
        $serviceDistributionQuery = Order::groupBy('service_id')
            ->selectRaw('service_id, count(*) as count')
            ->with('service');
            
        $this->applyPeriodFilter($serviceDistributionQuery, $servicePeriod);
            
        $serviceDistribution = $serviceDistributionQuery->get()
            ->map(function($item) {
                return [
                    'service_id' => $item->service_id,
                    'label' => $this->translateTerm($item->service->name ?? 'Unknown'),
                    'count' => $item->count
                ];
            });

        // Customer Growth Metrics
        $newCustomersThisWeek = User::where('role', 'pelanggan')
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->count();

        $totalCustomersWithOrders = Order::distinct('customer_id')->count();
        $returningCustomers = Order::select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('count(*) > 1')
            ->get()
            ->count();
        
        $retentionRate = $totalCustomersWithOrders > 0 
            ? round(($returningCustomers / $totalCustomersWithOrders) * 100, 1) 
            : 0;

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

        return view('admin.dashboard', compact(
            'totalOrders', 'completedOrders', 'inProgressOrders', 'statusProses',
            'employees', 'couriers', 'topCouriers', 'avgRating', 'stats', 'chartData',
            'latestOrders', 'latestReviews', 'period', 'servicePeriod', 'exportPeriod',
            'serviceDistribution', 'newCustomersThisWeek', 'retentionRate', 'greeting',
            'cancelledOrders', 'pendingPaymentOrders', 'activeCouriersCount', 'todayRevenue'
        ));
    }

    public function exportPdf(Request $request)
    {
        $month = $request->get('month', 'all');
        $year = $request->get('year', 'all');
        $query = Order::with(['customer', 'service', 'courier']);

        if ($month !== 'all') {
            $query->whereMonth('created_at', $month);
        }
        if ($year !== 'all') {
            $query->whereYear('created_at', $year);
        }

        if ($month === 'all' && $year === 'all') {
            if ($request->filled('period') && $request->get('period') !== 'all') {
                $period = $request->get('period');
                $this->applyPeriodFilter($query, $period);
            } else {
                $period = 'All Time';
            }
        } else {
            $periodLabel = '';
            if ($month !== 'all') {
                $periodLabel .= Carbon::create(2026, $month)->format('F');
            }
            if ($year !== 'all') {
                $periodLabel .= ($periodLabel ? ' ' : '') . $year;
            }
            $period = $periodLabel ?: 'All Time';
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $qrOptions = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => true,
            'scale' => 4,
        ]);
        $qrGenerator = new QRCode($qrOptions);

        $qrCodes = [];
        foreach ($orders as $order) {
            $qrCodes[$order->id] = $qrGenerator->render(route('orders.scan', $order->id));
            $qrGenerator->clearSegments();
        }

        $pdf = Pdf::loadView('admin.exports.orders_pdf', compact('orders', 'period', 'qrCodes'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isRemoteEnabled' => true,
                      'isHtml5ParserEnabled' => true,
                  ]);
                  
        $formattedPeriod = str_replace(' ', '_', strtolower($period));
        return $pdf->download("laundryan_report_{$formattedPeriod}_" . date('Ymd') . ".pdf");
    }

    public function exportCsv(Request $request)
    {
        $month = $request->get('month', 'all');
        $year = $request->get('year', 'all');
        $query = Order::with(['customer', 'service', 'courier']);

        if ($month !== 'all') {
            $query->whereMonth('created_at', $month);
        }
        if ($year !== 'all') {
            $query->whereYear('created_at', $year);
        }

        if ($month === 'all' && $year === 'all') {
            if ($request->filled('period') && $request->get('period') !== 'all') {
                $period = $request->get('period');
                $this->applyPeriodFilter($query, $period);
            } else {
                $period = 'All Time';
            }
        } else {
            $periodLabel = '';
            if ($month !== 'all') {
                $periodLabel .= Carbon::create(2026, $month)->format('F');
            }
            if ($year !== 'all') {
                $periodLabel .= ($periodLabel ? ' ' : '') . $year;
            }
            $period = $periodLabel ?: 'All Time';
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $formattedPeriod = str_replace(' ', '_', strtolower($period));
        $filename = "laundryan_orders_{$formattedPeriod}_" . date('Ymd') . ".csv";

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = [
            'Order Code', 'Date', 'Customer', 'Phone Number', 
            'Pickup Address', 'Delivery Address', 'Service', 'Item Type', 
            'Soap', 'Fragrance', 'Notes', 'Status', 'Payment Method', 'Total Price'
        ];

        $callback = function() use($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_code,
                    $order->created_at->format('d/m/Y H:i'),
                    $order->customer ? $order->customer->name : '-',
                    $order->customer ? $order->customer->phone : '-',
                    $order->pickup_address,
                    $order->delivery_address,
                    $this->translateTerm($order->service ? $order->service->name : '-'),
                    $this->translateTerm($order->itemType ? $order->itemType->name : '-'),
                    $this->translateTerm($order->soap ?: '-'),
                    $this->translateTerm($order->fragrance ?: '-'),
                    $order->notes ?: '-',
                    $this->translateTerm($order->status),
                    $this->translateTerm($order->payment_method ?: 'CASH'),
                    $order->total_price
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function applyPeriodFilter($query, $period)
    {
        if ($period === 'daily') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($period === 'monthly') {
            $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        } elseif ($period === 'yearly') {
            $query->whereYear('created_at', Carbon::now()->year);
        } else { // weekly
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        }
    }

    /**
     * Translate common laundry terms from ID to EN
     */
    private function translateTerm($term)
    {
        if (!$term) return '-';

        $map = [
            // Services
            'Cuci Lipat' => 'Wash & Fold',
            'Cuci Setrika' => 'Wash & Iron',
            'Express 6 Jam' => '6-Hour Express',
            'Cuci Kering' => 'Dry Cleaning',
            'Setrika Saja' => 'Ironing Only',
            'Cuci Saja' => 'Wash Only',
            'Cuci Satuan' => 'Special Items',
            
            // Item Types
            'Pakaian Kiloan' => 'Regular Laundry (kg)',
            'Bedcover' => 'Bedcover',
            'Jas / Blazer' => 'Suit / Blazer',
            'Selimut' => 'Blanket',
            'Karpet' => 'Carpet',
            'Sepatu' => 'Shoes',
            'Tas' => 'Bag',
            'Helm' => 'Helmet',
            'Boneka' => 'Soft Toy',
            'Gorden' => 'Curtain',

            // Statuses
            'waiting_pickup' => 'WAITING PICKUP',
            'picking_up' => 'PICKING UP',
            'penjemputan' => 'PICKING UP',
            'picked_up' => 'PICKED UP',
            'dijemput' => 'PICKED UP',
            'in_transit_to_laundry' => 'TO LAUNDRY',
            'diantar' => 'TO LAUNDRY',
            'arrived_at_laundry' => 'ARRIVED',
            'sampai' => 'ARRIVED',
            'washing' => 'WASHING',
            'drying_ironing' => 'IRONING',
            'packing' => 'PACKING',
            'ready_for_delivery' => 'READY FOR DELIVERY',
            'pengantaran' => 'READY FOR DELIVERY',
            'delivering' => 'ON DELIVERY',
            'diantarkan' => 'ON DELIVERY',
            'completed' => 'COMPLETED',
            'selesai' => 'COMPLETED',
            'cancelled' => 'CANCELLED',
            'pending_payment' => 'PENDING PAYMENT',

            // Payment Methods
            'midtrans' => 'E-WALLET / VA',
            'cash' => 'CASH',
            'manual_transfer' => 'BANK TRANSFER',

            // Common labels
            'Pribadi' => 'Personal',
            'Bisnis' => 'Business',
            'Urgent' => 'Urgent',
            'Normal' => 'Normal',
            'Sabun Standar' => 'Standard Soap',
            'Tanpa Pewangi' => 'No Fragrance',
        ];

        $key = strtolower(trim($term));
        return $map[$key] ?? ($map[$term] ?? strtoupper(str_replace('_', ' ', $term)));
    }
}
