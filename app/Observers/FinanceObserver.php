<?php

namespace App\Observers;

use App\Models\Finance;
use App\Models\ActivityLog;

class FinanceObserver
{
    public function created(Finance $finance)
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : 'System';
        $amountFormatted = 'Rp' . number_format($finance->amount, 0, ',', '.');
        $typeTxt = $finance->type === 'income' ? 'income' : 'expense';

        ActivityLog::log(
            'Finance',
            'Transaction Created',
            'New ' . $typeTxt . ' transaction of ' . $amountFormatted . ' added',
            'Finance',
            $finance->id,
            null,
            $finance->toArray()
        );
    }

    public function updated(Finance $finance)
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : 'System';
        $typeTxt = $finance->type === 'income' ? 'income' : 'expense';

        ActivityLog::log(
            'Finance',
            'Transaction Updated',
            'Transaction for ' . $typeTxt . ' updated',
            'Finance',
            $finance->id,
            $finance->getOriginal(),
            $finance->toArray()
        );

        // ─── SYNC WITH ORDER & PAYMENT ───────────────────────────────────
        if (preg_match('/ORD-[A-Z0-9]+/', $finance->description, $matches)) {
            $orderCode = $matches[0];
            $order = \App\Models\Order::where('order_code', $orderCode)->first();
            if ($order) {
                // Sync Order total_price
                $order->update(['total_price' => $finance->amount]);

                // Sync corresponding Payment amount
                $payment = \App\Models\Payment::where('order_id', $order->id)->first();
                if ($payment) {
                    $payment->update([
                        'amount' => $finance->amount,
                        'payment_method' => strtolower($finance->payment_method),
                    ]);
                }
            }
        }

        // ─── SYNC WITH PAYROLL ───────────────────────────────────────────
        if (in_array($finance->category, ['Payroll', 'Penggajian']) || str_contains(strtolower($finance->description), 'salary')) {
            if (preg_match('/(?:payment|payout) for (.+) \((.+)\)/i', $finance->description, $matches)) {
                $userName = trim($matches[1]);
                $periodStr = trim($matches[2]);

                try {
                    $period = \Illuminate\Support\Carbon::parse($periodStr);
                    $month = $period->month;
                    $year = $period->year;

                    $user = \App\Models\User::where('name', $userName)->first();
                    if ($user) {
                        $payroll = \App\Models\Payroll::where('user_id', $user->id)
                            ->where('month', $month)
                            ->where('year', $year)
                            ->first();

                        if ($payroll) {
                            $payroll->update([
                                'amount' => $finance->amount,
                                'bonus' => 0,
                                'potongan' => 0,
                                'payment_method' => strtolower($finance->payment_method),
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore date parsing exceptions
                }
            }
        }
    }

    public function deleted(Finance $finance)
    {
        $actor = auth()->user();
        $actorName = $actor ? $actor->name : 'System';
        $amountFormatted = 'Rp' . number_format($finance->amount, 0, ',', '.');
        $typeTxt = $finance->type === 'income' ? 'income' : 'expense';

        ActivityLog::log(
            'Finance',
            'Transaction Deleted',
            'Transaction for ' . $typeTxt . ' of ' . $amountFormatted . ' deleted',
            'Finance',
            $finance->id,
            $finance->toArray(),
            null
        );

        // ─── SYNC WITH ORDER & PAYMENT (MARK AS UNPAID) ──────────────────
        if (preg_match('/ORD-[A-Z0-9]+/', $finance->description, $matches)) {
            $orderCode = $matches[0];
            $order = \App\Models\Order::where('order_code', $orderCode)->first();
            if ($order) {
                $order->update(['payment_status' => 'unpaid']);
                $payment = \App\Models\Payment::where('order_id', $order->id)->first();
                if ($payment) {
                    $payment->delete();
                }
            }
        }

        // ─── SYNC WITH PAYROLL (RESET TO PENDING) ────────────────────────
        if (in_array($finance->category, ['Payroll', 'Penggajian']) || str_contains(strtolower($finance->description), 'salary')) {
            if (preg_match('/(?:payment|payout) for (.+) \((.+)\)/i', $finance->description, $matches)) {
                $userName = trim($matches[1]);
                $periodStr = trim($matches[2]);

                try {
                    $period = \Illuminate\Support\Carbon::parse($periodStr);
                    $month = $period->month;
                    $year = $period->year;

                    $user = \App\Models\User::where('name', $userName)->first();
                    if ($user) {
                        $payroll = \App\Models\Payroll::where('user_id', $user->id)
                            ->where('month', $month)
                            ->where('year', $year)
                            ->first();

                        if ($payroll) {
                            $payroll->update([
                                'status' => 'pending',
                                'payment_date' => null,
                                'stripe_transfer_id' => null,
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore date parsing exceptions
                }
            }
        }
    }
}
