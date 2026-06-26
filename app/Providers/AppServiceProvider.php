<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\PasswordReset::class,
            \App\Listeners\ClearLoginLockout::class
        );

        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\Payment::observe(\App\Observers\PaymentObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Finance::observe(\App\Observers\FinanceObserver::class);
        \App\Models\Attendance::observe(\App\Observers\AttendanceObserver::class);
        \App\Models\Payroll::observe(\App\Observers\PayrollObserver::class);
        \App\Models\Service::observe(\App\Observers\ServiceObserver::class);
        \App\Models\ItemType::observe(\App\Observers\ItemTypeObserver::class);
        \App\Models\Tax::observe(\App\Observers\TaxObserver::class);
        \App\Models\LandingPageSetting::observe(\App\Observers\LandingPageSettingObserver::class);
    }
}
