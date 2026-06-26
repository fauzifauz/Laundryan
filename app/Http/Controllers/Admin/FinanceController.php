<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Finance;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $this->syncOrderFinances();

        $period = $request->get('period', 'monthly');

        // New: explicit month/year selectors
        $filterMonth = $request->get('month', 'all');
        $filterYear  = $request->get('year',  'all');

        $filterDate     = $request->get('filter_date');
        $filterCategory = $request->get('filter_category');
        $filterService  = $request->get('filter_service');
        $startDate      = $request->get('start_date');
        $endDate        = $request->get('end_date');

        // Determine whether we are using the new month/year selector
        $usingMonthYear = ($filterMonth !== 'all' || $filterYear !== 'all');

        // Base Queries
        $incomeQuery  = Finance::where('type', 'income');
        $expenseQuery = Finance::where('type', 'expense');

        // Total balances without filters (Overall — always all-time)
        $overallIncome  = Finance::where('type', 'income')->sum('amount');
        $overallExpense = Finance::where('type', 'expense')->sum('amount');
        $balance        = $overallIncome - $overallExpense;

        // Apply interactive drill-down filters (category and service) first, since they apply to both periods
        if ($filterCategory) {
            $isIncomeCategory  = Finance::where('type', 'income')->where('category', $filterCategory)->exists();
            $isExpenseCategory = Finance::where('type', 'expense')->where('category', $filterCategory)->exists();

            if ($isIncomeCategory && !$isExpenseCategory) {
                $incomeQuery->where('category', $filterCategory);
            } elseif ($isExpenseCategory && !$isIncomeCategory) {
                $expenseQuery->where('category', $filterCategory);
            } else {
                $expenseQuery->where('category', $filterCategory);
                $incomeQuery->where('category', $filterCategory);
            }
        }

        $serviceName = null;
        if ($filterService) {
            $serviceName = Service::find($filterService)->name ?? 'Selected Service';
            $orderCodes  = Order::where('service_id', $filterService)->where('payment_status', 'paid')->pluck('order_code')->toArray();

            if (!empty($orderCodes)) {
                $incomeQuery->where(function ($q) use ($orderCodes) {
                    foreach (array_chunk($orderCodes, 50) as $chunk) {
                        foreach ($chunk as $code) {
                            $q->orWhere('description', 'like', "%{$code}%");
                        }
                    }
                });
            } else {
                $incomeQuery->whereRaw('1 = 0');
            }
        }

        // Clone base queries for comparison (previous period) before date filters are applied
        $prevIncomeQuery  = clone $incomeQuery;
        $prevExpenseQuery = clone $expenseQuery;

        // Determine the date range of the active (selected) period and the comparison period
        $start = null;
        $end = null;
        $prevStart = null;
        $prevEnd = null;
        $timeframeLabel = 'bulan lalu'; // Default comparison label
        $lastMonthDate = Carbon::now()->subMonth();
        $lastMonthFilter = $lastMonthDate->format('Y-m');

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $days = $start->diffInDays($end) + 1;
            
            $prevStart = $start->copy()->subDays($days)->startOfDay();
            $prevEnd = $start->copy()->subDay()->endOfDay();
            $timeframeLabel = "$days hari sebelumnya";
        } elseif ($usingMonthYear) {
            $year = $filterYear !== 'all' ? (int) $filterYear : Carbon::now()->year;
            
            if ($filterMonth !== 'all') {
                $start = Carbon::create($year, (int)$filterMonth, 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                
                $prevStart = $start->copy()->subMonth()->startOfMonth();
                $prevEnd = $prevStart->copy()->endOfMonth();
                $timeframeLabel = 'bulan lalu';
            } else {
                $start = Carbon::create($year, 1, 1)->startOfYear();
                $end = $start->copy()->endOfYear();
                
                $prevStart = $start->copy()->subYear()->startOfYear();
                $prevEnd = $prevStart->copy()->endOfYear();
                $timeframeLabel = 'tahun lalu';
            }
        } elseif ($filterDate) {
            if (strlen($filterDate) === 7) { // Y-m
                $parts = explode('-', $filterDate);
                $start = Carbon::create((int)$parts[0], (int)$parts[1], 1)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                
                $prevStart = $start->copy()->subMonth()->startOfMonth();
                $prevEnd = $prevStart->copy()->endOfMonth();
                $timeframeLabel = 'bulan lalu';
            } else { // Y-m-d
                $start = Carbon::parse($filterDate)->startOfDay();
                $end = Carbon::parse($filterDate)->endOfDay();
                
                $prevStart = $start->copy()->subDay()->startOfDay();
                $prevEnd = $start->copy()->subDay()->endOfDay();
                $timeframeLabel = 'kemarin';
            }
        } else {
            // Period mode ('daily', 'weekly', 'monthly', 'yearly')
            if ($period === 'daily') {
                $start = Carbon::today()->startOfDay();
                $end = Carbon::today()->endOfDay();
                
                $prevStart = Carbon::yesterday()->startOfDay();
                $prevEnd = Carbon::yesterday()->endOfDay();
                $timeframeLabel = 'kemarin';
            } elseif ($period === 'weekly') {
                $start = Carbon::now()->startOfWeek()->startOfDay();
                $end = Carbon::now()->endOfWeek()->endOfDay();
                
                $prevStart = $start->copy()->subWeek()->startOfDay();
                $prevEnd = $prevStart->copy()->endOfWeek()->endOfDay();
                $timeframeLabel = 'minggu lalu';
            } elseif ($period === 'yearly') {
                $start = Carbon::now()->startOfYear()->startOfDay();
                $end = Carbon::now()->endOfYear()->endOfDay();
                
                $prevStart = $start->copy()->subYear()->startOfYear();
                $prevEnd = $prevStart->copy()->endOfYear();
                $timeframeLabel = 'tahun lalu';
            } else { // default 'monthly'
                $start = Carbon::now()->startOfMonth()->startOfDay();
                $end = Carbon::now()->endOfMonth()->endOfDay();
                
                $prevStart = $start->copy()->subMonth()->startOfMonth();
                $prevEnd = $prevStart->copy()->endOfMonth();
                $timeframeLabel = 'bulan lalu';
            }
        }

        // Apply date filters to the queries
        if ($start && $end) {
            $incomeQuery->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            $expenseQuery->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            
            $prevIncomeQuery->whereBetween('date', [$prevStart->toDateString(), $prevEnd->toDateString()]);
            $prevExpenseQuery->whereBetween('date', [$prevStart->toDateString(), $prevEnd->toDateString()]);
        }

        $activeStartDate = $start ? $start->toDateString() : null;
        $activeEndDate = $end ? $end->toDateString() : null;

        // --- Trend Chart ---
        $chartData = $this->generateTrendChartData($period, $startDate, $endDate, $filterMonth, $filterYear);

        // --- Revenue by Service ---
        $ordersQuery = Order::with('service')->where('payment_status', 'paid');
        if ($startDate && $endDate) {
            $ordersQuery->whereBetween('orders.created_at', [$startDate, $endDate]);
        } elseif ($usingMonthYear) {
            $this->applyMonthYearFilter($ordersQuery, 'orders.created_at', $filterMonth, $filterYear);
        } else {
            $this->applyPeriodFilter($ordersQuery, $period, 'orders.created_at', $startDate, $endDate);
        }

        $niceColors = ['#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#f43f5e', '#14b8a6', '#6366f1'];
        $colorIndex = 0;

        $revenueByServiceData = $ordersQuery
            ->join('services', 'orders.service_id', '=', 'services.id')
            ->selectRaw('orders.service_id, services.name as service_name, sum(orders.total_price) as total')
            ->groupBy('orders.service_id', 'services.name')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) use (&$colorIndex, $niceColors) {
                $color = $niceColors[$colorIndex % count($niceColors)];
                $colorIndex++;
                return [
                    'label'      => $item->service_name ?? 'Unknown Service',
                    'value'      => $item->total,
                    'color'      => $color,
                    'service_id' => $item->service_id,
                ];
            });

        // --- Expense Allocation Pie Chart ---
        $pieDataQuery = Finance::where('type', 'expense');
        if ($startDate && $endDate) {
            $pieDataQuery->whereBetween('date', [$startDate, $endDate]);
        } elseif ($usingMonthYear) {
            $this->applyMonthYearFilter($pieDataQuery, 'date', $filterMonth, $filterYear);
        } else {
            $this->applyPeriodFilter($pieDataQuery, $period, 'date', $startDate, $endDate);
        }
        $expensePieData = $pieDataQuery
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->category,
                    'value' => $item->total,
                    'color' => $this->getCategoryColor($item->category),
                ];
            });

        $filteredIncomeSum  = $incomeQuery->sum('amount');
        $filteredExpenseSum = $expenseQuery->sum('amount');
        $netProfit = $filteredIncomeSum - $filteredExpenseSum;

        $prevIncomeSum  = $prevIncomeQuery->sum('amount');
        $prevExpenseSum = $prevExpenseQuery->sum('amount');

        // Helper function for trend statistics calculation
        $getTrendStats = function ($current, $previous, $label, $isExpense = false) {
            if ($previous == 0) {
                if ($current == 0) {
                    return [
                        'change' => 0,
                        'label' => 'Stabil (0%) dari ' . $label,
                        'direction' => 'flat',
                        'icon' => 'trending_flat',
                        'class' => 'text-gray-300'
                    ];
                }
                
                return [
                    'change' => 100,
                    'label' => 'Naik 100% dari ' . $label,
                    'direction' => 'up',
                    'icon' => 'arrow_upward',
                    'class' => $isExpense ? 'text-rose-200' : 'text-green-200'
                ];
            }
            
            $diff = $current - $previous;
            $percentage = round(($diff / $previous) * 100, 1);
            
            if ($percentage > 0) {
                return [
                    'change' => $percentage,
                    'label' => 'Naik ' . $percentage . '% dari ' . $label,
                    'direction' => 'up',
                    'icon' => 'arrow_upward',
                    'class' => $isExpense ? 'text-rose-200' : 'text-green-200'
                ];
            } elseif ($percentage < 0) {
                return [
                    'change' => $percentage,
                    'label' => 'Turun ' . abs($percentage) . '% dari ' . $label,
                    'direction' => 'down',
                    'icon' => 'arrow_downward',
                    'class' => $isExpense ? 'text-green-200' : 'text-rose-200'
                ];
            } else {
                return [
                    'change' => 0,
                    'label' => 'Stabil (0%) dari ' . $label,
                    'direction' => 'flat',
                    'icon' => 'trending_flat',
                    'class' => 'text-gray-300'
                ];
            }
        };

        $incomeTrend  = $getTrendStats($filteredIncomeSum, $prevIncomeSum, $timeframeLabel, false);
        $expenseTrend = $getTrendStats($filteredExpenseSum, $prevExpenseSum, $timeframeLabel, true);

        // Keep float variables for backward compatibility
        $incomeChange  = $incomeTrend['change'];
        $expenseChange = $expenseTrend['change'];

        // Profit Margin calculated dynamically for the active filtered period
        $profitMargin = $filteredIncomeSum > 0 
            ? round((($filteredIncomeSum - $filteredExpenseSum) / $filteredIncomeSum) * 100, 1) 
            : 0;

        // Statistics for Payment Methods (based on current period)
        $paymentMethodQuery = Finance::where('type', 'income');
        if ($startDate && $endDate) {
            $paymentMethodQuery->whereBetween('date', [$startDate, $endDate]);
        } elseif ($usingMonthYear) {
            $this->applyMonthYearFilter($paymentMethodQuery, 'date', $filterMonth, $filterYear);
        } else {
            $this->applyPeriodFilter($paymentMethodQuery, $period, 'date', $startDate, $endDate);
        }
        $paymentMethodStats = $paymentMethodQuery
            ->selectRaw('payment_method, sum(amount) as total')
            ->groupBy('payment_method')
            ->get();

        $incomeHistory  = $incomeQuery->orderBy('date', 'desc')->take(10)->get();
        $expenseHistory = $expenseQuery->orderBy('date', 'desc')->take(10)->get();

        // Build a readable period label for KPI cards
        $periodLabel = 'This Period';
        if ($startDate && $endDate) {
            $periodLabel = Carbon::parse($startDate)->format('d M') . ' – ' . Carbon::parse($endDate)->format('d M Y');
        } elseif ($usingMonthYear) {
            $parts = [];
            if ($filterMonth !== 'all') $parts[] = Carbon::create(2026, $filterMonth)->format('F');
            if ($filterYear  !== 'all') $parts[] = $filterYear;
            $periodLabel = implode(' ', $parts) ?: 'All Time';
        } else {
            $periodLabel = ucfirst($period);
        }

        return view('admin.finance.index', compact(
            'incomeHistory', 'expenseHistory', 'balance',
            'overallIncome', 'overallExpense',
            'filteredIncomeSum', 'filteredExpenseSum', 'netProfit',
            'period', 'filterMonth', 'filterYear', 'periodLabel',
            'filterDate', 'filterCategory', 'filterService', 'serviceName',
            'startDate', 'endDate', 'activeStartDate', 'activeEndDate',
            'chartData', 'expensePieData', 'revenueByServiceData',
            'incomeChange', 'expenseChange', 'profitMargin', 'lastMonthFilter',
            'paymentMethodStats', 'incomeTrend', 'expenseTrend'
        ));
    }


    public function income(Request $request)
    {
        $this->syncOrderFinances();

        $filterMonth   = $request->get('month', 'all');
        $filterYear    = $request->get('year',  'all');
        $filterDate    = $request->get('filter_date');
        $filterService = $request->get('filter_service');
        $startDate     = $request->get('start_date');
        $endDate       = $request->get('end_date');

        $query = Finance::where('type', 'income');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($filterMonth !== 'all' || $filterYear !== 'all') {
            $this->applyMonthYearFilter($query, 'date', $filterMonth, $filterYear);
        }

        if ($filterDate) {
            $query->whereDate('date', $filterDate);
        }

        $serviceName = null;
        if ($filterService) {
            $service = Service::find($filterService);
            $serviceName = $service ? $service->name : 'Selected Service';
            $orderCodes  = Order::where('service_id', $filterService)->where('payment_status', 'paid')->pluck('order_code')->toArray();

            if (!empty($orderCodes)) {
                $query->where(function ($q) use ($orderCodes) {
                    foreach (array_chunk($orderCodes, 50) as $chunk) {
                        foreach ($chunk as $code) {
                            $q->orWhere('description', 'like', "%{$code}%");
                        }
                    }
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $incomeHistory = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        $totalIncome   = $query->sum('amount');

        // Build readable label
        $parts = [];
        if ($filterMonth !== 'all') $parts[] = \Carbon\Carbon::create(2026, $filterMonth)->format('F');
        if ($filterYear  !== 'all') $parts[] = $filterYear;
        $periodLabel = $parts ? implode(' ', $parts) : ($startDate ? $startDate.' – '.$endDate : 'All Time');

        return view('admin.finance.income', compact(
            'incomeHistory', 'totalIncome',
            'filterMonth', 'filterYear', 'periodLabel',
            'filterDate', 'filterService', 'serviceName', 'startDate', 'endDate'
        ));
    }

    public function expense(Request $request)
    {
        $filterMonth    = $request->get('month', 'all');
        $filterYear     = $request->get('year',  'all');
        $filterDate     = $request->get('filter_date');
        $filterCategory = $request->get('filter_category');
        $startDate      = $request->get('start_date');
        $endDate        = $request->get('end_date');

        $query = Finance::where('type', 'expense');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($filterMonth !== 'all' || $filterYear !== 'all') {
            $this->applyMonthYearFilter($query, 'date', $filterMonth, $filterYear);
        }

        if ($filterDate) {
            $query->whereDate('date', $filterDate);
        }

        if ($filterCategory) {
            $query->where('category', $filterCategory);
        }

        $expenseHistory = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        $totalExpense   = $query->sum('amount');

        // Build readable label
        $parts = [];
        if ($filterMonth !== 'all') $parts[] = \Carbon\Carbon::create(2026, $filterMonth)->format('F');
        if ($filterYear  !== 'all') $parts[] = $filterYear;
        $periodLabel = $parts ? implode(' ', $parts) : ($startDate ? $startDate.' – '.$endDate : 'All Time');

        return view('admin.finance.expense', compact(
            'expenseHistory', 'totalExpense',
            'filterMonth', 'filterYear', 'periodLabel',
            'filterDate', 'filterCategory', 'startDate', 'endDate'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'nullable|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'payment_method' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('finances', 'public');
        }

        $type = $request->type ?? 'expense';

        Finance::create([
            'type' => $type,
            'amount' => $request->amount,
            'category' => $request->category,
            'description' => $request->description,
            'attachment' => $attachmentPath,
            'payment_method' => $request->payment_method,
            'date' => $request->date,
        ]);

        if ($type === 'income') {
            return redirect()->back()->with('success', 'Income recorded successfully.')->with('new_income_created', true);
        } else {
            return redirect()->back()->with('success', 'Expense recorded successfully.')->with('new_expense_created', true);
        }
    }

    public function exportPdf(Request $request)
    {
        $month     = $request->get('month', 'all');
        $year      = $request->get('year', 'all');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $incomeQuery  = Finance::where('type', 'income');
        $expenseQuery = Finance::where('type', 'expense');

        $this->applyMonthYearFilter($incomeQuery,  'date', $month, $year, $startDate, $endDate);
        $this->applyMonthYearFilter($expenseQuery, 'date', $month, $year, $startDate, $endDate);

        $incomeHistory  = $incomeQuery->orderBy('date', 'desc')->get();
        $expenseHistory = $expenseQuery->orderBy('date', 'desc')->get();

        $totalIncome  = $incomeHistory->sum('amount');
        $totalExpense = $expenseHistory->sum('amount');
        $balance      = $totalIncome - $totalExpense;
        $profitMargin = $totalIncome > 0 ? round((($totalIncome - $totalExpense) / $totalIncome) * 100, 1) : 0;

        $ordersQuery = \App\Models\Order::with('service')->where('payment_status', 'paid');
        $this->applyMonthYearFilter($ordersQuery, 'orders.created_at', $month, $year, $startDate, $endDate);

        $revenueByServiceData = $ordersQuery->get()->groupBy('service_id')->map(function ($orders) {
            return ['name' => $orders->first()->service->name ?? 'Unknown Service', 'revenue' => $orders->sum('total_price')];
        })->values();

        $expensePieData = $expenseHistory->groupBy('category')->map(function ($expenses, $category) {
            return ['category' => $category, 'amount' => $expenses->sum('amount')];
        })->values();

        // Build period label for the PDF header
        $periodParts = [];
        if ($month !== 'all') $periodParts[] = Carbon::create(2026, $month)->format('F');
        if ($year  !== 'all') $periodParts[] = $year;
        $period = $periodParts ? implode(' ', $periodParts) : 'All Time';

        $pdf = Pdf::loadView('admin.exports.finance_pdf', compact(
            'incomeHistory', 'expenseHistory', 'totalIncome', 'totalExpense',
            'balance', 'period', 'startDate', 'endDate',
            'profitMargin', 'revenueByServiceData', 'expensePieData'
        ))
            ->setPaper('a4', 'portrait')
            ->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $slug = str_replace(' ', '_', strtolower($period));
        return $pdf->download("laundryan_finance_{$slug}_" . date('Ymd') . ".pdf");
    }

    public function exportCsv(Request $request)
    {
        $month     = $request->get('month', 'all');
        $year      = $request->get('year', 'all');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $incomeQuery  = Finance::where('type', 'income');
        $expenseQuery = Finance::where('type', 'expense');
        $this->applyMonthYearFilter($incomeQuery,  'date', $month, $year, $startDate, $endDate);
        $this->applyMonthYearFilter($expenseQuery, 'date', $month, $year, $startDate, $endDate);

        $incomeHistory  = $incomeQuery->orderBy('date', 'desc')->get();
        $expenseHistory = $expenseQuery->orderBy('date', 'desc')->get();

        $totalIncome  = $incomeHistory->sum('amount');
        $totalExpense = $expenseHistory->sum('amount');
        $balance      = $totalIncome - $totalExpense;
        $profitMargin = $totalIncome > 0 ? round((($totalIncome - $totalExpense) / $totalIncome) * 100, 1) : 0;

        // Revenue by Service
        $ordersQuery = \App\Models\Order::with('service')->where('payment_status', 'paid');
        $this->applyMonthYearFilter($ordersQuery, 'orders.created_at', $month, $year, $startDate, $endDate);
        $revenueByService = $ordersQuery->get()->groupBy('service_id')->map(function ($orders) {
            return ['name' => $orders->first()->service->name ?? 'Unknown Service', 'revenue' => $orders->sum('total_price')];
        })->values();

        // Expense by Category
        $expenseByCategory = $expenseHistory->groupBy('category')->map(function ($expenses, $category) {
            return ['category' => $category, 'amount' => $expenses->sum('amount')];
        })->values();

        // Build period label
        $periodParts = [];
        if ($month !== 'all') $periodParts[] = Carbon::create(2026, $month)->format('F');
        if ($year  !== 'all') $periodParts[] = $year;
        $period      = $periodParts ? implode(' ', $periodParts) : 'All Time';
        $periodLabel = ($startDate && $endDate)
            ? Carbon::parse($startDate)->format('d M Y') . ' – ' . Carbon::parse($endDate)->format('d M Y')
            : $period;

        $filename = "laundryan_finance_{$period}_" . date('Ymd') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use (
            $incomeHistory, $expenseHistory,
            $totalIncome, $totalExpense, $balance, $profitMargin,
            $revenueByService, $expenseByCategory,
            $periodLabel, $period
        ) {
            $file = fopen('php://output', 'w');

            // ── Report Header ──────────────────────────────────────────
            fputcsv($file, ['LAUNDRYAN - FINANCIAL REPORT']);
            fputcsv($file, ['Period', $periodLabel]);
            fputcsv($file, ['Printed At', now()->timezone('Asia/Jakarta')->format('l, d/m/Y H:i') . ' WIB']);
            fputcsv($file, []);

            // ── Executive Summary ──────────────────────────────────────
            fputcsv($file, ['== EXECUTIVE SUMMARY ==']);
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Income',   'Rp ' . number_format($totalIncome,  0, ',', '.')]);
            fputcsv($file, ['Total Expenses', 'Rp ' . number_format($totalExpense, 0, ',', '.')]);
            fputcsv($file, ['Net Profit/Loss','Rp ' . number_format($balance,       0, ',', '.')]);
            fputcsv($file, ['Profit Margin',  $profitMargin . '%']);
            fputcsv($file, []);

            // ── Revenue Breakdown ──────────────────────────────────────
            fputcsv($file, ['== REVENUE BREAKDOWN (BY SERVICE) ==']);
            fputcsv($file, ['No', 'Service Name', 'Percentage', 'Total Revenue']);
            foreach ($revenueByService as $i => $item) {
                $pct = $totalIncome > 0 ? round(($item['revenue'] / $totalIncome) * 100, 1) : 0;
                fputcsv($file, [
                    $i + 1,
                    $item['name'],
                    $pct . '%',
                    'Rp ' . number_format($item['revenue'], 0, ',', '.'),
                ]);
            }
            fputcsv($file, []);

            // ── Expense Breakdown ──────────────────────────────────────
            fputcsv($file, ['== EXPENSE BREAKDOWN (BY CATEGORY) ==']);
            fputcsv($file, ['No', 'Expense Category', 'Percentage', 'Total Expense']);
            foreach ($expenseByCategory as $i => $item) {
                $pct = $totalExpense > 0 ? round(($item['amount'] / $totalExpense) * 100, 1) : 0;
                fputcsv($file, [
                    $i + 1,
                    $item['category'],
                    $pct . '%',
                    'Rp ' . number_format($item['amount'], 0, ',', '.'),
                ]);
            }
            fputcsv($file, []);

            // ── Income History ─────────────────────────────────────────
            fputcsv($file, ['== INCOME HISTORY (DETAILED) ==']);
            fputcsv($file, ['No', 'Date', 'Category', 'Method', 'Description', 'Amount']);
            foreach ($incomeHistory as $i => $row) {
                fputcsv($file, [
                    $i + 1,
                    Carbon::parse($row->date)->format('d/m/Y'),
                    $row->category,
                    $row->payment_method ?: 'CASH',
                    $row->description ?: '-',
                    'Rp ' . number_format($row->amount, 0, ',', '.'),
                ]);
            }
            fputcsv($file, ['', '', '', '', 'TOTAL INCOME', 'Rp ' . number_format($totalIncome, 0, ',', '.')]);
            fputcsv($file, []);

            // ── Expense History ────────────────────────────────────────
            fputcsv($file, ['== EXPENSE HISTORY (DETAILED) ==']);
            fputcsv($file, ['No', 'Date', 'Category', 'Method', 'Description', 'Amount']);
            foreach ($expenseHistory as $i => $row) {
                fputcsv($file, [
                    $i + 1,
                    Carbon::parse($row->date)->format('d/m/Y'),
                    $row->category,
                    $row->payment_method ?: 'CASH',
                    $row->description ?: '-',
                    'Rp ' . number_format($row->amount, 0, ',', '.'),
                ]);
            }
            fputcsv($file, []);

            // ── Signature ─────────────────────────────────────────────
            fputcsv($file, ['== AUTHORIZATION ==']);
            fputcsv($file, ['Date', now()->timezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y')]);
            fputcsv($file, ['Prepared By', auth()->user()->name ?? 'Admin Laundryan']);
            fputcsv($file, ['Acknowledged By', 'Owner / Manager']);

            fclose($file);
        };


        return response()->stream($callback, 200, $headers);
    }

    private function syncOrderFinances()
    {
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
    }

    private function applyPeriodFilter($query, $period, $column = 'date', $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $query->whereBetween($column, [$startDate, $endDate]);
        } elseif ($period === 'daily') {
            $query->whereDate($column, Carbon::today());
        } elseif ($period === 'monthly') {
            $query->whereMonth($column, Carbon::now()->month)->whereYear($column, Carbon::now()->year);
        } elseif ($period === 'yearly') {
            $query->whereYear($column, Carbon::now()->year);
        } elseif ($period === 'weekly') {
            $query->whereBetween($column, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        }
    }

    /**
     * New: filter by explicit month and/or year (for export period selectors).
     */
    private function applyMonthYearFilter($query, $column, $month, $year, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            $query->whereBetween($column, [$startDate, $endDate]);
            return;
        }
        if ($month !== 'all') {
            $query->whereMonth($column, $month);
        }
        if ($year !== 'all') {
            $query->whereYear($column, $year);
        }
    }

    private function generateTrendChartData($period, $startDate = null, $endDate = null, $filterMonth = 'all', $filterYear = 'all')
    {
        $data = ['labels' => [], 'income' => [], 'expense' => [], 'full_dates' => []];

        // Date-range mode
        if ($startDate && $endDate) {
            $start      = Carbon::parse($startDate);
            $end        = Carbon::parse($endDate);
            $diffInDays = $start->diffInDays($end);

            if ($diffInDays > 60) {
                $current = $start->copy()->startOfMonth();
                while ($current <= $end) {
                    $data['labels'][]     = $current->format('M Y');
                    $data['full_dates'][] = $current->format('Y-m');
                    $data['income'][]     = Finance::where('type', 'income')->whereMonth('date', $current->month)->whereYear('date', $current->year)->sum('amount');
                    $data['expense'][]    = Finance::where('type', 'expense')->whereMonth('date', $current->month)->whereYear('date', $current->year)->sum('amount');
                    $current->addMonth();
                }
            } else {
                for ($date = $start; $date <= $end; $date->addDay()) {
                    $data['labels'][]     = $date->format('d M');
                    $data['full_dates'][] = $date->toDateString();
                    $data['income'][]     = Finance::where('type', 'income')->whereDate('date', $date->toDateString())->sum('amount');
                    $data['expense'][]    = Finance::where('type', 'expense')->whereDate('date', $date->toDateString())->sum('amount');
                }
            }
            return $data;
        }

        // Month/Year selector mode — show days within selected month, or months within selected year
        $usingMonthYear = ($filterMonth !== 'all' || $filterYear !== 'all');
        if ($usingMonthYear) {
            $year  = $filterYear  !== 'all' ? (int) $filterYear  : Carbon::now()->year;
            $month = $filterMonth !== 'all' ? (int) $filterMonth : null;

            if ($month) {
                // Show each day in the selected month
                $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $date = Carbon::create($year, $month, $i);
                    $data['labels'][]     = $i;
                    $data['full_dates'][] = $date->toDateString();
                    $data['income'][]     = Finance::where('type', 'income')->whereDate('date', $date->toDateString())->sum('amount');
                    $data['expense'][]    = Finance::where('type', 'expense')->whereDate('date', $date->toDateString())->sum('amount');
                }
            } else {
                // Year only — show 12 months
                for ($m = 1; $m <= 12; $m++) {
                    $mo = Carbon::create($year, $m, 1);
                    $data['labels'][]     = $mo->format('M');
                    $data['full_dates'][] = $mo->format('Y-m');
                    $data['income'][]     = Finance::where('type', 'income')->whereMonth('date', $m)->whereYear('date', $year)->sum('amount');
                    $data['expense'][]    = Finance::where('type', 'expense')->whereMonth('date', $m)->whereYear('date', $year)->sum('amount');
                }
            }
            return $data;
        }

        // Legacy period mode
        if ($period === 'daily') {
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $data['labels'][]     = $date->format('d M');
                $data['full_dates'][] = $date->toDateString();
                $data['income'][]     = Finance::where('type', 'income')->whereDate('date', $date)->sum('amount');
                $data['expense'][]    = Finance::where('type', 'expense')->whereDate('date', $date)->sum('amount');
            }
        } elseif ($period === 'monthly') {
            $daysInMonth = Carbon::now()->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = Carbon::now()->day($i);
                $data['labels'][]     = $i;
                $data['full_dates'][] = $date->toDateString();
                $data['income'][]     = Finance::where('type', 'income')->whereDate('date', $date->toDateString())->sum('amount');
                $data['expense'][]    = Finance::where('type', 'expense')->whereDate('date', $date->toDateString())->sum('amount');
            }
        } elseif ($period === 'yearly') {
            for ($i = 1; $i <= 12; $i++) {
                $month = Carbon::now()->month($i);
                $data['labels'][]     = $month->format('M');
                $data['full_dates'][] = $month->format('Y-m');
                $data['income'][]     = Finance::where('type', 'income')->whereMonth('date', $i)->whereYear('date', Carbon::now()->year)->sum('amount');
                $data['expense'][]    = Finance::where('type', 'expense')->whereMonth('date', $i)->whereYear('date', Carbon::now()->year)->sum('amount');
            }
        } else { // weekly
            for ($i = 0; $i <= 6; $i++) {
                $date = Carbon::now()->startOfWeek()->addDays($i);
                $data['labels'][]     = $date->format('D');
                $data['full_dates'][] = $date->toDateString();
                $data['income'][]     = Finance::where('type', 'income')->whereDate('date', $date)->sum('amount');
                $data['expense'][]    = Finance::where('type', 'expense')->whereDate('date', $date)->sum('amount');
            }
        }

        return $data;
    }


    private function getCategoryColor($category)
    {
        $colors = [
            'Payroll'       => '#6366f1', // Indigo  — penggajian otomatis
            'Penggajian'    => '#6366f1', // Indigo  — alias manual
            'Sabun'         => '#10b981', // Emerald
            'Pewangi'       => '#f59e0b', // Amber
            'Listrik'       => '#f43f5e', // Rose
            'Air'           => '#8b5cf6', // Violet
            'Pajak'         => '#64748b', // Slate
            'Transportasi'  => '#0ea5e9', // Sky Blue
            'Peralatan'     => '#f97316', // Orange
            'Sewa'          => '#14b8a6', // Teal
            'Lainnya'       => '#94a3b8', // Light Slate
            'Laundry Order' => '#005bc0', // Dark Blue — income otomatis
        ];

        return $colors[$category] ?? sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    public function update(Request $request, Finance $finance)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'payment_method' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $data = [
            'amount' => $request->amount,
            'category' => $request->category,
            'description' => $request->description,
            'payment_method' => $request->payment_method,
            'date' => $request->date,
        ];

        if ($request->hasFile('attachment')) {
            if ($finance->attachment) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($finance->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('finances', 'public');
        }

        $finance->update($data);

        if ($finance->type === 'income') {
            return redirect()->back()->with('success', 'Income record updated successfully.')->with('income_updated', true);
        } else {
            return redirect()->back()->with('success', 'Expense record updated successfully.')->with('expense_updated', true);
        }
    }

    public function destroy(Finance $finance)
    {
        $type = $finance->type;

        if ($finance->attachment) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($finance->attachment);
        }

        $finance->delete();

        if ($type === 'income') {
            return redirect()->back()->with('success', 'Income record deleted successfully.')->with('income_deleted', true);
        } else {
            return redirect()->back()->with('success', 'Expense record deleted successfully.')->with('expense_deleted', true);
        }
    }
}
