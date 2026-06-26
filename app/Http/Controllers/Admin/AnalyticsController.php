<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Finance;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // 1. Revenue Last 7 Days
        $revenueData = Finance::where('type', 'income')
            ->where('date', '>=', now()->subDays(6))
            ->select(DB::raw('date, SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 2. Service Popularity
        $serviceData = Order::select('service_id', DB::raw('count(*) as total'))
            ->with('service')
            ->groupBy('service_id')
            ->get();

        // 3. Overall Stats
        $stats = [
            'total_revenue' => Finance::where('type', 'income')->sum('amount'),
            'total_orders' => Order::count(),
            'active_orders' => Order::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'avg_rating' => \App\Models\Review::avg('rating') ?: 0,
        ];

        return view('admin.analytics.index', compact('revenueData', 'serviceData', 'stats'));
    }
}
