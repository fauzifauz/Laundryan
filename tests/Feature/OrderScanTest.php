<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Service;
use App\Models\ItemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderScanTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $courier;
    private $customer;
    private $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard roles
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'phone' => '081234567890',
        ]);

        $this->courier = User::create([
            'name' => 'Courier User',
            'email' => 'courier@example.com',
            'password' => bcrypt('password'),
            'role' => 'kurir',
            'phone' => '081234567891',
        ]);

        $this->customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
            'phone' => '081234567892',
        ]);

        // Create service and item type
        $service = Service::create([
            'name' => 'Regular Wash',
            'base_price' => 10000,
            'is_active' => true,
        ]);

        $itemType = ItemType::create([
            'name' => 'Kiloan',
            'base_price' => 5000,
            'is_active' => true,
        ]);

        // Create order
        $this->order = Order::create([
            'order_code' => 'ORD-TEST1234',
            'customer_id' => $this->customer->id,
            'service_id' => $service->id,
            'item_type_id' => $itemType->id,
            'pickup_address' => 'Test Pickup Address',
            'delivery_address' => 'Test Delivery Address',
            'pickup_time' => now()->addDay(),
            'notes' => 'Test notes',
            'service_price' => 10000,
            'item_price' => 5000,
            'shipping_cost' => 15000,
            'tax' => 3000,
            'total_price' => 33000,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('orders.scan', $this->order));

        $response->assertRedirect('/login');
    }

    public function test_admin_is_redirected_to_admin_order_show(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('orders.scan', $this->order));

        $response->assertRedirect(route('admin.orders.show', $this->order));
    }

    public function test_courier_is_redirected_to_courier_order_show(): void
    {
        $response = $this->actingAs($this->courier)
            ->get(route('orders.scan', $this->order));

        $response->assertRedirect(route('kurir.orders.show', $this->order));
    }

    public function test_customer_is_redirected_to_customer_order_show(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('orders.scan', $this->order));

        $response->assertRedirect(route('customer.orders.show', $this->order));
    }
}
