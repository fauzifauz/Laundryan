<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Employee\OrderController as EmployeeDashboardController;
use App\Http\Controllers\Courier\OrderController as CourierDashboardController;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return (new AdminDashboardController())->index($request);
        }

        if ($user->role === 'karyawan') {
            return (new EmployeeDashboardController())->index($request);
        }

        if ($user->role === 'kurir') {
            return (new CourierDashboardController())->index();
        }

        // Default for pelanggan (customer)
        $user = auth()->user();
        $orders = $user->customerOrders();
        
        $activeOrdersCount = (clone $orders)->whereNotIn('status', ['completed', 'cancelled'])->count();
        $completedOrdersCount = (clone $orders)->where('status', 'completed')->count();
        $totalSpending = (clone $orders)->where('payment_status', 'paid')->sum('total_price');
        
        $latestOrder = (clone $orders)->latest()->with(['service', 'itemType', 'courier'])->first();
        
        // Fetch courier history
        $pickupCouriers = (clone $orders)->whereNotNull('pickup_courier_id')->with('pickupCourier')->get()->pluck('pickupCourier');
        $deliveryCouriers = (clone $orders)->whereNotNull('delivery_courier_id')->with('deliveryCourier')->get()->pluck('deliveryCourier');
        $assignedCouriers = (clone $orders)->whereNotNull('courier_id')->with('courier')->get()->pluck('courier');
        $couriers = $pickupCouriers->concat($deliveryCouriers)->concat($assignedCouriers)->unique('id')->filter()->take(5);

        return view('dashboard', compact(
            'activeOrdersCount',
            'completedOrdersCount',
            'totalSpending',
            'latestOrder',
            'couriers'
        ));
    }
}
