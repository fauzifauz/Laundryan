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
        
        // Fetch all active/in-progress orders
        $activeOrders = (clone $orders)->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->with(['service', 'itemType', 'courier', 'pickupCourier', 'deliveryCourier'])
            ->get();
        
        // Fetch courier history and separate pickup & delivery
        $allOrders = (clone $orders)->with(['service', 'itemType', 'pickupCourier', 'deliveryCourier', 'courier'])->get();
        $pickupCouriers = $allOrders->whereNotNull('pickup_courier_id')->pluck('pickupCourier')->unique('id')->filter();
        $deliveryCouriers = $allOrders->whereNotNull('delivery_courier_id')->pluck('deliveryCourier')->unique('id')->filter();

        $courierList = collect();

        foreach ($pickupCouriers as $courier) {
            $totalOrdersHandled = $allOrders->filter(function($o) use ($courier) {
                return $o->pickup_courier_id == $courier->id 
                    || $o->delivery_courier_id == $courier->id 
                    || $o->courier_id == $courier->id;
            });

            $courierList->push([
                'courier' => $courier,
                'role' => 'Kurir Pickup',
                'role_code' => 'pickup',
                'orders' => $totalOrdersHandled
            ]);
        }

        foreach ($deliveryCouriers as $courier) {
            $totalOrdersHandled = $allOrders->filter(function($o) use ($courier) {
                return $o->pickup_courier_id == $courier->id 
                    || $o->delivery_courier_id == $courier->id 
                    || $o->courier_id == $courier->id;
            });

            $existing = $courierList->firstWhere('courier.id', $courier->id);
            if ($existing) {
                $courierList = $courierList->map(function($item) use ($courier, $totalOrdersHandled) {
                    if ($item['courier']['id'] == $courier->id) {
                        $item['role'] = 'Kurir Pickup & Delivery';
                        $item['role_code'] = 'both';
                        $item['orders'] = $totalOrdersHandled;
                    }
                    return $item;
                });
            } else {
                $courierList->push([
                    'courier' => $courier,
                    'role' => 'Kurir Delivery',
                    'role_code' => 'delivery',
                    'orders' => $totalOrdersHandled
                ]);
            }
        }

        // Build a helper to format a courier entry
        $formatCourier = function($courier, $role, $role_code) use ($allOrders) {
            $ordersHandled = $allOrders->filter(function($o) use ($courier) {
                return $o->pickup_courier_id == $courier->id
                    || $o->delivery_courier_id == $courier->id
                    || $o->courier_id == $courier->id;
            });
            return [
                'id'      => $courier->id,
                'name'    => $courier->name,
                'phone'   => $courier->phone,
                'email'   => $courier->email,
                'initial' => strtoupper(substr($courier->name, 0, 1)),
                'role'    => $role,
                'role_code' => $role_code,
                'orders'  => $ordersHandled->map(function($o) use ($courier) {
                    $roles = [];
                    if ($o->pickup_courier_id == $courier->id)  $roles[] = 'Pickup';
                    if ($o->delivery_courier_id == $courier->id) $roles[] = 'Delivery';
                    if ($o->courier_id == $courier->id && empty($roles)) $roles[] = 'Assigned';
                    return [
                        'order_code'    => $o->order_code,
                        'service_name'  => $o->service->name,
                        'item_type_name'=> $o->itemType->name,
                        'status'        => $o->status,
                        'role_in_order' => implode(' & ', $roles),
                        'date'          => $o->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
                        'url'           => route('customer.orders.show', $o->id)
                    ];
                })->values()->toArray()
            ];
        };

        // Separate pickup and delivery courier lists for left/right columns
        $pickupCourierHistory  = $pickupCouriers->map(fn($c)  => $formatCourier($c, 'Kurir Pickup', 'pickup'))->values();
        $deliveryCourierHistory = $deliveryCouriers->map(fn($c) => $formatCourier($c, 'Kurir Delivery', 'delivery'))->values();

        // Determine whether to show the onboarding tour (set by AuthenticatedSessionController on login)
        $showOnboarding = session('show_onboarding', false);

        return view('dashboard', compact(
            'activeOrdersCount',
            'completedOrdersCount',
            'totalSpending',
            'activeOrders',
            'pickupCourierHistory',
            'deliveryCourierHistory',
            'showOnboarding'
        ));
    }
}
