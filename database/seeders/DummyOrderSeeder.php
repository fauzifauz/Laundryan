<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ItemType;
use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use Carbon\Carbon;

class DummyOrderSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Services
        $s1 = Service::updateOrCreate(['name' => 'Cuci Lipat'], ['base_price' => 5000, 'is_active' => true]);
        $s2 = Service::updateOrCreate(['name' => 'Cuci Setrika'], ['base_price' => 7000, 'is_active' => true]);
        $s3 = Service::updateOrCreate(['name' => 'Express 6 Jam'], ['base_price' => 15000, 'is_active' => true]);

        // 2. Create Item Types
        $i1 = ItemType::updateOrCreate(['name' => 'Pakaian Kiloan'], ['base_price' => 0, 'is_active' => true]);
        $i2 = ItemType::updateOrCreate(['name' => 'Bedcover'], ['base_price' => 25000, 'is_active' => true]);
        $i3 = ItemType::updateOrCreate(['name' => 'Jas / Blazer'], ['base_price' => 35000, 'is_active' => true]);

        // 3. Get Users
        $customer = User::where('role', 'pelanggan')->first();
        $courier = User::where('role', 'kurir')->first();

        if (!$customer || !$courier) return;

        $soaps = ['Rinso Matic', 'Attack Hygiene', 'So Klin Liquid'];
        $fragrances = ['Molen Blue', 'Downy Mystique', 'Downy Passion'];

        // 4. Create Dummy Orders
        for ($i = 0; $i < 15; $i++) {
            $status = ['arrived_at_laundry', 'washing', 'drying_ironing', 'packing', 'completed'][rand(0, 4)];
            
            $order = Order::create([
                'order_code' => 'ORD-' . strtoupper(bin2hex(random_bytes(3))),
                'customer_id' => $customer->id,
                'service_id' => rand(1, 3),
                'item_type_id' => rand(1, 3),
                'courier_id' => $courier->id,
                'pickup_courier_id' => $courier->id,
                'delivery_courier_id' => $courier->id,
                'pickup_address' => 'Jl. Merdeka No. ' . rand(1, 100),
                'pickup_lat' => -6.200000 + (rand(-100, 100) / 1000),
                'pickup_lng' => 106.816666 + (rand(-100, 100) / 1000),
                'delivery_address' => 'Jl. Merdeka No. ' . rand(1, 100),
                'delivery_lat' => -6.200000 + (rand(-100, 100) / 1000),
                'delivery_lng' => 106.816666 + (rand(-100, 100) / 1000),
                'pickup_time' => Carbon::now()->addHours(rand(1, 24)),
                'service_price' => 5000,
                'item_price' => 20000,
                'shipping_cost' => 5000,
                'tax' => 2000,
                'total_price' => 32000,
                'status' => $status,
                'payment_status' => 'paid',
                'soap' => $soaps[rand(0, 2)],
                'fragrance' => $fragrances[rand(0, 2)],
                'payment_method' => ['cash', 'transfer', 'e-wallet'][rand(0, 2)],
                'created_at' => Carbon::now()->subDays(rand(0, 6)),
            ]);

            // Add Status Logs
            $employee = User::where('role', 'karyawan')->first();
            $adminUser = User::where('role', 'admin')->first();
            $creatorId = $adminUser ? $adminUser->id : $customer->id;

            \App\Models\OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => 'pending_payment',
                'user_id' => $creatorId,
                'created_at' => $order->created_at,
            ]);

            \App\Models\OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => 'waiting_pickup',
                'user_id' => $adminUser ? $adminUser->id : $customer->id,
                'created_at' => $order->created_at->addMinutes(10),
            ]);

            if (in_array($status, ['arrived_at_laundry', 'washing', 'drying_ironing', 'packing', 'completed'])) {
                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'arrived_at_laundry',
                    'user_id' => $employee ? $employee->id : $courier->id,
                    'created_at' => $order->created_at->addHours(2),
                ]);
            }

            if (in_array($status, ['washing', 'drying_ironing', 'packing', 'completed'])) {
                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'washing',
                    'user_id' => $employee ? $employee->id : $courier->id,
                    'created_at' => $order->created_at->addHours(4),
                ]);
            }

            if (in_array($status, ['drying_ironing', 'packing', 'completed'])) {
                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'drying_ironing',
                    'user_id' => $employee ? $employee->id : $courier->id,
                    'created_at' => $order->created_at->addHours(6),
                ]);
            }

            if (in_array($status, ['packing', 'completed'])) {
                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'packing',
                    'user_id' => $employee ? $employee->id : $courier->id,
                    'created_at' => $order->created_at->addHours(8),
                ]);
            }

            if ($status === 'completed') {
                \App\Models\OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'completed',
                    'user_id' => $courier->id,
                    'created_at' => $order->created_at->addHours(10),
                ]);

                Review::create([
                    'order_id' => $order->id,
                    'rating' => rand(4, 5),
                    'rating_service' => rand(4, 5),
                    'rating_courier' => rand(4, 5),
                    'comment' => 'Sangat puas dengan hasilnya!',
                ]);
            }
        }
    }
}
