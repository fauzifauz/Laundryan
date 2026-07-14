<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Review;
use App\Models\Service;
use App\Models\ItemType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder demo: bikin aktivitas HARI INI buat "Kurir Dummy" supaya
 * card "Aktivitas Hari Ini" & "Tugas Aktif" di dashboard kurir keisi.
 *
 * Jalankan dengan:
 *   php artisan db:seed --class=DemoKurirTodaySeeder
 *
 * Aman dijalankan berkali-kali, order lama dari seeder ini akan
 * dihapus dulu (ditandai lewat prefix order_code "DEMO-").
 */
class DemoKurirTodaySeeder extends Seeder
{
    public function run(): void
    {
        $admin    = User::where('email', 'ithelpsdesk1@gmail.com')->first() ?? User::where('role', 'admin')->first();
        $karyawan = User::where('email', 'karyawan@laundryan.com')->first() ?? User::where('role', 'karyawan')->first();
        $kurir    = User::where('email', 'kurir@laundryan.com')->first() ?? User::where('role', 'kurir')->first();
        $customer = User::where('email', 'pelanggan@laundryan.com')->first() ?? User::where('role', 'pelanggan')->first();

        if (!$admin || !$karyawan || !$kurir || !$customer) {
            $this->command?->error('User demo (admin/karyawan/kurir/pelanggan) belum lengkap. Jalankan DatabaseSeeder dulu.');
            return;
        }

        $service = Service::first() ?? Service::create(['name' => 'Cuci Lipat', 'base_price' => 5000, 'is_active' => true]);
        $itemType = ItemType::first() ?? ItemType::create(['name' => 'Pakaian Kiloan', 'base_price' => 0, 'is_active' => true]);

        // Bersihin data demo lama biar tidak numpuk kalau seeder dijalankan berkali-kali
        $oldDemoOrders = Order::where('order_code', 'like', 'DEMO-%')->pluck('id');
        OrderStatusLog::whereIn('order_id', $oldDemoOrders)->delete();
        Review::whereIn('order_id', $oldDemoOrders)->delete();
        Order::whereIn('id', $oldDemoOrders)->delete();

        $today = Carbon::today();

        $baseOrderData = [
            'customer_id'     => $customer->id,
            'service_id'      => $service->id,
            'item_type_id'    => $itemType->id,
            'pickup_address'  => 'Jl. Demo Aktivitas No. 1',
            'pickup_lat'      => -6.1664983,
            'pickup_lng'      => 106.5602886,
            'delivery_address'=> 'Jl. Demo Aktivitas No. 2',
            'delivery_lat'    => -6.1700000,
            'delivery_lng'    => 106.5650000,
            'service_price'   => 5000,
            'item_price'      => 20000,
            'shipping_cost'   => 5000,
            'tax'             => 2000,
            'total_price'     => 32000,
            'payment_status'  => 'paid',
            'soap'            => 'Rinso Matic',
            'fragrance'       => 'Downy Passion',
            'payment_method'  => 'transfer',
        ];

        // ------------------------------------------------------------------
        // ORDER A — sudah selesai lengkap hari ini (banyak aktivitas kurir)
        // ------------------------------------------------------------------
        $orderA = Order::create(array_merge($baseOrderData, [
            'order_code'          => 'DEMO-' . strtoupper(bin2hex(random_bytes(3))),
            'pickup_time'         => $today->copy()->setTime(8, 0),
            'pickup_courier_id'   => $kurir->id,
            'delivery_courier_id' => $kurir->id,
            'courier_id'          => $kurir->id,
            'status'              => 'completed',
        ]));
        $this->setCreatedAt($orderA, $today->copy()->setTime(8, 0));

        $this->log($orderA, 'pending_payment',      $admin->id,    $today->copy()->setTime(8, 0));
        $this->log($orderA, 'waiting_pickup',        $admin->id,    $today->copy()->setTime(8, 10));
        $this->log($orderA, 'picking_up',            $kurir->id,    $today->copy()->setTime(8, 30));
        $this->log($orderA, 'picked_up',              $kurir->id,    $today->copy()->setTime(9, 0));
        $this->log($orderA, 'in_transit_to_laundry', $kurir->id,    $today->copy()->setTime(9, 20));
        $this->log($orderA, 'arrived_at_laundry',    $kurir->id,    $today->copy()->setTime(9, 45));
        $this->log($orderA, 'washing',                $karyawan->id, $today->copy()->setTime(10, 30));
        $this->log($orderA, 'drying_ironing',        $karyawan->id, $today->copy()->setTime(12, 0));
        $this->log($orderA, 'packing',                $karyawan->id, $today->copy()->setTime(13, 0));
        $this->log($orderA, 'ready_for_delivery',    $karyawan->id, $today->copy()->setTime(13, 30));
        $this->log($orderA, 'delivering',             $kurir->id,    $today->copy()->setTime(14, 0));
        $this->log($orderA, 'completed',              $kurir->id,    $today->copy()->setTime(14, 40));

        Review::create([
            'order_id'       => $orderA->id,
            'rating'         => 5,
            'rating_service' => 5,
            'rating_courier' => 5,
            'comment'        => 'Cepat dan rapi, terima kasih!',
        ]);

        // ------------------------------------------------------------------
        // ORDER B — masih proses pickup (muncul di "Tugas Aktif")
        // ------------------------------------------------------------------
        $orderB = Order::create(array_merge($baseOrderData, [
            'order_code'        => 'DEMO-' . strtoupper(bin2hex(random_bytes(3))),
            'pickup_time'       => $today->copy()->setTime(15, 0),
            'pickup_courier_id' => $kurir->id,
            'courier_id'        => $kurir->id,
            'status'            => 'picking_up',
        ]));
        $this->setCreatedAt($orderB, $today->copy()->setTime(15, 0));

        $this->log($orderB, 'pending_payment', $admin->id, $today->copy()->setTime(15, 0));
        $this->log($orderB, 'waiting_pickup',  $admin->id, $today->copy()->setTime(15, 5));
        $this->log($orderB, 'picking_up',      $kurir->id, $today->copy()->setTime(15, 20));

        // ------------------------------------------------------------------
        // ORDER C — siap diantar, menunggu kurir mulai delivery
        // ------------------------------------------------------------------
        $orderC = Order::create(array_merge($baseOrderData, [
            'order_code'          => 'DEMO-' . strtoupper(bin2hex(random_bytes(3))),
            'pickup_time'         => $today->copy()->setTime(10, 0),
            'delivery_courier_id' => $kurir->id,
            'courier_id'          => $kurir->id,
            'status'              => 'ready_for_delivery',
        ]));
        $this->setCreatedAt($orderC, $today->copy()->setTime(10, 0));

        $this->log($orderC, 'pending_payment',   $admin->id,    $today->copy()->setTime(10, 0));
        $this->log($orderC, 'waiting_pickup',    $admin->id,    $today->copy()->setTime(10, 5));
        $this->log($orderC, 'arrived_at_laundry',$karyawan->id, $today->copy()->setTime(11, 0));
        $this->log($orderC, 'washing',           $karyawan->id, $today->copy()->setTime(11, 30));
        $this->log($orderC, 'drying_ironing',    $karyawan->id, $today->copy()->setTime(12, 30));
        $this->log($orderC, 'packing',           $karyawan->id, $today->copy()->setTime(13, 0));
        $this->log($orderC, 'ready_for_delivery',$karyawan->id, $today->copy()->setTime(13, 30));

        $this->command?->info('Berhasil! 3 order demo dibuat untuk hari ini (Order A selesai, Order B pickup aktif, Order C siap antar).');
    }

    private function setCreatedAt(Order $order, Carbon $time): void
    {
        $order->timestamps = false;
        $order->created_at = $time;
        $order->save();
    }

    private function log(Order $order, string $status, int $userId, Carbon $time): void
    {
        $log = new OrderStatusLog([
            'order_id' => $order->id,
            'status'   => $status,
            'user_id'  => $userId,
        ]);

        // created_at/updated_at bukan mass-assignable di model ini,
        // jadi di-set langsung & timestamps auto di-nonaktifkan
        // supaya save() tidak menimpanya dengan waktu sekarang.
        $log->timestamps = false;
        $log->created_at = $time;
        $log->updated_at = $time;
        $log->save();
    }
}