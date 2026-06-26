<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'ithelpsdesk1@gmail.com'],
            [
                'name' => 'Admin Laundryan',
                'phone' => '081234567890',
                'role' => 'admin',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'karyawan@laundryan.com'],
            [
                'name' => 'Karyawan Dummy',
                'phone' => '081234567891',
                'role' => 'karyawan',
                'status' => 'active',
                'password' => bcrypt('password'),
                'stripe_account_id' => 'acct_1StEdkCQHbYHU5hT', 
            ]
        );

        User::updateOrCreate(
            ['email' => 'kurir@laundryan.com'],
            [
                'name' => 'Kurir Dummy',
                'phone' => '081234567892',
                'role' => 'kurir',
                'status' => 'active',
                'password' => bcrypt('password'),
                'stripe_account_id' => 'acct_1StEdkCQHbYHU5hT',
            ]
        );

        User::updateOrCreate(
            ['email' => 'pelanggan@laundryan.com'],
            [
                'name' => 'Pelanggan Dummy',
                'phone' => '081234567893',
                'role' => 'pelanggan',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]
        );

        // Seed Delivery Fees
        \App\Models\DeliveryFee::updateOrCreate(
            ['min_distance' => 0.00, 'max_distance' => 5.00],
            ['fee' => 2000, 'min_fee' => 10000, 'max_fee' => 10000, 'is_active' => true]
        );
        \App\Models\DeliveryFee::updateOrCreate(
            ['min_distance' => 5.00, 'max_distance' => 10.00],
            ['fee' => 2500, 'min_fee' => 15000, 'max_fee' => 15000, 'is_active' => true]
        );
        \App\Models\DeliveryFee::updateOrCreate(
            ['min_distance' => 10.00, 'max_distance' => 25.00],
            ['fee' => 3000, 'min_fee' => 25000, 'max_fee' => 35000, 'is_active' => true]
        );

        // Seed Taxes
        \App\Models\Tax::updateOrCreate(
            ['name' => 'PPN'],
            ['percentage' => 10.00, 'is_active' => true]
        );

        $this->call([
            LandingPageSeeder::class,
            DummyOrderSeeder::class,
            PaymentSeeder::class,
            TrackingDummySeeder::class,
            AttendanceSeeder::class,
            PayrollDummySeeder::class,
        ]);
    }
}
