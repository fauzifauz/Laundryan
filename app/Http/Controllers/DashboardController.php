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
        return view('dashboard');
    }
}
