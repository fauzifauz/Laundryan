<?php

namespace App\Observers;

use App\Models\Payroll;
use App\Models\ActivityLog;

class PayrollObserver
{
    public function updated(Payroll $payroll)
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : 'System';

        if ($payroll->isDirty('status') && $payroll->status === 'paid' && $payroll->getOriginal('status') !== 'paid') {
            $user = $payroll->user;
            $userName = $user ? $user->name : 'Employee';
            $netSalary = $payroll->amount + $payroll->bonus - $payroll->potongan;
            $amountFormatted = 'Rp' . number_format($netSalary, 0, ',', '.');

            ActivityLog::log(
                'Payroll & Attendance',
                'Payroll Processed',
                'Payroll for employee "' . $userName . '" of ' . $amountFormatted . ' processed',
                'Payroll',
                $payroll->id,
                $payroll->getOriginal(),
                $payroll->toArray()
            );
        }
    }
}
