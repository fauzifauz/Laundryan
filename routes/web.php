<?php

use App\Http\Controllers\ProfileController;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $settings = \App\Models\LandingPageSetting::all()
        ->pluck('content', 'key');

    return view('welcome', compact('settings'));
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get(
        '/dashboard',
        [\App\Http\Controllers\DashboardController::class, 'index']
    )->name('dashboard');

    Route::get('/orders/{order}/scan', function (Order $order) {
        /** @var User $user */
        $user = Auth::user();

        $role = $user->role;

        if ($role === 'admin') {
            return redirect()->route('admin.orders.show', $order);
        }

        if ($role === 'karyawan') {
            return redirect()->route('karyawan.orders.show', $order);
        }

        if ($role === 'kurir') {
            return redirect()->route('kurir.orders.show', $order);
        }

        if ($role === 'pelanggan') {
            return redirect()->route('customer.orders.show', $order);
        }

        return redirect()->route('dashboard');
    })->name('orders.scan');

    /*
    |--------------------------------------------------------------------------
    | Customer Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:pelanggan')
        ->prefix('customer')
        ->name('customer.')
        ->group(function () {
            Route::get(
                '/orders',
                [\App\Http\Controllers\Customer\OrderController::class, 'index']
            )->name('orders.index');

            Route::get(
                '/orders/create',
                [\App\Http\Controllers\Customer\OrderController::class, 'create']
            )->name('orders.create');

            Route::post(
                '/orders',
                [\App\Http\Controllers\Customer\OrderController::class, 'store']
            )->name('orders.store');

            Route::post(
                '/orders/calculate-price',
                [\App\Http\Controllers\Customer\OrderController::class, 'calculatePrice']
            )->name('orders.calculate-price');

            Route::get(
                '/orders/{order}',
                [\App\Http\Controllers\Customer\OrderController::class, 'show']
            )->name('orders.show');

            Route::get(
                '/orders/{order}/invoice',
                [\App\Http\Controllers\Customer\OrderController::class, 'invoice']
            )->name('orders.invoice');

            Route::post(
                '/orders/{order}/reviews',
                [\App\Http\Controllers\Customer\ReviewController::class, 'store']
            )->name('reviews.store');

            Route::post(
                '/location',
                [\App\Http\Controllers\Customer\OrderController::class, 'updateLocation']
            )->name('location.update');

            /*
            |--------------------------------------------------------------------------
            | Customer Payment Routes
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/payments',
                [\App\Http\Controllers\Customer\PaymentController::class, 'index']
            )->name('payments.index');

            Route::post(
                '/payments/{order}/upload',
                [\App\Http\Controllers\Customer\PaymentController::class, 'uploadProof']
            )->name('payments.upload-proof');

            Route::get(
                '/orders/{order}/qris-simulation',
                [\App\Http\Controllers\Customer\PaymentController::class, 'qrisSimulation']
            )->name('payment.qris-simulation');

            Route::post(
                '/orders/{order}/qris-simulation/pay',
                [\App\Http\Controllers\Customer\PaymentController::class, 'qrisSimulationPay']
            )->name('payment.qris-simulation.pay');

            Route::get(
                '/payment/success/{order}',
                [\App\Http\Controllers\Customer\OrderController::class, 'success']
            )->name('payment.success');

            Route::get(
                '/payment/cancel/{order}',
                [\App\Http\Controllers\Customer\OrderController::class, 'cancel']
            )->name('payment.cancel');
        });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get(
            '/dashboard',
            [\App\Http\Controllers\Admin\DashboardController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/export/orders/pdf',
            [\App\Http\Controllers\Admin\DashboardController::class, 'exportPdf']
        )->name('export.pdf');

        Route::get(
            '/export/orders/csv',
            [\App\Http\Controllers\Admin\DashboardController::class, 'exportCsv']
        )->name('export.csv');

        Route::resource(
            'services',
            \App\Http\Controllers\Admin\ServiceController::class
        );

        Route::resource(
            'item-types',
            \App\Http\Controllers\Admin\ItemTypeController::class
        );

        Route::get(
            '/users/export/pdf',
            [\App\Http\Controllers\Admin\UserController::class, 'exportPdf']
        )->name('users.export.pdf');

        Route::get(
            '/users/export/csv',
            [\App\Http\Controllers\Admin\UserController::class, 'exportCsv']
        )->name('users.export.csv');

        Route::resource(
            'users',
            \App\Http\Controllers\Admin\UserController::class
        );

        /*
        |--------------------------------------------------------------------------
        | Admin Order Routes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/orders/export/pdf',
            [\App\Http\Controllers\Admin\OrderController::class, 'exportPdf']
        )->name('orders.export.pdf');

        Route::get(
            '/orders/export/csv',
            [\App\Http\Controllers\Admin\OrderController::class, 'exportCsv']
        )->name('orders.export.csv');

        Route::post(
            '/orders/{order}/status',
            [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus']
        )->name('orders.status');

        Route::resource(
            'orders',
            \App\Http\Controllers\Admin\OrderController::class
        );

        Route::post(
            '/orders/{order}/assign',
            [\App\Http\Controllers\Admin\OrderController::class, 'assignCourier']
        )->name('orders.assign');

        /*
        |--------------------------------------------------------------------------
        | Admin Payment Routes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/payments/export/pdf',
            [\App\Http\Controllers\Admin\PaymentController::class, 'exportPdf']
        )->name('payments.export.pdf');

        Route::get(
            '/payments/export/csv',
            [\App\Http\Controllers\Admin\PaymentController::class, 'exportCsv']
        )->name('payments.export.csv');

        Route::post(
            '/payments/{payment}/verify',
            [\App\Http\Controllers\Admin\PaymentController::class, 'verify']
        )->name('payments.verify');

        Route::get(
            '/payments/{payment}/invoice',
            [\App\Http\Controllers\Admin\PaymentController::class, 'downloadInvoice']
        )->name('payments.invoice');

        Route::resource(
            'payments',
            \App\Http\Controllers\Admin\PaymentController::class
        )->only(['index', 'show']);

        /*
        |--------------------------------------------------------------------------
        | Admin Attendance Routes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/attendance/realtime-stats',
            [\App\Http\Controllers\Admin\AttendanceController::class, 'realtimeStats']
        )->name('attendance.realtime-stats');

        Route::get(
            '/attendance',
            [\App\Http\Controllers\Admin\AttendanceController::class, 'index']
        )->name('attendance.index');

        Route::post(
            '/attendance/{attendance}/approve',
            [\App\Http\Controllers\Admin\AttendanceController::class, 'approve']
        )->name('attendance.approve');

        Route::post(
            '/attendance/{attendance}/reject',
            [\App\Http\Controllers\Admin\AttendanceController::class, 'reject']
        )->name('attendance.reject');

        Route::get(
            '/attendance/export',
            [\App\Http\Controllers\Admin\AttendanceController::class, 'exportPdf']
        )->name('attendance.export');

        Route::get(
            '/attendance/export-csv',
            [\App\Http\Controllers\Admin\AttendanceController::class, 'exportCsv']
        )->name('attendance.export_csv');

        /*
        |--------------------------------------------------------------------------
        | Admin Finance Routes
        |--------------------------------------------------------------------------
        */

        Route::prefix('finance')
            ->name('finance.')
            ->group(function () {
                Route::get(
                    '/',
                    [\App\Http\Controllers\Admin\FinanceController::class, 'index']
                )->name('index');

                Route::get(
                    '/income',
                    [\App\Http\Controllers\Admin\FinanceController::class, 'income']
                )->name('income');

                Route::get(
                    '/expense',
                    [\App\Http\Controllers\Admin\FinanceController::class, 'expense']
                )->name('expense');

                Route::post(
                    '/store',
                    [\App\Http\Controllers\Admin\FinanceController::class, 'store']
                )->name('store');

                Route::get(
                    '/export/pdf',
                    [\App\Http\Controllers\Admin\FinanceController::class, 'exportPdf']
                )->name('export.pdf');

                Route::get(
                    '/export/csv',
                    [\App\Http\Controllers\Admin\FinanceController::class, 'exportCsv']
                )->name('export.csv');

                Route::put(
                    '/{finance}',
                    [\App\Http\Controllers\Admin\FinanceController::class, 'update']
                )->name('update');

                Route::delete(
                    '/{finance}',
                    [\App\Http\Controllers\Admin\FinanceController::class, 'destroy']
                )->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Admin Payroll Routes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/payroll',
            [\App\Http\Controllers\Admin\PayrollController::class, 'index']
        )->name('payroll.index');

        Route::post(
            '/payroll',
            [\App\Http\Controllers\Admin\PayrollController::class, 'store']
        )->name('payroll.store');

        Route::post(
            '/payroll/generate',
            [\App\Http\Controllers\Admin\PayrollController::class, 'generate']
        )->name('payroll.generate');

        Route::get(
            '/payroll/export/pdf',
            [\App\Http\Controllers\Admin\PayrollController::class, 'exportPdf']
        )->name('payroll.export.pdf');

        Route::get(
            '/payroll/export/csv',
            [\App\Http\Controllers\Admin\PayrollController::class, 'exportCsv']
        )->name('payroll.export.csv');

        Route::put(
            '/payroll/{payroll}',
            [\App\Http\Controllers\Admin\PayrollController::class, 'update']
        )->name('payroll.update');

        Route::post(
            '/payroll/{payroll}/payout',
            [\App\Http\Controllers\Admin\PayrollController::class, 'payout']
        )->name('payroll.payout');

        Route::post(
            '/payroll/{payroll}/payout-cash',
            [\App\Http\Controllers\Admin\PayrollController::class, 'payoutCash']
        )->name('payroll.payout.cash');

        /*
        |--------------------------------------------------------------------------
        | Admin Analytics and Tracking
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/analytics',
            [\App\Http\Controllers\Admin\AnalyticsController::class, 'index']
        )->name('analytics.index');

        Route::get(
            '/tracking',
            [\App\Http\Controllers\Admin\TrackingController::class, 'index']
        )->name('tracking.index');

        Route::get(
            '/tracking/data',
            [\App\Http\Controllers\Admin\TrackingController::class, 'data']
        )->name('tracking.data');

        /*
        |--------------------------------------------------------------------------
        | Admin Landing Page
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/landing-page',
            [\App\Http\Controllers\Admin\LandingPageController::class, 'index']
        )->name('landing-page.index');

        Route::post(
            '/landing-page/{key}',
            [\App\Http\Controllers\Admin\LandingPageController::class, 'update']
        )->name('landing-page.update');

        /*
        |--------------------------------------------------------------------------
        | Admin Pricing Routes
        |--------------------------------------------------------------------------
        */

        Route::get('/pricing', function () {
            return redirect()->route('admin.pricing.services');
        })->name('pricing.index');

        Route::get(
            '/pricing/services',
            [\App\Http\Controllers\Admin\PricingController::class, 'indexServices']
        )->name('pricing.services');

        Route::get(
            '/pricing/item-types',
            [\App\Http\Controllers\Admin\PricingController::class, 'indexItemTypes']
        )->name('pricing.item-types');

        Route::get(
            '/pricing/delivery-fees',
            [\App\Http\Controllers\Admin\PricingController::class, 'indexDeliveryFees']
        )->name('pricing.delivery-fees');

        Route::get(
            '/pricing/taxes',
            [\App\Http\Controllers\Admin\PricingController::class, 'indexTaxes']
        )->name('pricing.taxes');

        Route::post(
            '/pricing/services',
            [\App\Http\Controllers\Admin\PricingController::class, 'storeService']
        )->name('pricing.services.store');

        Route::put(
            '/pricing/services/{service}',
            [\App\Http\Controllers\Admin\PricingController::class, 'updateService']
        )->name('pricing.services.update');

        Route::delete(
            '/pricing/services/{service}',
            [\App\Http\Controllers\Admin\PricingController::class, 'destroyService']
        )->name('pricing.services.destroy');

        Route::post(
            '/pricing/services/{service}/toggle',
            [\App\Http\Controllers\Admin\PricingController::class, 'toggleService']
        )->name('pricing.services.toggle');

        Route::post(
            '/pricing/item-types',
            [\App\Http\Controllers\Admin\PricingController::class, 'storeItemType']
        )->name('pricing.item-types.store');

        Route::put(
            '/pricing/item-types/{itemType}',
            [\App\Http\Controllers\Admin\PricingController::class, 'updateItemType']
        )->name('pricing.item-types.update');

        Route::delete(
            '/pricing/item-types/{itemType}',
            [\App\Http\Controllers\Admin\PricingController::class, 'destroyItemType']
        )->name('pricing.item-types.destroy');

        Route::post(
            '/pricing/item-types/{itemType}/toggle',
            [\App\Http\Controllers\Admin\PricingController::class, 'toggleItemType']
        )->name('pricing.item-types.toggle');

        Route::post(
            '/pricing/delivery-fees',
            [\App\Http\Controllers\Admin\PricingController::class, 'storeDeliveryFee']
        )->name('pricing.delivery-fees.store');

        Route::put(
            '/pricing/delivery-fees/{deliveryFee}',
            [\App\Http\Controllers\Admin\PricingController::class, 'updateDeliveryFee']
        )->name('pricing.delivery-fees.update');

        Route::delete(
            '/pricing/delivery-fees/{deliveryFee}',
            [\App\Http\Controllers\Admin\PricingController::class, 'destroyDeliveryFee']
        )->name('pricing.delivery-fees.destroy');

        Route::post(
            '/pricing/delivery-fees/{deliveryFee}/toggle',
            [\App\Http\Controllers\Admin\PricingController::class, 'toggleDeliveryFee']
        )->name('pricing.delivery-fees.toggle');

        Route::post(
            '/pricing/taxes',
            [\App\Http\Controllers\Admin\PricingController::class, 'storeTax']
        )->name('pricing.taxes.store');

        Route::put(
            '/pricing/taxes/{tax}',
            [\App\Http\Controllers\Admin\PricingController::class, 'updateTax']
        )->name('pricing.taxes.update');

        Route::delete(
            '/pricing/taxes/{tax}',
            [\App\Http\Controllers\Admin\PricingController::class, 'destroyTax']
        )->name('pricing.taxes.destroy');

        Route::post(
            '/pricing/taxes/{tax}/toggle',
            [\App\Http\Controllers\Admin\PricingController::class, 'toggleTax']
        )->name('pricing.taxes.toggle');

        /*
        |--------------------------------------------------------------------------
        | Admin Activity Logs
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/activity-logs',
            [\App\Http\Controllers\Admin\ActivityLogController::class, 'index']
        )->name('activity-logs.index');

        Route::get(
            '/activity-logs/export/pdf',
            [\App\Http\Controllers\Admin\ActivityLogController::class, 'exportPdf']
        )->name('activity-logs.export.pdf');

        Route::get(
            '/activity-logs/export/csv',
            [\App\Http\Controllers\Admin\ActivityLogController::class, 'exportCsv']
        )->name('activity-logs.export.csv');
    });

/*
|--------------------------------------------------------------------------
| Employee Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:karyawan'])
    ->prefix('karyawan')
    ->name('karyawan.')
    ->group(function () {
        Route::get(
            '/dashboard',
            [\App\Http\Controllers\Employee\OrderController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/dashboard/export/pdf',
            [\App\Http\Controllers\Employee\OrderController::class, 'exportPdf']
        )->name('export.pdf');

        Route::get(
            '/dashboard/export/csv',
            [\App\Http\Controllers\Employee\OrderController::class, 'exportCsv']
        )->name('export.csv');

        Route::get(
            '/orders/export/pdf',
            [\App\Http\Controllers\Employee\OrderController::class, 'ordersExportPdf']
        )->name('orders.export.pdf');

        Route::get(
            '/orders/export/csv',
            [\App\Http\Controllers\Employee\OrderController::class, 'ordersExportCsv']
        )->name('orders.export.csv');

        Route::get(
            '/orders/create',
            [\App\Http\Controllers\Employee\OrderController::class, 'create']
        )->name('orders.create');

        Route::post(
            '/orders',
            [\App\Http\Controllers\Employee\OrderController::class, 'store']
        )->name('orders.store');

        Route::post(
            '/orders/{order}/status',
            [\App\Http\Controllers\Employee\OrderController::class, 'updateStatus']
        )->name('orders.status');

        Route::post(
            '/orders/{order}/assign',
            [\App\Http\Controllers\Employee\OrderController::class, 'assignCourier']
        )->name('orders.assign');

        Route::get(
            '/orders',
            [\App\Http\Controllers\Employee\OrderController::class, 'ordersIndex']
        )->name('orders.index');

        Route::get(
            '/orders/{order}/edit',
            [\App\Http\Controllers\Employee\OrderController::class, 'edit']
        )->name('orders.edit');

        Route::put(
            '/orders/{order}',
            [\App\Http\Controllers\Employee\OrderController::class, 'update']
        )->name('orders.update');

        Route::delete(
            '/orders/{order}',
            [\App\Http\Controllers\Employee\OrderController::class, 'destroy']
        )->name('orders.destroy');

        Route::get(
            '/orders/{order}',
            [\App\Http\Controllers\Employee\OrderController::class, 'show']
        )->name('orders.show');

        /*
        |--------------------------------------------------------------------------
        | Employee Attendance Routes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/attendance',
            [\App\Http\Controllers\Employee\AttendanceController::class, 'index']
        )->name('attendance.index');

        Route::post(
            '/attendance/check-in',
            [\App\Http\Controllers\Employee\AttendanceController::class, 'checkIn']
        )->name('attendance.check-in');

        Route::post(
            '/attendance/check-out',
            [\App\Http\Controllers\Employee\AttendanceController::class, 'checkOut']
        )->name('attendance.check-out');

        Route::post(
            '/attendance/request',
            [\App\Http\Controllers\Employee\AttendanceController::class, 'applyPermitLeave']
        )->name('attendance.request');

        /*
        |--------------------------------------------------------------------------
        | Employee Tracking Routes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/tracking',
            [\App\Http\Controllers\Employee\TrackingController::class, 'index']
        )->name('tracking.index');

        Route::get(
            '/tracking/data',
            [\App\Http\Controllers\Employee\TrackingController::class, 'data']
        )->name('tracking.data');

        /*
        |--------------------------------------------------------------------------
        | Employee Salary Routes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/salary',
            [\App\Http\Controllers\Employee\SalaryController::class, 'index']
        )->name('salary.index');

        Route::post(
            '/salary/{payroll}/withdraw',
            [\App\Http\Controllers\Employee\SalaryController::class, 'withdraw']
        )->name('salary.withdraw');

        Route::get(
            '/salary/export/pdf',
            [\App\Http\Controllers\Employee\SalaryController::class, 'exportPdf']
        )->name('salary.export.pdf');

        Route::get(
            '/salary/export/csv',
            [\App\Http\Controllers\Employee\SalaryController::class, 'exportCsv']
        )->name('salary.export.csv');

        Route::get(
            '/salary/{payroll}/payslip/pdf',
            [\App\Http\Controllers\Employee\SalaryController::class, 'downloadPayslipPdf']
        )->name('salary.payslip.pdf');
    });

/*
|--------------------------------------------------------------------------
| Courier Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:kurir'])
    ->prefix('kurir')
    ->name('kurir.')
    ->group(function () {
        Route::get(
            '/dashboard',
            [\App\Http\Controllers\Courier\OrderController::class, 'index']
        )->name('dashboard');

        /*
         * Harus berada sebelum /orders/{order},
         * agar "orders" tidak dianggap sebagai parameter order.
         */
        Route::get(
            '/delivery-board',
            [\App\Http\Controllers\Courier\OrderController::class, 'deliveryBoard']
        )->name('delivery-board');

        Route::get(
            '/orders',
            [\App\Http\Controllers\Courier\OrderController::class, 'orders']
        )->name('orders.index');

        Route::get(
            '/orders/{order}',
            [\App\Http\Controllers\Courier\OrderController::class, 'show']
        )->name('orders.show');

        Route::post(
            '/orders/{order}/status',
            [\App\Http\Controllers\Courier\OrderController::class, 'updateStatus']
        )->name('orders.status');

        Route::post(
            '/location',
            [\App\Http\Controllers\Courier\OrderController::class, 'updateLocation']
        )->name('location.update');

        /*
        |--------------------------------------------------------------------------
        | Courier Salary Routes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/salary',
            [\App\Http\Controllers\Courier\SalaryController::class, 'index']
        )->name('salary.index');

        Route::get(
            '/salary/export/pdf',
            [\App\Http\Controllers\Courier\SalaryController::class, 'exportPdf']
        )->name('salary.export.pdf');

        Route::get(
            '/salary/export/csv',
            [\App\Http\Controllers\Courier\SalaryController::class, 'exportCsv']
        )->name('salary.export.csv');

        Route::post(
            '/salary/{payroll}/withdraw',
            [\App\Http\Controllers\Courier\SalaryController::class, 'withdraw']
        )->name('salary.withdraw');

        Route::get(
            '/salary/{payroll}/payslip/pdf',
            [\App\Http\Controllers\Courier\SalaryController::class, 'downloadPayslipPdf']
        )->name('salary.payslip.pdf');


        /*
        |--------------------------------------------------------------------------
        | Courier Attendance Routes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/attendance',
            [\App\Http\Controllers\Courier\AttendanceController::class, 'index']
        )->name('attendance.index');

        Route::post(
            '/attendance/check-in',
            [\App\Http\Controllers\Courier\AttendanceController::class, 'checkIn']
        )->name('attendance.check-in');

        Route::post(
            '/attendance/check-out',
            [\App\Http\Controllers\Courier\AttendanceController::class, 'checkOut']
        )->name('attendance.check-out');

        Route::post(
            '/attendance/request',
            [\App\Http\Controllers\Courier\AttendanceController::class, 'applyPermitLeave']
        )->name('attendance.request');
    });

/*
|--------------------------------------------------------------------------
| Shared Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');

    Route::post(
        '/orders/{order}/messages',
        [\App\Http\Controllers\MessageController::class, 'store']
    )->name('messages.store');

    Route::get(
        '/orders/{order}/locations',
        [\App\Http\Controllers\Customer\OrderController::class, 'locations']
    )->name('orders.locations.data');
});

require __DIR__.'/auth.php';