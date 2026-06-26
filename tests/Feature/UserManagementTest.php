<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $karyawan;
    private User $kurir;
    private User $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin to authenticate
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        // Create other roles
        $this->karyawan = User::create([
            'name' => 'Karyawan Test',
            'email' => 'karyawan@test.com',
            'password' => bcrypt('password'),
            'role' => 'karyawan',
            'status' => 'active'
        ]);

        $this->kurir = User::create([
            'name' => 'Kurir Test',
            'email' => 'kurir@test.com',
            'password' => bcrypt('password'),
            'role' => 'kurir',
            'status' => 'active'
        ]);

        $this->pelanggan = User::create([
            'name' => 'Pelanggan Test',
            'email' => 'pelanggan@test.com',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
            'status' => 'inactive'
        ]);
    }

    /**
     * Test admin can access users index and see tables.
     */
    public function test_admin_can_access_users_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management');
        $response->assertSee('Admin Test');
        $response->assertSee('Karyawan Test');
        $response->assertSee('Kurir Test');
        $response->assertSee('Pelanggan Test');
    }

    /**
     * Test user search filter.
     */
    public function test_user_index_search_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'Kurir']));

        $response->assertStatus(200);
        $response->assertSee('Kurir Test');
        $response->assertDontSee('Karyawan Test');
    }

    /**
     * Test status filter.
     */
    public function test_user_index_status_filter(): void
    {
        // Filter active
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['status' => 'active']));
        $response->assertStatus(200);
        $response->assertSee('Karyawan Test');
        $response->assertDontSee('Pelanggan Test');

        // Filter inactive (Suspended)
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['status' => 'inactive']));
        $response->assertStatus(200);
        $response->assertSee('Pelanggan Test');
        $response->assertDontSee('Karyawan Test');
    }

    /**
     * Verify that period month/year filters are NOT applied to the main index listing query,
     * as they are reserved for CSV/PDF report filters only.
     */
    public function test_user_index_ignores_period_filters(): void
    {
        $currentYear = now()->year;

        // Passing future year should still show users in the index listing
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', [
            'year' => $currentYear + 5
        ]));
        $response->assertStatus(200);
        $response->assertSee('Karyawan Test');
    }

    /**
     * Test AJAX user detail retrieval.
     */
    public function test_ajax_user_detail_retrieval(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $this->karyawan->id), [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => [
                'id', 'name', 'email', 'role', 'status', 'registered_at'
            ],
            'recent_orders',
            'recent_attendances'
        ]);
        $response->assertJsonPath('user.name', 'Karyawan Test');
    }

    /**
     * Test admin can toggle user status.
     */
    public function test_admin_can_toggle_user_status(): void
    {
        $this->assertEquals('inactive', $this->pelanggan->status);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->pelanggan->id), [
            'status' => 'active'
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertEquals('active', $this->pelanggan->fresh()->status);
    }

    /**
     * Test admin can update all user profile fields.
     */
    public function test_admin_can_update_user_profile(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->karyawan->id), [
            'name' => 'Staff Name Updated',
            'email' => 'staff_updated@test.com',
            'phone' => '+6289999999',
            'address' => 'Updated Address Way',
            'role' => 'karyawan',
            'status' => 'active'
        ]);

        $response->assertRedirect(route('admin.users.index', ['role' => 'karyawan']));
        $updated = $this->karyawan->fresh();
        $this->assertEquals('Staff Name Updated', $updated->name);
        $this->assertEquals('staff_updated@test.com', $updated->email);
        $this->assertEquals('+6289999999', $updated->phone);
        $this->assertEquals('Updated Address Way', $updated->address);
    }

    /**
     * Test admin can export users to PDF.
     */
    public function test_admin_can_export_users_to_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.export.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test admin can export users to CSV.
     */
    public function test_admin_can_export_users_to_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.export.csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        // Assert response content is streamed CSV containing separate role headers
        $content = $response->streamedContent();
        $this->assertStringContainsString('=== Administrators ===', $content);
        $this->assertStringContainsString('=== Staff ===', $content);
        $this->assertStringContainsString('=== Couriers ===', $content);
        $this->assertStringContainsString('=== Customers ===', $content);
        
        $this->assertStringContainsString('Full Name', $content);
        $this->assertStringContainsString('Email', $content);
        $this->assertStringContainsString('Phone', $content);
        
        // Under a unified export, all user entries are present
        $this->assertStringContainsString('Kurir Test', $content);
        $this->assertStringContainsString('Karyawan Test', $content);
    }
}
