<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Models\ItemType;
use App\Models\DeliveryFee;
use App\Models\Tax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PricingConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $customer;
    protected $service;
    protected $itemType;
    protected $deliveryFee;
    protected $tax;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin and Customer users
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'pelanggan']);

        // Create initial pricing rules
        $this->service = Service::create([
            'name' => 'Regular Wash',
            'description' => 'Wash and Fold',
            'base_price' => 10000,
            'is_active' => true
        ]);

        $this->itemType = ItemType::create([
            'name' => 'Clothing',
            'description' => 'Standard clothes',
            'base_price' => 5000,
            'is_active' => true
        ]);

        $this->deliveryFee = DeliveryFee::create([
            'min_distance' => 0.00,
            'max_distance' => 10.00,
            'fee' => 2000,
            'min_fee' => 5000,
            'max_fee' => 15000,
            'is_active' => true
        ]);

        $this->tax = Tax::create([
            'name' => 'PPN',
            'percentage' => 11.00,
            'is_active' => true
        ]);
    }

    /** @test */
    public function guests_and_customers_cannot_access_pricing_configuration()
    {
        $this->get(route('admin.pricing.services'))->assertRedirect(route('login'));

        $this->actingAs($this->customer)
            ->get(route('admin.pricing.services'))
            ->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_pricing_configuration_pages()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.pricing.services'))
            ->assertStatus(200)
            ->assertSee('Regular Wash');

        $this->actingAs($this->admin)
            ->get(route('admin.pricing.item-types'))
            ->assertStatus(200)
            ->assertSee('Clothing');

        $this->actingAs($this->admin)
            ->get(route('admin.pricing.delivery-fees'))
            ->assertStatus(200)
            ->assertSee('10');

        $this->actingAs($this->admin)
            ->get(route('admin.pricing.taxes'))
            ->assertStatus(200)
            ->assertSee('PPN');
    }

    /** @test */
    public function admin_can_crud_services_with_photos()
    {
        Storage::fake('public');

        // 1. Create with photo
        $photo = UploadedFile::fake()->image('service.jpg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pricing.services.store'), [
                'name' => 'Express Wash',
                'description' => '1-day service',
                'base_price' => 20000,
                'is_active' => '1',
                'photo' => $photo
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('services', ['name' => 'Express Wash', 'base_price' => 20000]);
        $expressService = Service::where('name', 'Express Wash')->first();
        $this->assertNotNull($expressService->photo);
        Storage::disk('public')->assertExists($expressService->photo);

        // 2. Update and replace photo
        $newPhoto = UploadedFile::fake()->image('new_service.jpg');
        $oldPhotoPath = $expressService->photo;

        $response = $this->actingAs($this->admin)
            ->put(route('admin.pricing.services.update', $expressService), [
                'name' => 'Super Express',
                'base_price' => 25000,
                'photo' => $newPhoto,
                'is_active' => '1'
            ]);

        $response->assertRedirect();
        $expressService = $expressService->fresh();
        $this->assertEquals('Super Express', $expressService->name);
        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($expressService->photo);

        // 3. Toggle Status
        $this->actingAs($this->admin)
            ->post(route('admin.pricing.services.toggle', $expressService));
        $this->assertFalse((bool)$expressService->fresh()->is_active);

        // 4. Update and remove photo
        $response = $this->actingAs($this->admin)
            ->put(route('admin.pricing.services.update', $expressService), [
                'name' => 'Super Express No Image',
                'base_price' => 25000,
                'remove_photo' => '1',
                'is_active' => '1'
            ]);
        $response->assertRedirect();
        $this->assertNull($expressService->fresh()->photo);

        // 5. Delete
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.pricing.services.destroy', $expressService));

        $response->assertRedirect();
        $this->assertDatabaseMissing('services', ['id' => $expressService->id]);
    }

    /** @test */
    public function admin_can_crud_item_types_with_photos()
    {
        Storage::fake('public');

        // 1. Create with photo
        $photo = UploadedFile::fake()->image('item.jpg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pricing.item-types.store'), [
                'name' => 'Leather Jacket',
                'description' => 'Premium clean',
                'base_price' => 15000,
                'is_active' => '1',
                'photo' => $photo
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('item_types', ['name' => 'Leather Jacket', 'base_price' => 15000]);
        $itemType = ItemType::where('name', 'Leather Jacket')->first();
        $this->assertNotNull($itemType->photo);
        Storage::disk('public')->assertExists($itemType->photo);

        // 2. Update and replace photo
        $newPhoto = UploadedFile::fake()->image('new_item.jpg');
        $oldPhotoPath = $itemType->photo;

        $response = $this->actingAs($this->admin)
            ->put(route('admin.pricing.item-types.update', $itemType), [
                'name' => 'Premium Leather Jacket',
                'base_price' => 18000,
                'photo' => $newPhoto,
                'is_active' => '1'
            ]);

        $response->assertRedirect();
        $itemType = $itemType->fresh();
        $this->assertEquals('Premium Leather Jacket', $itemType->name);
        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($itemType->photo);

        // 3. Update and remove photo
        $response = $this->actingAs($this->admin)
            ->put(route('admin.pricing.item-types.update', $itemType), [
                'name' => 'Premium Leather Jacket No Image',
                'base_price' => 18000,
                'remove_photo' => '1',
                'is_active' => '1'
            ]);
        $response->assertRedirect();
        $this->assertNull($itemType->fresh()->photo);

        // 4. Delete
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.pricing.item-types.destroy', $itemType));

        $response->assertRedirect();
        $this->assertDatabaseMissing('item_types', ['id' => $itemType->id]);
    }

    /** @test */
    public function admin_can_crud_delivery_fees()
    {
        // 1. Create
        $response = $this->actingAs($this->admin)
            ->post(route('admin.pricing.delivery-fees.store'), [
                'min_distance' => 10.01,
                'max_distance' => 25.00,
                'fee' => 3000,
                'min_fee' => 15000,
                'max_fee' => 45000,
                'is_active' => '1'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('delivery_fees', ['min_distance' => 10.01, 'max_distance' => 25.00, 'fee' => 3000]);
        $feeConfig = DeliveryFee::where('min_distance', 10.01)->first();

        // 2. Update
        $response = $this->actingAs($this->admin)
            ->put(route('admin.pricing.delivery-fees.update', $feeConfig), [
                'min_distance' => 10.01,
                'max_distance' => 30.00,
                'fee' => 4000,
                'min_fee' => 20000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('delivery_fees', ['id' => $feeConfig->id, 'max_distance' => 30.00, 'fee' => 4000]);

        // 3. Delete
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.pricing.delivery-fees.destroy', $feeConfig));

        $response->assertRedirect();
        $this->assertDatabaseMissing('delivery_fees', ['id' => $feeConfig->id]);
    }

    /** @test */
    public function customer_can_get_realtime_calculated_pricing()
    {
        // Tangerang address to Tangerang address (simulate geocoding)
        $response = $this->actingAs($this->customer)
            ->postJson(route('customer.orders.calculate-price'), [
                'service_id' => $this->service->id,
                'item_type_id' => $this->itemType->id,
                'pickup_address' => 'Mall Alam Sutera, Tangerang',
                'delivery_address' => 'Mall Alam Sutera, Tangerang',
            ]);

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(10000, $data['service_price']);
        $this->assertEquals(5000, $data['item_price']);
        $this->assertGreaterThan(0, $data['distance']);
        $this->assertGreaterThanOrEqual(5000, $data['shipping_cost']); // min_fee is 5000
        $this->assertEquals(11.00, $data['tax_percentage']);
        
        $expectedSubtotal = 10000 + 5000 + $data['shipping_cost'];
        $expectedTax = $expectedSubtotal * 0.11;
        $expectedTotal = $expectedSubtotal + $expectedTax;

        $this->assertEquals($expectedTax, $data['tax']);
        $this->assertEquals($expectedTotal, $data['total_price']);
    }
}
