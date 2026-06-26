<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\Location;
use App\Models\Service;
use App\Models\ItemType;
use Carbon\Carbon;

class TrackingDummySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get Base Data
        $service = Service::first();
        $itemType = ItemType::first();
        $mainCourier = User::where('email', 'kurir@laundryan.com')->first();

        if (!$service || !$itemType) {
            echo "Ensure services and item types exist before seeding tracking.\n";
            return;
        }

        // Give location to Main Courier (Kurir Dummy) so they appear as Idle at Base
        if ($mainCourier) {
            Location::updateOrCreate(
                ['user_id' => $mainCourier->id],
                [
                    'latitude' => -6.1664983,
                    'longitude' => 106.5602886,
                    'updated_at' => now(),
                ]
            );
        }

        // --- BUAT PELANGGAN DUMMY UNTUK TESTING ---
        $customer1 = User::updateOrCreate(
            ['email' => 'budi.pelanggan@test.com'],
            ['name' => 'Budi Pelanggan', 'phone' => '0811111111', 'role' => 'pelanggan', 'password' => bcrypt('password')]
        );
        $customer2 = User::updateOrCreate(
            ['email' => 'siti.customer@test.com'],
            ['name' => 'Siti Customer', 'phone' => '0822222222', 'role' => 'pelanggan', 'password' => bcrypt('password')]
        );
        $customer3 = User::updateOrCreate(
            ['email' => 'agus.buyer@test.com'],
            ['name' => 'Agus Buyer', 'phone' => '0833333333', 'role' => 'pelanggan', 'password' => bcrypt('password')]
        );

        // 2. Courier A: STATUS "PENJEMPUTAN" (Menuju Lokasi Pelanggan)
        $courierA = User::updateOrCreate(
            ['email' => 'kurir.pickup@laundryan.com'],
            [
                'name' => 'Andi Kurir',
                'phone' => '081211112222',
                'role' => 'kurir',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]
        );

        Location::updateOrCreate(
            ['user_id' => $courierA->id],
            ['latitude' => -6.168000, 'longitude' => 106.565000, 'updated_at' => now()]
        );

        Order::updateOrCreate(
            ['order_code' => 'TRX-00123'],
            [
                'customer_id' => $customer1->id,
                'service_id' => $service->id,
                'item_type_id' => $itemType->id,
                'courier_id' => $courierA->id,
                'pickup_address' => 'Jalan Merdeka No. 10',
                'pickup_lat' => -6.175000,
                'pickup_lng' => 106.575000,
                'delivery_address' => 'Laundryan HQ',
                'delivery_lat' => -6.1664983,
                'delivery_lng' => 106.5602886,
                'pickup_time' => now()->addHour(),
                'service_price' => 5000,
                'item_price' => 15000,
                'shipping_cost' => 5000,
                'tax' => 2000,
                'total_price' => 27000,
                'status' => 'penjemputan',
                'payment_status' => 'paid',
            ]
        );

        // 3. Courier B: STATUS "DIJEMPUT" (Barang Sudah Di Tangan Kurir)
        $courierB = User::updateOrCreate(
            ['email' => 'kurir.dijemput@laundryan.com'],
            [
                'name' => 'Bowo Express',
                'phone' => '081233334444',
                'role' => 'kurir',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]
        );

        Location::updateOrCreate(
            ['user_id' => $courierB->id],
            ['latitude' => -6.173000, 'longitude' => 106.573000, 'updated_at' => now()]
        );

        Order::updateOrCreate(
            ['order_code' => 'TRX-00456'],
            [
                'customer_id' => $customer2->id,
                'service_id' => $service->id,
                'item_type_id' => $itemType->id,
                'courier_id' => $courierB->id,
                'pickup_address' => 'Perumahan Indah Blok C',
                'pickup_lat' => -6.175000,
                'pickup_lng' => 106.575000,
                'delivery_address' => 'Laundryan HQ',
                'delivery_lat' => -6.1664983,
                'delivery_lng' => 106.5602886,
                'pickup_time' => now()->subHour(),
                'service_price' => 5000,
                'item_price' => 25000,
                'shipping_cost' => 5000,
                'tax' => 3000,
                'total_price' => 38000,
                'status' => 'dijemput',
                'payment_status' => 'paid',
            ]
        );

        // 4. Courier C: STATUS "PENGANTARAN" (Menuju Pelanggan)
        $courierC = User::updateOrCreate(
            ['email' => 'kurir.diantar@laundryan.com'],
            [
                'name' => 'Candra Logistik',
                'phone' => '081255556666',
                'role' => 'kurir',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]
        );

        Location::updateOrCreate(
            ['user_id' => $courierC->id],
            ['latitude' => -6.170000, 'longitude' => 106.562000, 'updated_at' => now()]
        );

        Order::updateOrCreate(
            ['order_code' => 'TRX-00789'],
            [
                'customer_id' => $customer3->id,
                'service_id' => $service->id,
                'item_type_id' => $itemType->id,
                'courier_id' => $courierC->id,
                'pickup_address' => 'Laundryan HQ',
                'pickup_lat' => -6.1664983,
                'pickup_lng' => 106.5602886,
                'delivery_address' => 'Apartemen Mewah Lantai 10',
                'delivery_lat' => -6.180000,
                'delivery_lng' => 106.580000,
                'pickup_time' => now()->subHours(2),
                'service_price' => 5000,
                'item_price' => 35000,
                'shipping_cost' => 5000,
                'tax' => 4000,
                'total_price' => 49000,
                'status' => 'pengantaran',
                'payment_status' => 'paid',
            ]
        );

        echo "Tracking dummy data seeded successfully!\n";
    }
}
