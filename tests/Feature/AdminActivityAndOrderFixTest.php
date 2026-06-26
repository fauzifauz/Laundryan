<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Service;
use App\Models\ItemType;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminActivityAndOrderFixTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $customer;
    private $service;
    private $itemType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'phone' => '081234567890',
        ]);

        $this->customer = User::create([
            'name' => 'Existing Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
            'phone' => '081234567892',
        ]);

        $this->service = Service::create([
            'name' => 'Regular Wash',
            'base_price' => 10000,
            'is_active' => true,
        ]);

        $this->itemType = ItemType::create([
            'name' => 'Kiloan',
            'base_price' => 5000,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_order_manually(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.orders.store'), [
            'customer_mode' => 'manual',
            'customer_name' => 'Walkin Guest',
            'customer_phone' => '089988887777',
            'service_id' => $this->service->id,
            'item_type_id' => $this->itemType->id,
            'pickup_address' => 'Street A',
            'delivery_address' => 'Street B',
            'pickup_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'notes' => 'No notes',
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.orders.index'));
        
        $customer = User::where('phone', '089988887777')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Walkin Guest', $customer->name);
        $this->assertStringContainsString('walkin_', $customer->email);

        $order = Order::where('customer_id', $customer->id)->first();
        $this->assertNotNull($order);
    }

    public function test_admin_can_create_order_manually_with_only_four_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.orders.store'), [
            'customer_mode' => 'manual',
            'customer_name' => 'Walkin Guest Four Fields',
            'customer_phone' => '089988887779',
            'service_id' => $this->service->id,
            'item_type_id' => $this->itemType->id,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.orders.index'));
        
        $customer = User::where('phone', '089988887779')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Walkin Guest Four Fields', $customer->name);

        $order = Order::where('customer_id', $customer->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('-', $order->pickup_address);
        $this->assertEquals('-', $order->delivery_address);
        $this->assertNotNull($order->pickup_time);
    }

    public function test_admin_can_create_order_selecting_customer(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.orders.store'), [
            'customer_mode' => 'select',
            'customer_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'item_type_id' => $this->itemType->id,
            'pickup_address' => 'Street A',
            'delivery_address' => 'Street B',
            'pickup_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'notes' => 'No notes',
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.orders.index'));

        $order = Order::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($order);
    }

    public function test_admin_can_update_walkin_order(): void
    {
        // Create manual/walkin customer
        $walkinUser = User::create([
            'name' => 'Walkin Test User',
            'email' => 'walkin_test_123@laundryan.local',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
            'status' => 'active'
        ]);

        // Create order
        $order = Order::create([
            'order_code' => 'ORD-TEST12345',
            'customer_id' => $walkinUser->id,
            'service_id' => $this->service->id,
            'item_type_id' => $this->itemType->id,
            'pickup_address' => '-',
            'delivery_address' => '-',
            'pickup_time' => now(),
            'status' => 'pending_payment',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
            'service_price' => 10000,
            'item_price' => 5000,
            'shipping_cost' => 0,
            'tax' => 1500,
            'total_price' => 16500,
        ]);

        // Update with empty address details
        $response = $this->actingAs($this->admin)->put(route('admin.orders.update', $order->id), [
            'customer_id' => $walkinUser->id,
            'service_id' => $this->service->id,
            'item_type_id' => $this->itemType->id,
            'notes' => 'Updated notes',
            'status' => 'washing',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.orders.index'));

        $order->refresh();
        $this->assertEquals('-', $order->pickup_address);
        $this->assertEquals('-', $order->delivery_address);
        $this->assertEquals('Updated notes', $order->notes);
        $this->assertEquals('washing', $order->status);
    }

    public function test_activity_logs_page_loads_with_filters(): void
    {
        ActivityLog::create([
            'user_name' => 'System',
            'email' => 'system@laundryan.local',
            'role' => 'system',
            'category' => 'Auth & Security',
            'activity_type' => 'Audit',
            'description' => 'System audit initialized',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('System Activity Logs');
        $response->assertSee('All Months');
        $response->assertSee('All Years');
        $response->assertSee('Month');
    }

    public function test_activity_logs_export_pdf_all(): void
    {
        ActivityLog::create([
            'user_name' => 'System',
            'email' => 'system@laundryan.local',
            'role' => 'system',
            'category' => 'Auth & Security',
            'activity_type' => 'Audit',
            'description' => 'System audit initialized',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs.export.pdf') . '?month=all&year=all');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_activity_logs_export_csv_all(): void
    {
        ActivityLog::create([
            'user_name' => 'System',
            'email' => 'system@laundryan.local',
            'role' => 'system',
            'category' => 'Auth & Security',
            'activity_type' => 'Audit',
            'description' => 'System audit initialized',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs.export.csv') . '?month=all&year=all');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_activity_logs_page_filters_by_day_week_month_year(): void
    {
        // 1. Day log
        $dayLog = new ActivityLog([
            'user_name' => 'Day User',
            'email' => 'day@laundryan.local',
            'role' => 'system',
            'category' => 'Auth & Security',
            'activity_type' => 'Audit',
            'description' => 'Target Day Log',
        ]);
        $dayLog->created_at = Carbon::parse('2026-05-10 12:00:00');
        $dayLog->save();

        // 2. Week log
        $weekLog = new ActivityLog([
            'user_name' => 'Week User',
            'email' => 'week@laundryan.local',
            'role' => 'system',
            'category' => 'Auth & Security',
            'activity_type' => 'Audit',
            'description' => 'Target Week Log',
        ]);
        $weekLog->created_at = Carbon::parse('2026-05-18 12:00:00'); // 2026-W21
        $weekLog->save();

        // 3. Month log
        $monthLog = new ActivityLog([
            'user_name' => 'Month User',
            'email' => 'month@laundryan.local',
            'role' => 'system',
            'category' => 'Auth & Security',
            'activity_type' => 'Audit',
            'description' => 'Target Month Log',
        ]);
        $monthLog->created_at = Carbon::parse('2026-06-15 12:00:00');
        $monthLog->save();

        // 4. Year log
        $yearLog = new ActivityLog([
            'user_name' => 'Year User',
            'email' => 'year@laundryan.local',
            'role' => 'system',
            'category' => 'Auth & Security',
            'activity_type' => 'Audit',
            'description' => 'Target Year Log',
        ]);
        $yearLog->created_at = Carbon::parse('2025-06-15 12:00:00');
        $yearLog->save();

        // Filter Day
        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs.index') . '?period=today&filter_date=2026-05-10');
        $response->assertSee('Target Day Log');
        $response->assertDontSee('Target Week Log');

        // Filter Week
        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs.index') . '?period=week&filter_week=2026-W21');
        $response->assertSee('Target Week Log');
        $response->assertDontSee('Target Month Log');

        // Filter Month
        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs.index') . '?period=month&filter_month=2026-06');
        $response->assertSee('Target Month Log');
        $response->assertDontSee('Target Year Log');

        // Filter Year
        $response = $this->actingAs($this->admin)->get(route('admin.activity-logs.index') . '?period=year&filter_year=2025');
        $response->assertSee('Target Year Log');
        $response->assertDontSee('Target Month Log');
    }
}
