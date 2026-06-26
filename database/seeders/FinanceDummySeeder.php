<?php

namespace Database\Seeders;

use App\Models\Finance;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Models\ItemType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FinanceDummySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure we have a customer
        $customer = User::where('role', 'pelanggan')->first();
        if (!$customer) {
            $customer = User::create([
                'name' => 'Dummy Customer',
                'email' => 'customer@dummy.com',
                'password' => bcrypt('password'),
                'role' => 'pelanggan'
            ]);
        }

        // 2. Ensure we have item types
        $itemType = ItemType::first();
        if (!$itemType) {
            $itemType = ItemType::create(['name' => 'Pakaian', 'price' => 5000]);
        }

        // 3. Create Services
        $services = [
            ['name' => 'Cuci Kering Reguler', 'base_price' => 7000],
            ['name' => 'Cuci Kering Express', 'base_price' => 12000],
            ['name' => 'Setrika Saja', 'base_price' => 5000],
            ['name' => 'Cuci Selimut', 'base_price' => 25000],
            ['name' => 'Cuci Sepatu Premium', 'base_price' => 35000],
        ];

        $serviceModels = [];
        foreach ($services as $s) {
            $serviceModels[] = Service::updateOrCreate(['name' => $s['name']], $s);
        }

        // 4. Generate Data for different timeframes
        $now = Carbon::now();
        $expenseCategories = ['Payroll', 'Sabun', 'Pewangi', 'Listrik', 'Air', 'Sewa', 'Transportasi'];

        for ($i = 0; $i <= 12; $i++) {
            $date = $now->copy()->subMonths($i);
            
            $incomeCount = rand(5, 15);
            for ($j = 0; $j < $incomeCount; $j++) {
                $randomDay = rand(1, 28);
                $entryDate = $date->copy()->day($randomDay);
                if ($entryDate->isAfter($now)) continue;

                $randomService = $serviceModels[array_rand($serviceModels)];
                $amount = rand(50000, 200000);
                $orderCode = 'ORD-' . strtoupper(Str::random(8));

                Order::create([
                    'order_code' => $orderCode,
                    'customer_id' => $customer->id,
                    'service_id' => $randomService->id,
                    'item_type_id' => $itemType->id,
                    'pickup_address' => 'Jl. Dummy No. ' . rand(1, 100),
                    'delivery_address' => 'Jl. Dummy No. ' . rand(1, 100),
                    'pickup_time' => $entryDate,
                    'service_price' => $randomService->base_price,
                    'item_price' => $amount - 15000,
                    'shipping_cost' => 10000,
                    'tax' => 5000,
                    'total_price' => $amount,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                    'created_at' => $entryDate,
                ]);

                Finance::create([
                    'type' => 'income',
                    'amount' => $amount,
                    'category' => 'Laundry Order',
                    'description' => "Payment for order {$orderCode}",
                    'date' => $entryDate,
                ]);
            }

            $expenseCount = rand(3, 8);
            foreach (range(1, $expenseCount) as $k) {
                $randomDay = rand(1, 28);
                $entryDate = $date->copy()->day($randomDay);
                if ($entryDate->isAfter($now)) continue;

                $category = $expenseCategories[array_rand($expenseCategories)];
                $amount = $category === 'Payroll' ? rand(1000000, 3000000) : rand(50000, 500000);

                Finance::create([
                    'type' => 'expense',
                    'amount' => $amount,
                    'category' => $category,
                    'description' => "Monthly {$category} cost for " . $entryDate->format('M Y'),
                    'date' => $entryDate,
                ]);
            }
        }

        // 5. Create specific data for CURRENT WEEK to ensure visibility
        foreach (range(1, 10) as $i) {
            $entryDate = $now->copy()->subDays(rand(0, 6));
            $amount = rand(100000, 300000);
            
            Finance::create([
                'type' => 'income',
                'amount' => $amount,
                'category' => 'Laundry Order',
                'description' => "Fresh income record for testing",
                'date' => $entryDate,
            ]);

            Finance::create([
                'type' => 'expense',
                'amount' => rand(20000, 50000),
                'category' => 'Sabun',
                'description' => "Fresh expense record for testing",
                'date' => $entryDate,
            ]);
        }

        $this->command->info('Finance dummy data seeded successfully!');
    }
}
