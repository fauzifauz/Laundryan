<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Payroll;
use Carbon\Carbon;

class PayrollDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure our employee and courier users exist by fetching them
        $employees = User::whereIn('role', ['karyawan', 'kurir'])->get();

        if ($employees->isEmpty()) {
            // Fallback if users seeder hasn't run yet
            $this->call(AttendanceSeeder::class);
            $employees = User::whereIn('role', ['karyawan', 'kurir'])->get();
        }

        $now = Carbon::now();
        
        // Define past months to seed
        $monthsToSeed = [
            // Current Month
            ['month' => $now->month, 'year' => $now->year],
            // Last Month
            ['month' => $now->copy()->subMonth()->month, 'year' => $now->copy()->subMonth()->year],
            // Two Months Ago
            ['month' => $now->copy()->subMonths(2)->month, 'year' => $now->copy()->subMonths(2)->year],
        ];

        foreach ($employees as $user) {
            // Determine base salary based on role
            $baseSalary = ($user->role === 'karyawan') ? 4500000.00 : 3500000.00;

            foreach ($monthsToSeed as $index => $period) {
                // If it is the current month, some can be pending/failed, whereas previous months are always paid
                $isCurrentMonth = ($period['month'] == $now->month && $period['year'] == $now->year);
                
                $status = 'paid';
                $paymentMethod = null;
                $paymentDate = null;
                $stripeTransferId = null;

                if ($isCurrentMonth) {
                    // Random status distribution for active month
                    $rand = rand(1, 10);
                    if ($rand <= 6) {
                        $status = 'paid';
                    } elseif ($rand <= 9) {
                        $status = 'pending';
                    } else {
                        $status = 'failed';
                    }
                }

                // If paid or failed (if paid, set payment details)
                if ($status === 'paid') {
                    $paymentMethod = ['stripe', 'cash', 'bank_transfer'][array_rand(['stripe', 'cash', 'bank_transfer'])];
                    $paymentDate = Carbon::create($period['year'], $period['month'], rand(25, 28), rand(9, 17), rand(0, 59));
                    
                    if ($paymentMethod === 'stripe') {
                        $stripeTransferId = 'tr_' . bin2hex(random_bytes(8));
                    }
                } elseif ($status === 'failed') {
                    $paymentMethod = 'stripe';
                    $paymentDate = Carbon::create($period['year'], $period['month'], rand(25, 28), rand(9, 17), rand(0, 59));
                    $stripeTransferId = 'tr_err_' . bin2hex(random_bytes(8));
                }

                // Random bonus and deductions
                $bonus = rand(0, 4) * 100000.00; // Rp 0 - Rp 400.000
                $potongan = rand(0, 2) * 50000.00; // Rp 0 - Rp 100.000

                Payroll::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'month'   => $period['month'],
                        'year'    => $period['year'],
                    ],
                    [
                        'amount'             => $baseSalary,
                        'bonus'              => $bonus,
                        'potongan'           => $potongan,
                        'status'             => $status,
                        'payment_method'     => $paymentMethod,
                        'payment_date'       => $paymentDate,
                        'stripe_transfer_id' => $stripeTransferId,
                    ]
                );
            }
        }

        echo "Payroll dummy data seeded successfully!\n";
    }
}
